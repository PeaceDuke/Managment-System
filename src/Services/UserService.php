<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 11.09.2018
 * Time: 18:28
 */

namespace App\Services;

use App\Model\User;

class UserService extends BaseService
{
    public function __construct($db)
    {
        parent::__construct($db);
    }

    public function addNewUser($firstname, $secondname, $email, $password, $phonenumber, $company, $perms)
    {
        $query = $this->db->prepare('INSERT INTO User (Company, FirstName, SecondName, `E-mail`, Password, Salt, PhoneNumber, Permission) 
            VALUES (:company, :fname, :sname, :mail, :pass, :salt, :number, :perms)');
        $salt = sha1(microtime());
        $password = ($password . $salt);
        $query->bindParam(':company', $company, \PDO::PARAM_STR);
        $query->bindParam(':fname', $firstname, \PDO::PARAM_STR);
        $query->bindParam(':sname', $secondname, \PDO::PARAM_STR);
        $query->bindParam(':mail', $email, \PDO::PARAM_STR);
        $query->bindParam(':pass', $password, \PDO::PARAM_STR);
        $query->bindParam(':salt', $salt, \PDO::PARAM_STR);
        $query->bindParam(':number', $phonenumber, \PDO::PARAM_STR);
        $query->bindParam(':perms', $perms, \PDO::PARAM_STR);
        $query->execute();
        $query = $this->db->prepare('SELECT LAST_INSERT_ID()');
        $query->execute();
        $res = $query->fetch(\PDO::FETCH_ASSOC);
        $user = new User($res['LAST_INSERT_ID()'], $firstname, $secondname, $email, $phonenumber, $company, $perms);
        return $user;
    }

    public function updateUser(User $user, $firstname, $secondname, $email, $password, $phonenumber, $company, $perms)
    {
        $query = $this->db->prepare('UPDATE User SET Company = :company, FirstName = :fname, SecondName = :sname, `E-mail` = :mail,
             Password = :pass, Salt = :salt, PhoneNumber = :number, Permission = :perms WHERE id = :id');
        $salt = sha1(microtime());
        if(!isset($firstname) || $firstname == '')
            $firstname = $user->getFirstname();
        if(!isset($secondname) || $secondname == '')
            $secondname = $user->getSecondname();
        if(!isset($email) || $email == '')
            $email = $user->getEmail();
        if(!isset($password) || $password == '')
            $password = ($password . $salt);
        if(!isset($phonenumber) || $phonenumber == '')
            $phonenumber = $user->getPhonenumber();
        if(!isset($company) || $company == '')
            $company = $user->getCompany();
        if(!isset($perms) || $perms == '')
            $perms = $user->getPerms();
        $query->bindParam(':id', $user->getId(), \PDO::PARAM_INT);
        $query->bindParam(':company', $company, \PDO::PARAM_STR);
        $query->bindParam(':fname', $firstname, \PDO::PARAM_STR);
        $query->bindParam(':sname', $secondname, \PDO::PARAM_STR);
        $query->bindParam(':mail', $email, \PDO::PARAM_STR);
        $query->bindParam(':pass', $password, \PDO::PARAM_STR);
        $query->bindParam(':salt', $salt, \PDO::PARAM_STR);
        $query->bindParam(':number', $phonenumber, \PDO::PARAM_STR);
        $query->bindParam(':perms', $perms, \PDO::PARAM_STR);
        $query->execute();
        return new User($user->getId(), $firstname, $secondname, $email, $phonenumber, $company, $perms);
    }

    public function deleteUser($user)
    {
        $query = $this->db->prepare('SELECT id FROM Warehouse WHERE Owner_id = :id');
        $query->bindParam(':id', $user->getId(), \PDO::PARAM_INT);
        $query->execute();
        $res = $query->fetchAll();
        foreach ($res as $warehouse)
        {
            $query = $this->db->prepare('DELETE FROM StoredItems WHERE Warehouse_id = :id;
                DELETE FROM Transaction WHERE Whin_id = :id OR Whout_id = :id;');
            $query->execute();
        }
        $query = $this->db->prepare('DELETE FROM Item WHERE Owner_id = :id;
            DELETE FROM User WHERE id = :id;');
        $query->bindParam(':id', $user->getId(), \PDO::PARAM_INT);
        $query->execute();
    }

    /**
     * @param $id
     * @return User
     */
    public function getUser($id)
    {
        $query = $this->db->prepare('SELECT * FROM User WHERE id = :id');
        $query->bindParam(':id', $id, \PDO::PARAM_INT);
        $query->execute();
        $res = $query->fetch(\PDO::FETCH_ASSOC);
        if($res) {
            return new User($id, $res['FirstName'], $res['SecondName'], $res['E-mail'], $res['PhoneNumber'], $res['Company'], $res['Permission']);
        }
        return null;
    }

    public function getUserList()
    {
        $query = $this->db->prepare('SELECT * FROM User');
        $query->bindParam(':id', $id, \PDO::PARAM_INT);
        $query->execute();
        $res = $query->fetchAll(\PDO::FETCH_ASSOC);
        $userList = [];
        foreach ($res as $user){
            $userList[$user['id']] = new User($user['id'], $user['FirstName'], $user['SecondName'], $user['E-mail'], $user['PhoneNumber'], $user['Company'], $user['Permission']);
        }
        return $userList;
    }
}