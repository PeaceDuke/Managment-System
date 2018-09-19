<?php

namespace App\Controller;

use App\Services\ItemService;
use App\Services\WarehouseService;
use App\Services\TransactionService;
use App\Model\ItemPack;
use Slim\Http\Request;
use Slim\Http\Response;

class WarehouseController
{
    /**
     * @var WarehouseService
     */
    private $warehouseService;
    /**
     * @var TransactionService
     */
    private $transactionService;
    /**
     * @var ItemService
     */
    private $itemService;


    public function __construct(WarehouseService $warehouseService, TransactionService $transactionService, ItemService $itemService)
    {
        $this->warehouseService = $warehouseService;
        $this->transactionService = $transactionService;
        $this->itemService = $itemService;
    }

    public function index(Request $request, Response $response)
    {
        return $response->getBody()->write("Hello, MVC practice");
    }

    public function addWarehouse(Request $request, Response $response, $args)
    {
        $bodyParams = $request->getParsedBody();
        $address = $bodyParams['address'];
        $capacity = $bodyParams['capacity'];
        if (isset($capacity) && isset($address)) {
            $warehouse = $this->warehouseService->addNewWarehouse($address, $capacity);
            return $response->getBody()->write("Создан склад\nid: " . $warehouse->getId() . "\nAddress: "
                . $warehouse->getAddress() . "\nCapacity: " . $warehouse->getCapacity());
        } else {
            return $response->getBody()->write('Указаны не все параметры');
        }
    }

    public function updateWarehouse(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $warehouse = $this->warehouseService->getWarehouseInfo($id);
        if (isset($warehouse)) {
            $bodyParams = $request->getParsedBody();
            $address = $bodyParams['address'];
            $capacity = $bodyParams['capacity'];
            $warehouse = $this->warehouseService->updateWarehouse($warehouse, $address, $capacity);
            return $response->getBody()->write("Cклад обновлен\nid: " . $warehouse->getId() . "\nAddress: "
                . $warehouse->getAddress() . "\nCapacity: " . $warehouse->getCapacity());
        } else {
            return $response->getBody()->write("Данный склад недоступен или не существует");
        }
    }

    public function deleteWarehouse(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $warehouse = $this->warehouseService->getWarehouseInfo($id);
        if (isset($warehouse)) {
            $this->warehouseService->deleteWarehouse($id);
            return $response->getBody()->write("Склад по адресу: " . $warehouse->getAddress() . " удален");
        } else {
            return $response->getBody()->write("Данный склад недоступен или не существует");
        }
    }

    public function getWarehouseInfo(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $warehouse = $this->warehouseService->getWarehouse($id);
        if (isset($warehouse)) {
            $output = "Информация о складе\nId: " . $warehouse->getId() . "\nAddress: " . $warehouse->getAddress()
                . "\nCapacity: " . $warehouse->getCapacity() . "\nRemaining space: " . $warehouse->getRemainingSpace() . "\n\nТовары:\n";
            $sum = 0;
            foreach ($warehouse->getItemPacks() as $itemPack) {
                $output = $output . $itemPack->getName() . ": " . $itemPack->getQuantity() . "\n";
                $sum += $itemPack->getQuantity() * $itemPack->getPrice();
            }
            $output = $output . "Общая стоимость товаров: " . $sum . "у.е";
            return $response->getBody()->write($output);
        } else {
            return $response->getBody()->write("Данный склад недоступен или не существует");
        }
    }

    public function getWarehousesList(Request $request, Response $response, $args)
    {
        $warehouses = $this->warehouseService->getAllWarehouses();
        if (!isset($warehouses))
            return $response->getBody()->write('У вас не зарегестрированных складов');
        $output = "Список складов:\n";
        foreach ($warehouses as $warehouse) {
            $output = $output . "Id: " . $warehouse->getId() . "\nAddress: " . $warehouse->getAddress()
                . "\nCapacity: " . $warehouse->getCapacity() . "\n";
        }
        return $response->getBody()->write($output);
    }

