<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 12.09.2018
 * Time: 22:24
 */

namespace App\Controller;


use App\Services\UserRepository;
use App\Services\UserService;
use Slim\Http\Request;
use Slim\Http\Response;

class UserController
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function addUser(Request $request, Response $response, $args)
    {
        //доступно только админу
        $bodyParams = $request->getParsedBody();
        $user = $this->userService->addNewUser($bodyParams['firstname'], $bodyParams['secondname'], $bodyParams['email'],
            $bodyParams['password'], $bodyParams['phonenumber'], $bodyParams['company'], $bodyParams['perms']);
        return $response->getBody()->write("Создан новый пользователь\n" . $user->getFullInfo());

    }

    public function updateUser(Request $request, Response $response, $args)
    {
        //доступно только админу
        $id = $args['id'];
        if ($_SESSION['userType'] == 'Admin' || $_SESSION['userId'] == $id) {
            $user = $this->userService->getUser($id);
            if (isset($user)) {
                $bodyParams = $request->getParsedBody();
                $firstname = $bodyParams['firstname'];
                $secondname = $bodyParams['secondname'];
                $email = $bodyParams['email'];
                $password = $bodyParams['password'];
                $phonenumber = $bodyParams['phonenumber'];
                $company = $bodyParams['company'];
                $perms = $bodyParams['perms'];
                $user = $this->userService->updateUser($user, $firstname, $secondname, $email, $password, $phonenumber, $company, $perms);
                return $response->getBody()->write("Данные пользователя обновлены\n" . $user->getFullInfo());
            } else {
                return $response->getBody()->write("Данный пользователь не существует");
            }
        } else {
            return $response->getBody()->write("У вас нет доступа к этой функции");
        }
    }

    public function deleteUser(Request $request, Response $response, $args)
    {
        //доступно только админу
        $id = $args['id'];
        if ($_SESSION['userType'] == 'Admin' || $_SESSION['userId'] == $id) {
            $user = $this->userService->getUser($id);
            if (isset($user)) {
                $this->userService->deleteUser($user);
                return $response->getBody()->write("Пользователь " . $user->getFirstname() . ' ' . $user->getSecondname()
                    . " удален, а так же все связанные с ним склады и записи");
            } else {
                return $response->getBody()->write("Данный пользователь не существует");
            }
        } else {
            return $response->getBody()->write("У вас нет доступа к этой функции");
        }
    }

    public function getUserInfo(Request $request, Response $response, $args)
    {
        if ($_SESSION['userType'] == 'Admin') {
            $id = $args['id'];
            $user = $this->userService->getUser($id);
            if (isset($user)) {
                return $response->getBody()->write("Пользователь:\n " . $user->getFullInfo());
            } else {
                return $response->getBody()->write("Данный пользователь не существует");
            }
        } else {
            return $response->getBody()->write("У вас нет доступа к этой функции");
        }
    }

    public function getUserList(Request $request, Response $response, $args)
    {
        if ($_SESSION['userType'] == 'Admin') {
            $userList = $this->userService->getUserList();
            $response->getBody()->write("Список пользователей:\n ");
            foreach ($userList as $user) {
                $response->getBody()->write($user->getFullInfo());
            }
            return $response;
        } else {
            return $response->getBody()->write("У вас нет доступа к этой функции");
        }
    }

    public function getCurrentUserInfo(Request $request, Response $response, $args)
    {
        $id = $_SESSION['userId'];
        $user = $this->userService->getUser($id);
        if (isset($user)) {
            return $response->getBody()->write("Вы:\n " . $user->getFullInfo());
        } else {
            return $response->getBody()->write("Данный пользователь не существует");
        }
    }


}