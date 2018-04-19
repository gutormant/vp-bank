<?php

require_once 'ormmodel.php';

/**
 * Working with client accounts and do operation
 * User: gutormant
 * Date: 18.04.2018
 * Time: 15:35
 */
class Account extends ORMModel
{
    /** @var  client id */
    private $clientID;
    /** @var  date of account creation */
    private $dateOfCreation;
    /** @var  unique deposit percent */
    private $percent;

    /**
     * Client constructor.
     * @param $table
     */
    public function __construct()
    {
        // call parent constructor with account table name and db fields
        parent::__construct('account', ['id','clientID','dateOfCreation', 'percent']);
    }

    /**
     * @return mixed
     */
    public function getClientID()
    {
        return $this->clientID;
    }

    /**
     * @param mixed $clientID
     */
    public function setClientID($clientID)
    {
        $this->clientID = $clientID;
    }

    /**
     * @return mixed
     */
    public function getDateOfCreation()
    {
        return $this->dateOfCreation;
    }

    /**
     * @param mixed $dateOfCreation
     */
    public function setDateOfCreation($dateOfCreation)
    {
        $this->dateOfCreation = $dateOfCreation;
    }

    /**
     * @return mixed
     */
    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * @param mixed $percent
     */
    public function setPercent($percent)
    {
        $this->percent = $percent;
    }


    /**
     * Accrue percent
     * @param null $time
     */
    public function accruePercent( $time = null )
    {
        // check input param on default value (cron using)
        if( is_null($time) )
        {
            $time = time();
            $forGeneration = false;
        }
        // regeneration using
        else
        {
            $forGeneration = date('Y-m-d', $time);
        }

        // get day in month to use in query condition
        $dayInMonth = cal_days_in_month(CAL_GREGORIAN, date('m',$time), date('Y',$time));

        $day = date('j', $time);

        // create query text
        $sql = 'SELECT  a.id,
                        a.percent,
                        month(a.dateOfCreation) month,
                        DAY(a.dateOfCreation) day,
                        COALESCE((SELECT SUM(o.sum) AS opSum FROM operation o where o.accountID = a.id ), 0) balance
                FROM account a
                WHERE (DAY(a.dateOfCreation) = '.$day.(($day == $dayInMonth)?' OR DAY(a.dateOfCreation) > ' . $dayInMonth . ')':')');

        if( $forGeneration ) $sql .= " AND a.dateOfCreation < '$forGeneration'";

        // execute query
        $query = $this->db->query( $sql );

        // check result rows count on zero
        if( !$query->num_rows() ) return;

        // load operation
        $this->load->model('operation');

        // start transaction
        $this->db->trans_begin();

        // process items
        foreach($query->result() as $row)
        {
            // calculate sum by percent
            $percentSum = $row->percent * $row->balance / 1200;

            // add operation
            $this->addOperation( $row->id, date('Y-m-d H:i:s',$time), Operation::OPER_TYPE_PERCENT, $percentSum);
        }

        // check transaction state
        if ($this->db->trans_status() === FALSE)
        {
            // roolback
            $this->db->trans_rollback();

            throw new Exception('Rollback accrue percent transaction');
        }
        else
        {
            // all ok
            $this->db->trans_commit();
        }
    }


    /**
     * accrue bank comission on account
     * @param null $time
     */
    public function accrueCommission( $time = null )
    {
        // check input param on default value
        if( is_null($time) )
        {
            $time = time();
            $forGeneration = false;
        }
        else
        {
            $forGeneration = date('Y-m-d', $time);
        }

        // last month start
        $lastMonthStart = mktime(0,0,0,date('n', $time)-1,1,date('Y', $time));

        // hash montch to compae with user date
        $hashLastMonth = date('Yn', $lastMonthStart);

        $dayInMonth = cal_days_in_month(CAL_GREGORIAN, date('m',$lastMonthStart), date('Y',$lastMonthStart));

        $sql = 'SELECT  a.id,
                        a.dateOfCreation,
                        CONCAT(year(a.dateOfCreation),month(a.dateOfCreation)) hashMonth,
                        DAY(a.dateOfCreation) day,
                        COALESCE((SELECT SUM(o.sum) AS opSum FROM operation o where o.accountID = a.id ), 0) balance
                FROM account a ';

        if( $forGeneration ) $sql .= " WHERE a.dateOfCreation < '$forGeneration'";

        // select accounts from db
        $query = $this->db->query( $sql );

        // no data - no another work
        if( !$query->num_rows() ) return;

        // load model
        $this->load->model('operation');

        // start transaction
        $this->db->trans_begin();

        // processing result
        foreach($query->result() as $row)
        {
            // calculate commission by account balance
            $commissionSum = $this->_calcCommission( $row->balance );

            // check if account created last month
            if( $row->hashMonth == $hashLastMonth )
            {
                // correct commission for not full month
                $commissionSum *= ($dayInMonth - $row->day + 1)/$dayInMonth;
            }

            // save commission
            $this->addOperation( $row->id, date('Y-m-d H:i:s',$time), Operation::OPER_TYPE_COMMISSION, -$commissionSum);
        }

        // check transaction state
        if ($this->db->trans_status() === FALSE)
        {
            // rollback
            $this->db->trans_rollback();

            throw new Exception('Rollback accrue commission transaction');
        }
        else
        {
            // all ok
            $this->db->trans_commit();
        }
    }


    /**
     * calculate bank comission by account balance sum
     * @param $balance
     * @return float|mixed
     */
    private function _calcCommission( $balance )
    {
        if( $balance  > 9999.9999 )
        {
            return min(0.07 * $balance, 5000 );
        }
        elseif( $balance  > 999.9999 )
        {
            return 0.06 * $balance;
        }
        elseif( $balance  > -0.0001 )
        {
            return max(0.05 * $balance, 50 );
        }
        else
        {
            return 0.0000;
        }
    }


    /**
     * add new operation
     * @param $accountID
     * @param $dateOfExecution
     * @param $type
     * @param $sum
     */
    public function addOperation( $accountID, $dateOfExecution, $type, $sum)
    {
        //load model
        $this->load->model('operation');

        // set date of execution
        $this->operation->setDateOfExecution( $dateOfExecution );

        // set account id
        $this->operation->setAccountID( $accountID );

        // set type
        $this->operation->setType( $type );

        // set sum
        $this->operation->setSum( $sum );

        // set id
        $this->operation->setId( NULL );

        // saveoperation to db
        $this->operation->add();
    }

}