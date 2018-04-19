<?php

/**
 * Base class to work with table entities
 * User: gutormant
 * Date: 18.04.2018
 * Time: 15:35
 */
class OrmModel extends CI_Model
{
    /** @var  table name */
    private $table;
    /** @var  db fields */
    protected $dbFields;
    /** @var  default entity id field */
    private $id;

    /**
     * ORMModel constructor.
     * @param $table
     */
    public function __construct($tableName, $dbFields)
    {
        // set table name
        $this->table = $tableName;

        // set db fields
        $this->setDbFields( $dbFields );
    }


    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }


    /**
     * @return mixed
     */
    public function getDbFields()
    {
        return $this->dbFields;
    }


    /**
     * @param mixed $dbFields
     */
    public function setDbFields($dbFields)
    {
        $this->dbFields = $dbFields;
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * add new orm entity in DB
     */
    public function add()
    {
        // init data array
        $data = [];

        // processing field
        foreach($this->getDbFields() as $fieldName)
        {
            // set field values to data array
            $data[ $fieldName ] = $this->{'get'.$fieldName}();
        }

        // save new data to db
        $this->db->insert($this->getTable(), $data);

        // get id from db
        $this->id = $this->_getIDFromDB();
    }


    /**
     * get id of new row
     * @return mixed
     */
    private function _getIDFromDB()
    {
        return $this->db->insert_id();
    }


    /**
     * clear table
     * @return mixed
     */
    public function clearAll()
    {
        return $this->db->truncate( $this->getTable() );
    }
}