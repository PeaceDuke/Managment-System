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
        $response->withStatus(201);
        return $response->getBody()->write("Создан новый пользователь\n" . $user->getFullInfo());

    }

    public function updateUser(Request $request, Response $response, $args)
    {
        //доступно только админу
        $id = $args['id'];
        $bodyParams = $request->getParsedBody();
        $user = $this->userService->updateUser($id, $bodyParams['firstname'], $bodyParams['secondname'], $bodyParams['email'],
            $bodyParams['password'], $bodyParams['phonenumber'], $bodyParams['company'], $bodyParams['perms']);
        return $response->getBody()->write("Данные пользователя обновлены\n" . $user->getFullInfo());
    }

    public function deleteUser(Request $request, Response $response, $args)
    {
        //доступно только админу
        $id = $args['id'];
        $user = $this->userService->deleteUser($id);
        return $response->getBody()->write("Пользователь " . $user->getFirstname() . ' ' . $user->getSecondname()
            . " удален, а так же все связанные с ним склады и записи");
    }

    public function getUserInfo(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $user = $this->userService->getUser($id);
        return $response->getBody()->write("Пользователь:\n" . $user->getFullInfo());
    }

    public function getUserList(Request $request, Response $response, $args)
    {
        $userList = $this->userService->getUserList();
        $response->getBody()->write("Список пользователей:\n");
        foreach ($userList as $user) {
            $response->getBody()->write($user->getFullInfo());
        }
        return $response;
    }


}