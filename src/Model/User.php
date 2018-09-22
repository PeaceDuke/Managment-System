<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 11.09.2018
 * Time: 9:20
 */

namespace App\Model;


class User
{
    private $id;
    private $firstname;
    private $secondname;
    private $email;
    private $phonenumber;
    private $company;
    private $perms;
    private $salt;
    private $password;

    public function __construct($id, $firstname, $secondname, $email, $phonenumber, $company, $perms, $password, $salt)
    {
        $this->id = $id;
        $this->firstname = $firstname;
        $this->secondname = $secondname;
        $this->email = $email;
        $this->phonenumber = $phonenumber;
        $this->company = $company;
        $this->perms = $perms;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @return mixed
     */
    public function getSecondname()
    {
        return $this->secondname;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function getPhonenumber()
    {
        return $this->phonenumber;
    }

    /**
     * @return mixed
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @return mixed
     */
    public function getPerms()
    {
        return $this->perms;
    }

    /**
     * @return mixed
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    public function getFullInfo()
    {
        return 'Id: ' . $this->id . "\nFirst name: " . $this->firstname . "\nSecond name: " . $this->secondname . "\nE-mail: "
            . $this->email . "\nPhone Number: " . $this->phonenumber . "\nCompany: " . $this->company . ($this->perms ? "\nIs admin\n\n" : "\n\n");
    }

}