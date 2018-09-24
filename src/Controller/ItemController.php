<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 12.09.2018
 * Time: 21:05
 */

namespace App\Controller;

use App\Services\ItemService;
use Slim\Http\Request;
use Slim\Http\Response;

class ItemController
{
    /**
     * @var ItemService
     */
    private $itemService;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    public function addItem(Request $request, Response $response, $args)
    {
        $bodyParams = $request->getParsedBody();
        $item = $this->itemService->addNewItem($bodyParams['name'], $bodyParams['type'], $bodyParams['price'], $bodyParams['size']);
        $response->withStatus(201);
        return $response->getBody()->write("Товар добавлен\n" . $item->getFullInfo());
    }

    public function updateItem(Request $request, Response $response, $args)
    {
        $itemId = $args['id'];
        $bodyParams = $request->getParsedBody();
        var_dump($bodyParams);
        $item = $this->itemService->updateItem($itemId, $bodyParams['name'], $bodyParams['type'], $bodyParams['price'], $bodyParams['size']);
        return $response->getBody()->write("Товар обновлен\n" . $item->getFullInfo());
    }

    public function deleteItem(Request $request, Response $response, $args)
    {
        $itemId = $args['id'];
        $name = $this->itemService->deleteItem($itemId);
        return $response->getBody()->write("Товар " . $name . " удален");
    }

    public function getItemInfo(Request $request, Response $response, $args)
    {
        $itemId = $args['id'];
        $item = $this->itemService->getItem($itemId);
        return $response->getBody()->write("Ифнормация о товаре\n" . $item->getFullInfo());
    }

    public function getItemsList(Request $request, Response $response, $args)
    {
        $items = $this->itemService->getAllItem();
        if(isset($items)) {
            $output = "Список всех товаров\n";
            foreach ($items as $item) {
                $output = $output . "Id: " . $item->getId() . " Name: " . $item->getName() . "\n";
            }
            return $response->getBody()->write($output);
        } else {
            return $response->getBody()->write('У вас нет зарегестрированных товаров');
        }
    }

    public function getItemMovement(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $out = $this->itemService->getItemMovement($id);
        return $response->getBody()->write($out);
    }

    public function getItemInWarehousesOnDate(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $bodyParams = $request->getParsedBody();
        $out = $this->itemService->getItemInWarehousesOnDate($id, $bodyParams['date']);
        return $response->getBody()->write($out);
    }

    public function getItemInWarehouses(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $out = $this->itemService->getItemInWarehouses($id);
        return $response->getBody()->write($out);
    }

}