<?php

require_once 'ormmodel.php';

/**
 * Working with operation entity
 * User: gutormant
 * Date: 18.04.2018
 * Time: 15:35
 */
class Operation extends ORMModel
{
    /** @var  account id */
    private $accountID;
    /** @var  type of operation */
    private $type;
    /** @var  date of execution operation */
    private $dateOfExecution;
    /** @var  sum of operation */
    private $sum;

    /** charging */
    const OPER_TYPE_CHARGE = 10;
    /** writeoff */
    const OPER_TYPE_WRITEOFF = 20;
    /** accrue deposit percents */
    const OPER_TYPE_PERCENT = 30;
    /** accrue bank commission */
    const OPER_TYPE_COMMISSION = 40;

    /**
     * Client constructor.
     * @param $table
     */
    public function __construct()
    {
        parent::__construct('operation', ['id','accountID','type','dateOfExecution','sum']);
    }

    /**
     * @return mixed
     */
    public function getAccountID()
    {
        return $this->accountID;
    }

    /**
     * @param mixed $accountID
     */
    public function setAccountID($accountID)
    {
        $this->accountID = $accountID;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getDateOfExecution()
    {
        return $this->dateOfExecution;
    }

    /**
     * @param mixed $time
     */
    public function setDateOfExecution($dateOfExecution)
    {
        $this->dateOfExecution = $dateOfExecution;
    }

    /**
     * @return mixed
     */
    public function getSum()
    {
        return $this->sum;
    }

    /**
     * @param mixed $sum
     */
    public function setSum($sum)
    {
        $this->sum = $sum;
    }


}