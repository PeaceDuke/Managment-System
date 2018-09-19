<?php
use App\Services\UserService;
use App\Services\WarehouseService;
use App\Services\TransactionService;
use App\Services\ItemService;
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
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
    );
    $pdo = new PDO('mysql:host=localhost;dbname=WarehouseManagement;charset=utf8', 'root', 'root', $options);
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
    $pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'], $db['username'], $db['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};


$container['WarehouseService'] = function($c) {
    $db = $c->get('db');
    return new WarehouseService($db);
};

$container['UserService'] = function($c) {
    $db = $c->get('db');
    return new UserService($db);
};

$container['TransactionService'] = function($c) {
    $db = $c->get('db');
    return new TransactionService($db);
};

$container['ItemService'] = function($c) {
    $db = $c->get('db');
    return new ItemService($db);
};

$container['WarehouseController'] = function($c) {
    $warehouseService = $c->get('WarehouseService');
    $transactionService = $c->get('TransactionService');
    $itemService = $c->get('ItemService');
    return new WarehouseController($warehouseService, $transactionService, $itemService);
};

$container['ItemController'] = function($c) {
    $itemService = $c->get('ItemService');
    return new ItemController($itemService);
};

$container['UserController'] = function($c) {
    $userService = $c->get('UserService');
    return new UserController($userService);
};

require __DIR__ . '/router.php';
