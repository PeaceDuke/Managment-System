<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 11.09.2018
 * Time: 18:28
 */

namespace App\Services;

use App\Model\User;
use App\Repository\UserRepository;

class UserService
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function addNewUser($firstname, $secondname, $email, $password, $phonenumber, $company, $perms)
    {
        if ($_SESSION['userType'] == 'Admin') {
            if (isset($firstname) && isset($secondname) && isset($email) && isset($password) &&
                isset($phonenumber) && isset($company) && isset($perms)) {
                return $this->userRepository->addNewUser($firstname, $secondname, $email, $password,
                    $phonenumber, $company, $perms);
            } else {
                throw new \Exception("400 Bad Request Указаны не все данные", 400);
            }
        } else {
            throw new \Exception('403 Forbidden Доступно только администратору', 403);
        }
    }

    public function updateUser($userId, $firstname, $secondname, $email, $password, $phonenumber, $company, $perms)
    {
        if ($_SESSION['userType'] == 'Admin' || $_SESSION['userId'] == $userId) {
            $user = $this->userRepository->getUser($userId);
            if(!is_null($user)) {
                return $this->userRepository->updateUser($user, $firstname, $secondname, $email, $password, $phonenumber, $company, $perms);
            } else {
                throw new \Exception("404 Not Found Данного пользователя не существует", 404);
            }
        } else {
            throw new \Exception('403 Forbidden Доступно только администратору', 403);
        }
    }

    public function deleteUser($userId)
    {
        if ($_SESSION['userType'] == 'Admin' || $_SESSION['userId'] == $userId) {
            $user = $this->userRepository->getUser($userId);
            if(!is_null($user)) {
                $this->userRepository->deleteUser($userId);
            } else {
                throw new \Exception("404 Not Found Данного пользователя не существует", 404);
            }
        } else {
            throw new \Exception('403 Forbidden Доступно только администратору', 403);
        }
    }

    public function getUser($userId)
    {
        if ($_SESSION['userType'] == 'Admin' || $_SESSION['userId'] == $userId) {
            return $this->userRepository->getUser($userId);
        } else {
            throw new \Exception('403 Forbidden Доступно только администратору', 403);
        }
    }

    public function getUserList()
    {
        if ($_SESSION['userType'] == 'Admin') {
            return $this->userRepository->getUserList();
        } else {
            throw new \Exception('403 Forbidden Доступно только администратору', 403);
        }
    }
}