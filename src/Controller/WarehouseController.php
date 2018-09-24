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
     * @var ItemService
     */
    private $itemService;


    public function __construct(WarehouseService $warehouseService, ItemService $itemService)
    {
        $this->warehouseService = $warehouseService;
        $this->itemService = $itemService;
    }

    public function addWarehouse(Request $request, Response $response, $args)
    {
        $bodyParams = $request->getParsedBody();
        $warehouse = $this->warehouseService->addNewWarehouse($bodyParams['address'], $bodyParams['capacity']);
        $response->withStatus(201);
        return $response->getBody()->write("Создан склад\nid: " . $warehouse->getId() . "\nAddress: "
            . $warehouse->getAddress() . "\nCapacity: " . $warehouse->getCapacity());
    }

    public function updateWarehouse(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $bodyParams = $request->getParsedBody();
        $warehouse = $this->warehouseService->updateWarehouse($id, $bodyParams['address'], $bodyParams['capacity']);
        return $response->getBody()->write("Склад обновлен\nid: " . $warehouse->getId() . "\nAddress: "
            . $warehouse->getAddress() . "\nCapacity: " . $warehouse->getCapacity());
    }

    public function deleteWarehouse(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $name = $this->warehouseService->deleteWarehouse($id);
        return $response->getBody()->write("Склад по адресу: " . $name . " удален");
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
                $sum += $itemPack->calcPackPrice();
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
        $out = $this->warehouseService->getItemInWarehouses($id);
        return $response->getBody()->write($out);
    }

    public function moveItemsToWarehouse(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $bodyParams = $request->getParsedBody();
        $destination = $bodyParams['destination'];
        $items = json_decode($bodyParams['items']);
        $out = $this->warehouseService->moveItemsToWarehouse($id, $destination, $items);
        return $response->getBody()->write($out);
    }

    public function requestItemsToWarehouse(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $bodyParams = $request->getParsedBody();
        $items = json_decode($bodyParams['items']);
        $out = $this->warehouseService->requestItemsToWarehouse($id, $items);
        return $response->getBody()->write($out);
    }

    public function exportItemsFromWarehouse(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $bodyParams = $request->getParsedBody();
        $items = json_decode($bodyParams['items']);
        $out = $this->warehouseService->exportItemsFromWarehouse($id, $items);
        return $response->getBody()->write($out);
    }

    public function getMovementOnWarehouse(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $out = $this->warehouseService->getMovementOnWarehouse($id);
        return $response->getBody()->write($out);
    }

    public function getWarehouseStateOnDate(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $bodyParams = $request->getParsedBody();
        $out = $this->warehouseService->getWarehouseStateOnDate($id, $bodyParams['date']);
        return $response->getBody()->write($out);
    }
}