    public function getItemInWarehouses(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $item = $this->itemService->getItem($id);
        if (isset($item)) {
            $warehouses = $this->warehouseService->getAllWarehouses();
            if (isset($warehouses)) {
                $output = "Товар: " . $item->getName() . " есть на складах:\n";
                $sum = 0;
                foreach ($warehouses as $warehouse) {
                    $pack = $warehouse->getItemPack($id);
                    if (isset($pack)) {
                        $output = $output . $warehouse->getAddress() . ": " . $pack->getQuantity() . "\n";
                        $sum += $pack->getPrice() * $pack->getQuantity();
                    }
                }
                $output = $output . "Общая стоимиость: " . $sum . " У.е";
                return $response->getBody()->write($output);
            } else {
                return $response->getBody()->write("Такого товара нет ни на одном складе");
            }
        } else {
            return $response->getBody()->write("Данный товар недоступен или не существует");
        }
    }

    public function moveItemsToWarehouse(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $bodyParams = $request->getParsedBody();
        $destination = $bodyParams['destination'];
        $items = json_decode($bodyParams['items']);
        $whOut = $this->warehouseService->getWarehouse($id);
        $whIn = $this->warehouseService->getWarehouse($destination);
        if (isset($whOut)) {
            if (isset($whIn)) {
                if (isset($items)) {
                    $itemsList = [];
                    $totalSize = 0;
                    foreach ($items as $key => $val) {
                        $item = $this->itemService->getItem($key);
                        if(is_null($item))
                            return $response->getBody()->write('Товар с id: ' . $key . ' недоступен или не существует');
                        $itemsList[$key] = new ItemPack($item, $val);
                        if($whOut->getItemPack($key)->getQuantity() < $val){
                            $response->getBody()->write('На складе по адресу ' . $whOut->getAddress() .' недостаточно товара ' . $item->getName());
                        }
                        $totalSize += $item->getSize() * $val;
                    }
                    if($totalSize > $whIn->getRemainingSpace()){
                        $response->getBody()->write('На складе по адресу ' . $whIn->getAddress() .' недостаточно места');
                    }
                    $response->getBody()->write("Со склада по адресу " . $whOut->getAddress()
                        . " на склад по адресу " . $whIn->getAddress() . " отправленно: \n");
                    foreach ($itemsList as $item) {
                        $this->warehouseService->addItemInWarehouse($whIn, $item->getItem(), $item->getQuantity());
                        $this->warehouseService->removeItemFromWarehouse($whOut, $item->getItem(), $item->getQuantity());
                        $response->getBody()->write($item->getName() . " " . $item->getQuantity() . "\n");
                    }
                    $this->transactionService->addNewTransaction($whIn, $whOut, $itemsList);
                    return $response;
                } else {
                    return $response->getBody()->write('Не указаны товары для отпрвки');
                }
            } else {
                return $response->getBody()->write("Склад назначенчия недоступен или не существует");
            }
        } else {
            return $response->getBody()->write("Данный склад недоступен или не существует");
        }
    }

    public function requestItemsToWarehouse(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $bodyParams = $request->getParsedBody();
        $items = json_decode($bodyParams['items']);
        $whIn = $this->warehouseService->getWarehouse($id);
        if (isset($whIn)) {
            if (isset($items)) {
                $itemsList = [];
                $totalSize = 0;
                foreach ($items as $key => $val) {
                    $item = $this->itemService->getItem($key);
                    if(is_null($item))
                        return $response->getBody()->write('Товар с id: ' . $key . ' недоступен или не существует');
                    $itemsList[$key] = new ItemPack($item, $val);
                    $totalSize += $item->getSize() * $val;
                }
                if($totalSize > $whIn->getRemainingSpace()){
                    $response->getBody()->write('На складе по адресу ' . $whIn->getAddress() .' недостаточно места');
                }
                $response->getBody()->write("На склад по адресу " . $whIn->getAddress() . " отправленно: \n");
                foreach ($itemsList as $item) {
                    $this->warehouseService->addItemInWarehouse($whIn, $item->getItem(), $item->getQuantity());
                    $response->getBody()->write($item->getName() . " " . $val . "\n");
                }
                $this->transactionService->addNewTransaction($whIn, null, $itemsList);
                return $response;
            } else {
                return $response->getBody()->write('Не указаны товары для отпрвки');
            }
        } else {
            return $response->getBody()->write("Данный склад недоступен или не существует");
        }
    }

