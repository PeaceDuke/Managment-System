<?php

$app->get('/', function (){
    echo "Login: " . $_SERVER['PHP_AUTH_USER'] . " id: " . $_SESSION['userId'] . "\n";
    echo "managementsystem для дальнейшей работы";
});
$app->group('/managementsystem',function () use ($app) {
    $app->group('/warehouses',function () use ($app) {
        $app->get('/', 'WarehouseController:getWarehousesList'); //получить список складов
        $app->post('/', 'WarehouseController:addWarehouse'); //добавить склад
        $app->group('/{id}',function () use ($app) {
            $app->get('/', 'WarehouseController:getWarehouseInfo'); //полчить инофрмацию о одном складе
            $app->put('/', 'WarehouseController:updateWarehouse'); //обновить информацию о складе
            $app->delete('/', 'WarehouseController:deleteWarehouse'); //удалить информацию о складе
            $app->put('/request', 'WarehouseController:requestItemsToWarehouse'); //запросить у поставщика
            $app->put('/export', 'WarehouseController:exportItemsFromWarehouse'); //отправить поставщику
            $app->put('/transfer', 'WarehouseController:moveItemsToWarehouse'); //отправить на другой склад
            $app->group('/history',function () use ($app) {
                $app->get('/', function (){
                    echo "movement | state для дальнейшей работы";
                });
                $app->get('/movement', 'WarehouseController:getMovementOnWarehouse'); //получить движения связанные со складом
                $app->get('/state', 'WarehouseController:getWarehouseStateOnDate'); //получить состояние склада на дату
            });
        });
    });
    $app->group('/items',function () use ($app) {
        $app->get('/', 'ItemController:getItemsList'); //получить список всех итемов
        $app->post('/', 'ItemController:addItem'); //добавить итем
        $app->group('/{id}',function () use ($app) {
            $app->get('/', 'ItemController:getItemInfo'); //получить информацию о итеме
            $app->put('/', 'ItemController:updateItem'); //обновить информацию о итеме
            $app->delete('/', 'ItemController:deleteItem'); //удалить информацию о итеме
            $app->get('/find', 'ItemController:getItemInWarehouses'); //полчить все склады с товаром
            $app->group('/history',function () use ($app) {
                $app->get('/', function (){
                    echo "movement | state для дальнейшей работы";
                });
                $app->get('/movement', 'ItemController:getItemMovement'); //получить движения связанные со товаром
                $app->get('/state', 'ItemController:getItemInWarehousesOnDate'); //получить состояние товара на складах на дату
            });
        });
    });
    $app->group('/users',function () use ($app) {
        $app->get('/', 'UserController:getUserList'); //получить список всех юзеров
        $app->post('/', 'UserController:addUser'); //добавить юзера
        $app->group('/{id}',function () use ($app) {
            $app->get('/', 'UserController:getUserInfo'); //получить информацию о юзере
            $app->put('/', 'UserController:updateUser'); //обновить информацию о юзере
            $app->delete('/', 'UserController:deleteUser'); //удалить информацию о юзере
        });
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