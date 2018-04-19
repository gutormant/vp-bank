<?php

require_once 'ormmodel.php';
/**
 * Working with client entity
 * User: gutormant
 * Date: 18.04.2018
 * Time: 15:35
 */
class Client extends ORMModel
{

    /** @var  name */
    private $name;
    /** @var  surname */
    private $surname;
    /** @var  gender */
    private $gender;
    /** @var  date of birth */
    private $dateOfBirth;

    /**
     * Client constructor.
     * @param $table
     */
    public function __construct()
    {
        parent::__construct('client', ['id','name','surname','gender','dateOfBirth']);
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * @param mixed $surname
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
    }

    /**
     * @return mixed
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param mixed $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return mixed
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @param mixed $dateOfBirth
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;
    }

}