    public function exportItemsFromWarehouse(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $bodyParams = $request->getParsedBody();
        $items = json_decode($bodyParams['items']);
        $whOut = $this->warehouseService->getWarehouse($id);
        if (isset($whOut)) {
            if (isset($items)) {
                $itemsList = [];
                foreach ($items as $key => $val) {
                    $item = $this->itemService->getItem($key);
                    if(is_null($item))
                        return $response->getBody()->write('Товар с id: ' . $key . ' недоступен или не существует');
                    if($whOut->getItemPack($key)->getQuantity() < $val){
                        $response->getBody()->write('На складе по адресу ' . $whOut->getAddress() .' недостаточно товара ' . $item->getName());
                    }
                    $itemsList[$key] = new ItemPack($item, $val);
                }
                $response->getBody()->write("Со склада по адресу " . $whOut->getAddress() . " отправленно: \n");
                foreach ($itemsList as $item) {
                    $this->warehouseService->removeItemFromWarehouse($whOut, $item->getItem(), $item->getQuantity());
                    $response->getBody()->write($item->getName() . " " . $item->getQuantity() . "\n");
                }
                $this->transactionService->addNewTransaction(null, $whOut, $itemsList);
                return $response;
            } else {
                return $response->getBody()->write('Не указаны товары для отпрвки');
            }
        } else {
            return $response->getBody()->write("Данный склад недоступен или не существует");
        }
    }

    public function getItemMovement(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $item = $this->itemService->getItem($id);
        if (isset($item)) {
            $transactions = $this->transactionService->getItemMovement($id, new \DateTime('2000-01-01'));
            if (isset($transactions)) {
                $response->getBody()->write('Движение товара по складам товара: ' . $item->getName() . "\n");
                foreach ($transactions as $transaction) {
                    $out = $this->warehouseService->getWarehouseInfo($transaction->getWarehouseOut());
                    $in = $this->warehouseService->getWarehouseInfo($transaction->getWarehouseIn());
                    $response->getBody()->write("Из склада по адресу: " . (isset($out) ? $out->getAddress() : '*Адрес поставщика*')
                        . " в склад по адрессу: " . (isset($in) ? $in->getAddress() : '*Адрес приемщика*')
                        . " отправленно " . $this->itemService->getItem($transaction->getItem())->getName()
                        . " в колличестве " . $transaction->getQuantity() . " ед. Запись от " . $transaction->getDate()->format("Y-m-d H:i:s") . "\n");
                }
                return $response;
            } else {
                return $response->getBody()->write("Данный товар ни разу не перемещался");
            }
        } else {
            return $response->getBody()->write("Данный товар недоступен или не существует");
        }
    }

    public function getMovementOnWarehouse(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $warehouse = $this->warehouseService->getWarehouseInfo($id);
        if (isset($warehouse)) {
            $transactions = $this->transactionService->getMovementOnWarehouse($id, new \DateTime('2000-01-01'));
            if (isset($transactions)) {
                $response->getBody()->write('Движение товаров с участием склада: ' . $warehouse->getAddress() . "\n");
                foreach ($transactions as $transaction) {
                    $out = $this->warehouseService->getWarehouseInfo($transaction->getWarehouseOut());
                    $in = $this->warehouseService->getWarehouseInfo($transaction->getWarehouseIn());
                    $response->getBody()->write("Из склада по адресу: " . (isset($out) ? $out->getAddress() : '*Адрес поставщика*')
                        . " в склад по адрессу: " . (isset($in) ? $in->getAddress() : '*Адрес приемщика*')
                        . " отправленно " . $this->itemService->getItem($transaction->getItem())->getName()
                        . " в колличестве " . $transaction->getQuantity() . " ед. Запись от " . $transaction->getDate()->format("Y-m-d H:i:s") . "\n");
                }
                return $response;
            } else {
                return $response->getBody()->write("Данный склад не учавствовал не в одном перемещении");
            }
        } else {
            return $response->getBody()->write("Данный склад недоступен или не существует");
        }
    }

