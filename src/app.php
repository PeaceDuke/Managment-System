<?php
use App\Services\UserService;
use App\Services\WarehouseService;
use App\Services\ItemService;
use App\Repository\ItemRepository;
use App\Repository\TransactionRepository;
use App\Repository\WarehouseRepository;
use App\Repository\UserRepository;
use App\Controller\WarehouseController;
use App\Controller\ItemController;
use App\Controller\UserController;

$config = [
    'settings' => [
        'db' => [
            'engine' => 'mysql',
            'host' => 'localhost',
            'dbname' => 'WarehouseManagement',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci'
        ],
    ],
];

session_start();

$app = new \Slim\App($config);

// Get container
$container = $app->getContainer();

function checkUser($login, $password) {
    $options = array(
        \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
    );
    $pdo = new \PDO('mysql:host=localhost;dbname=WarehouseManagement;charset=utf8', 'root', 'root', $options);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $query = $pdo->prepare('SELECT id, Salt, Password, Permission FROM User WHERE `E-mail` = :login');
    $query->bindParam(':login', $login, PDO::PARAM_STR_NATL);
    $query->execute();
    $res = $query->fetch(PDO::FETCH_ASSOC);
    //echo sha1($password . $res['Salt']);
    if(!isset($res))
        return false;
    if(sha1($password . $res['Salt']) == $res['Password']) {
        $_SESSION['userId'] = $res['id'];
        $_SESSION['userType'] = $res['Permission'];
        return true;
    }
    return false;
}

//Проверяем наличие и корректность присланных данных
if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
    if(!isset($_SESSION['userId'])) {
        if (!checkUser($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
            echo ('Wrong login or password!');
            header('HTTP/1.0 401 Unauthorized');
        }
    }
}
else
{
    header('WWW-Authenticate: Basic realm="Secured Zone"');
    header('HTTP/1.0 401 Unauthorized');
    echo ('Authorization required!');
}

// Register component on container
$container['db'] = function ($c) {
    $db = $c->get('settings')['db'];
    $pdo = new \PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'], $db['username'], $db['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$container['WarehouseService'] = function($c) {
    $rep1 = $c->get('WarehouseRepository');
    $rep2 = $c->get('TransactionRepository');
    $rep3 = $c->get('ItemRepository');
    return new WarehouseService($rep1, $rep2, $rep3);
};

$container['UserService'] = function($c) {
    $rep = $c->get('UserRepository');
    return new UserService($rep);
};

$container['ItemService'] = function($c) {
    $rep1 = $c->get('ItemRepository');
    $rep2 = $c->get('TransactionRepository');
    $rep3 = $c->get('WarehouseRepository');
    return new ItemService($rep1, $rep2, $rep3);
};
$container['WarehouseRepository'] = function($c) {
    $db = $c->get('db');
    return new WarehouseRepository($db);
};

$container['UserRepository'] = function($c) {
    $db = $c->get('db');
    return new UserRepository($db);
};

$container['TransactionRepository'] = function($c) {
    $db = $c->get('db');
    return new TransactionRepository($db);
};

$container['ItemRepository'] = function($c) {
    $db = $c->get('db');
    return new ItemRepository($db);
};

$container['WarehouseController'] = function($c) {
    $warehouseService = $c->get('WarehouseService');
    $itemService = $c->get('ItemService');
    return new WarehouseController($warehouseService, $itemService);
};

$container['ItemController'] = function($c) {
    $itemService = $c->get('ItemService');
    return new ItemController($itemService);
};

$container['UserController'] = function($c) {
    $userService = $c->get('UserService');
    return new UserController($userService);
};

$container['errorHandler'] = function ($c) {
    return function ($request, $response, Exception $exception) use ($c) {
        return $c['response']->withStatus($exception->getCode() ? 500 : $exception->getCode())
            ->withHeader('Content-Type', 'text/html')
            ->write($exception->getMessage());
    };
};

require __DIR__ . '/router.php';
