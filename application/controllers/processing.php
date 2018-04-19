<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Processing
 * Realize all logic working with entities: clients, accounts, operations
 */
class Processing extends CI_Controller {

	/** random clients count*/
	const clientCount = 100;

	/** random min count of client accounts */
	const accountMinCount = 0;
	/** random max count of client accounts */
	const accountMaxCount = 2;

	/** random min initial charging sum*/
	const operationMinInputSum = 100;
	/** random max initial charging sum*/
	const operationMaxInputSum = 20000;


	/**
	 * create random clients with accounts and base account charge operation
	 */
	private function _fillRandomData()
	{
		$count = self::clientCount;

		// lets go
		while($count--)
		{
			// add new client
			$this->_addRandomClient();

			// get rand account count
			$accountCount = rand(self::accountMinCount, self::accountMaxCount);

			// processing accounta
			while($accountCount--)
			{
				// create account with initial charging
				$this->_addRandomAccount( $this->client->getId() );
			}
		}
	}


	/**
	 * clear all old data
	 */
	private function _clearAll()
	{
		$this->client->clearAll();
		$this->account->clearAll();
		$this->operation->clearAll();
	}


	/**
	 * create new client with rand params
	 */
	private function _addRandomClient()
	{
		// generate birth date as main random param
		$birthTime = mktime(0,0,0,rand(1,12),rand(1,31),rand(1940,2001));

		// set birth date
		$this->client->setDateOfBirth(date('Y-m-d H:i:s', $birthTime));

		// set name
		$this->client->setName(date('l',$birthTime));

		// set surname
		$this->client->setSurname(date('F',$birthTime));

		// set gender
		$this->client->setGender(rand(1,2));

		// reset client id
		$this->client->setId(NULL);

		// save client to db
		$this->client->add();
	}


	/**
	 * add clients random account with start account charging
	 * @param $clientID
	 */
	private function _addRandomAccount( $clientID )
	{
		// ste client id
		$this->account->setClientID( $clientID );

		// set date of account creation
		$this->account->setDateOfCreation( date('Y-m-d H:i:s', mktime(rand(0,23),rand(0,59),rand(0,59),1,rand(1,835),2016) ));

		// set random unique percent for account
		$this->account->setPercent( round(rand(20,200)/10,1) );

		// reset account id
		$this->account->setId(NULL);

		// save account to db
		$this->account->add();

		// make initial charging
		$this->_addOperation( $this->account );
	}


	/**
	 * initial charging
	 * @param $account
	 */
	private function _addOperation( $account )
	{
		// use account add operation method
		$this->account->addOperation(
			$account->getId(),
			$account->getDateOfCreation(),
			Operation::OPER_TYPE_CHARGE,
			round(rand(self::operationMinInputSum, self::operationMaxInputSum), -2)
		);
	}


	/**
	 * regenerate random data
	 */
	public function regenerate()
	{
		// load model
		$this->load->model( ['client','account','operation'] );

		// clear old data
		$this->_clearAll();

		// create clients with accounts and initial charging
		$this->_fillRandomData();

		// init day counter
		$dayIterCount = 1;

		// get current time to use as stop point
		$now = time();

		// check previous day since bank open to make accruals
		while ( ($accrueTime = mktime(0, 0, 0, 1, $dayIterCount++, 2016)) < $now )
		{
			// make accruals
			$this->_makeAccruals( $accrueTime );
		}
	}


	/**
	 * make accruals: capitalization deposit percent and writeoff commission
	 * @param null $accrueTime - time to accrue operations (using in regeneration)
	 * @param int $accrueMode - which operation can we make - bite flag
	 * & 1 - accrue deposit percent
	 * & 2 - accrue bank monthly commission
	 * to make different accruals in separate cron or new run attempt after exception
	 */
	private function _makeAccruals( $accrueTime = null, $accrueMode = 3 )
	{
		// check is date is default (using method as cron) or not (using in regeneration)
		if( is_null($accrueTime) ) $accrueTime = time();

		// load model
		$this->load->model( 'account' );

		try
		{
			// can we accrue percent
			if( $accrueMode & 1)
			{
				// accrue percent
				$this->account->accruePercent( $accrueTime );
			}

			// can we accrue commission and today is first day of month
			if( ($accrueMode & 2) && (date('j', $accrueTime) == 1) )
			{
				// accrue commission
				$this->account->accrueCommission( $accrueTime );
			}
		}
		// something wrong
		catch (Exception $e)
		{
			// logging the exception
			file_put_contents( APPPATH . 'logs/accrue-error.log', "\n".date('Y.m.d H:i:s')." : ".$e->getMessage(), FILE_APPEND);
		}
	}


	/**
	 * default page - using it as cron starting
	 */
	public function cron()
	{
		$this->_makeAccruals(null, $this->uri->segment(3, 3) );
	}
}
