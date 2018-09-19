<?php

$app->get('/', function (){
    echo "Login: " . $_SERVER['PHP_AUTH_USER'] . " id: " . $_SESSION['userId'] . "\n";
    echo "managementsystem для дальнейшей работы";
});
$app->group('/managementsystem',function () use ($app) {
    $app->group('/warehouses',function () use ($app) {
        $app->get('/', 'WarehouseController:getWarehousesList'); //получить список складов
        $app->post('/create', 'WarehouseController:addWarehouse'); //добавить склад
        $app->group('/{id}',function () use ($app) {
            $app->get('/', 'WarehouseController:getWarehouseInfo'); //полчить инофрмацию о одном складе
            $app->post('/update', 'WarehouseController:updateWarehouse'); //обновить информацию о складе
            $app->get('/delete', 'WarehouseController:deleteWarehouse'); //удалить информацию о складе
            $app->post('/request', 'WarehouseController:requestItemsToWarehouse'); //запросить у поставщика
            $app->post('/export', 'WarehouseController:exportItemsFromWarehouse'); //отправить поставщику
            $app->post('/transfer', 'WarehouseController:moveItemsToWarehouse'); //отправить на другой склад
            $app->group('/history',function () use ($app) {
                $app->get('/movement', 'WarehouseController:getMovementOnWarehouse'); //получить движения связанные со складом
                $app->post('/state', 'WarehouseController:getWarehouseStateOnDate'); //получить состояние склада на дату
            });
        });
    });
    $app->group('/items',function () use ($app) {
        $app->get('/', 'ItemController:getItemsList'); //получить список всех итемов
        $app->post('/create', 'ItemController:addItem'); //добавить итем
        $app->group('/{id}',function () use ($app) {
            $app->get('/', 'ItemController:getItemInfo'); //получить информацию о итеме
            $app->post('/update', 'ItemController:updateItem'); //обновить информацию о итеме
            $app->get('/delete', 'ItemController:deleteItem'); //удалить информацию о итеме
            $app->get('/find', 'WarehouseController:getItemInWarehouses'); //полчить все склады с товаром
            $app->group('/history',function () use ($app) {
                $app->get('/movement', 'WarehouseController:getItemMovement'); //получить движения связанные со товаром
                $app->post('/state', 'WarehouseController:getItemInWarehousesOnDate'); //получить состояние товара на складах на дату
            });
        });
    });
    $app->group('/users',function () use ($app) {
        $app->get('/', 'UserController:getUserList'); //получить список всех юзеров
        $app->post('/create', 'UserController:addUser'); //добавить юзера
        $app->group('/{id}',function () use ($app) {
            $app->get('/', 'UserController:getUserInfo'); //получить информацию о юзере
            $app->post('/update', 'UserController:updateUser'); //обновить информацию о юзере
            $app->get('/delete', 'UserController:deleteUser'); //удалить информацию о юзере
        });
        $app->get('/aboutme', 'UserController:getCurrentUserInfo'); //получить список всех юзеров
    });
    $app->get('/logout', function (){
        session_unset();
        session_destroy();
        header('HTTP/1.0 401 Unauthorized');
        echo 'Вы вышли из сисиетмы';
        exit;
    });
    $app->get('/', function (){
        echo "Login: " . $_SERVER['PHP_AUTH_USER'] . " id: " . $_SESSION['userId'] . "\n";
        echo "users | items | warehouses для дальнейшей работы";
    });
});