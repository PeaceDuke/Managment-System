<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 11.09.2018
 * Time: 18:28
 */

namespace App\Repository;

use App\Model\User;

class UserRepository
{
    private $db;

    /**
     * BaseService constructor.
     * @param $db \PDO
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function addNewUser($firstname, $secondname, $email, $password, $phonenumber, $company, $perms)
    {
        $query = $this->db->prepare('INSERT INTO User (Company, FirstName, SecondName, `E-mail`, Password, Salt, PhoneNumber, Permission) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $salt = sha1(microtime());
        $password = ($password . $salt);
        try {
            $query->execute([$company, $firstname, $secondname, $email, $password, $salt, $phonenumber, $perms]);
        } catch (\PDOException $exception) {
            throw new \Exception('400 Bad request Ошибка при добавлении в базу данных: ' . $exception->getMessage(), 400);
        }
        $query = $this->db->prepare('SELECT LAST_INSERT_ID()');
        $query->execute();
        $res = $query->fetch(\PDO::FETCH_ASSOC);
        $user = new User($res['LAST_INSERT_ID()'], $firstname, $secondname, $email, $phonenumber, $company, $perms, $password, $salt);
        return $user;
    }

    public function updateUser($user, $firstname, $secondname, $email, $password, $phonenumber, $company, $perms)
    {
        $query = $this->db->prepare('UPDATE User SET Company = ?, FirstName = ?, SecondName = ?, `E-mail` = ?,
             Password = ?, Salt = ?, PhoneNumber = ?, Permission = ? WHERE id = ?');
        $salt = sha1(microtime());
        $company = (!isset($company) ? $user->getCompany() : $company);
        $firstname = (!isset($firstname) ? $user->getFirstname() : $firstname);
        $secondname = (!isset($secondname) ? $user->getSecondname() : $secondname);
        $email = (!isset($email) ? $user->getEmail() : $email);
        $password = (!isset($password) ? $user->getPassword() : sha1($password . $salt));
        $salt = (!isset($salt) ? $user->getSalt() : $salt);
        $phonenumber = (!isset($phonenumber) ? $user->getPhonenumber() : $phonenumber);
        $perms = (!isset($perms) ? $user->getPerms() : $perms);
        try {
            $query->execute([$company, $firstname, $secondname, $password, $salt, $phonenumber, $perms, $user->getId()]);
        } catch (\PDOException $exception) {
            throw new \Exception('400 Bad request Ошибка при добавлении в базу данных: ' . $exception->getMessage(), 400);
        }
        return new User($user->getId(), $firstname, $secondname, $email, $phonenumber, $company, $perms, $password, $salt);
    }

    public function deleteUser($userId)
    {
        $query = $this->db->prepare('SELECT id FROM Warehouse WHERE Owner_id = ?');
        $query->execute([$userId]);
        $res = $query->fetchAll();
        foreach ($res as $warehouse) {
            $query = $this->db->prepare('DELETE FROM StoredItems WHERE Warehouse_id = ?;
                DELETE FROM Transaction WHERE Whin_id = ? OR Whout_id = ?;');
            $query->execute($warehouse['id'], $warehouse['id'], $warehouse['id']);
        }
        $query = $this->db->prepare('DELETE FROM Item WHERE Owner_id = ?;
            DELETE FROM User WHERE id = ?;');
        $query->execute([$userId]);
    }

    /**
     * @param $id
     * @return User
     */
    public function getUser($userId)
    {
        $query = $this->db->prepare('SELECT * FROM User WHERE id = ?');
        $query->execute([$userId]);
        $res = $query->fetch(\PDO::FETCH_ASSOC);
        if ($res) {
            return new User($userId, $res['FirstName'], $res['SecondName'], $res['E-mail'], $res['PhoneNumber'],
                $res['Company'], $res['Permission'], $res['Password'], $res['Salt']);
        }
        return null;
    }

    public function getUserList()
    {
        $query = $this->db->prepare('SELECT * FROM User');
        $query->execute();
        $res = $query->fetchAll(\PDO::FETCH_ASSOC);
        $userList = [];
        foreach ($res as $user) {
            $userList[$user['id']] = new User($user['id'], $user['FirstName'], $user['SecondName'], $user['E-mail'], $user['PhoneNumber'], $user['Company'], $user['Permission'], $res['Password'], $res['Salt']);
        }
        return $userList;
    }
}