    public function getWarehouseStateOnDate(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $bodyParams = $request->getParsedBody();
        try {
            $date = new \DateTime($bodyParams['date']);
        } catch (\Exception $exception) {
            return $response->getBody()->write('При конвертации даты произошла ошибка: ' . $exception->getMessage());
        }
        $warehouse = $this->warehouseService->getWarehouse($id);
        if (isset($warehouse)) {
            $transactions = array_reverse($this->transactionService->getMovementOnWarehouse($id, $date));
            if (isset($transactions)) {
                foreach ($transactions as $transaction) {
                    if ($transaction->getWarehouseOut() == $warehouse->getId()) {
                        $warehouse->addItem(new ItemPack($this->itemService->getItem($transaction->getItem()), $transaction->getQuantity()));
                    }
                    if ($transaction->getWarehouseIn() == $warehouse->getId()) {
                        $warehouse->removeItem(new ItemPack($this->itemService->getItem($transaction->getItem()), $transaction->getQuantity()));
                    }
                }
                $output = "Информация о складе\nId: " . $warehouse->getId() . "\nAddress: " . $warehouse->getAddress()
                    . "\nCapacity: " . $warehouse->getCapacity() . "\nRemaining space: " . $warehouse->getRemainingSpace()
                    . "\nСостояние на " . $date->format('Y-m-d H:i:s') . "\nТовары:\n";
                $sum = 0;
                foreach ($warehouse->getItemPacks() as $itemPack) {
                    $output = $output . $itemPack->getName() . ": " . $itemPack->getQuantity() . "\n";
                    $sum += $itemPack->getQuantity() * $itemPack->getPrice();
                }
                $output = $output . "Общая стоимость товаров: " . $sum . "у.е";
                return $response->getBody()->write($output);
            } else {
                return $response->getBody()->write("Данный склад пуст с момента добавления");
            }
        } else {
            return $response->getBody()->write("Данный склад недоступен или не существует");
        }
    }

    public function getItemInWarehousesOnDate(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        try {
            $bodyParams = $request->getParsedBody();
            $date = new \DateTime($bodyParams['date']);
        } catch (\Exception $exception) {
            return $response->getBody()->write('При конвертации даты произошла ошибка: ' . $exception->getMessage());
        }
        $item = $this->itemService->getItem($id);
        if (isset($item)) {
            $transactions = array_reverse($this->transactionService->getItemMovement($id, $date));
            if (isset($transactions)) {
                $warehouses = $this->warehouseService->getAllWarehouses();
                foreach ($transactions as $transaction) {
                    if (is_null($transaction->getWarehouseOut())) {
                        $warehouses[$transaction->getWarehouseIn()]->removeItem(new ItemPack($this->itemService->getItem($transaction->getItem()), $transaction->getQuantity()));
                    }
                    if (is_null($transaction->getWarehouseIn())) {
                        $warehouses[$transaction->getWarehouseIn()]->addItem(new ItemPack($this->itemService->getItem($transaction->getItem()), $transaction->getQuantity()));
                    }
                }
                $output = "Товар на " . $date->format('Y-m-d H:i:s') . " был складах: \n";
                $sum = 0;
                foreach ($warehouses as $warehouse) {
                    $pack = $warehouse->getItemPack($id);
                    if (isset($pack)) {
                        $output = $output . $warehouse->getAddress() . ": " . $pack->getQuantity() . "ед.\n";
                        $sum += $pack->getPrice() * $pack->getQuantity();
                    }
                }
                $output = $output . "Общая стоимиость: " . $sum . " У.е";
                return $response->getBody()->write($output);
            } else {
                return $response->getBody()->write("Данный товар не перемещался с момента добавления");
            }
        } else {
            return $response->getBody()->write("Данный товар недоступен или не существует");
        }
    }

}