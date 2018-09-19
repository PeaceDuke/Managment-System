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
        $name = $bodyParams['name'];
        $type = $bodyParams['type'];
        $price = $bodyParams['price'];
        $size = $bodyParams['size'];

        if(isset($type) && isset($name) && isset($price) && isset($size)) {
            $item = $this->itemService->addNewItem($name, $type, $price, $size);
            return $response->getBody()->write("Товар добавлен\nId: " . $item->getId() . "\nName: " . $item->getName()
                . "\nType: " . $item->getType(). "\nPrice: " . $item->getPrice(). "\nSize: " . $item->getSize());
        }
        else
        {
            return $response->getBody()->write('Указаны не все параметры');
        }
    }

    public function updateItem(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $item = $this->itemService->getItem($id);
        if(isset($item)) {
            $bodyParams = $request->getParsedBody();
            $type = $bodyParams['type'];
            $name = $bodyParams['name'];
            $price = $bodyParams['price'];
            $size = $bodyParams['size'];
            $item = $this->itemService->updateItem($item, $name, $type, $price, $size);
            return $response->getBody()->write("Товар обновлен\nId: " . $item->getId() . "\nName: " . $item->getName()
                . "\nType: " . $item->getType(). "\nPrice: " . $item->getPrice(). "\nSize: " . $item->getSize());
        }
        else{
            return $response->getBody()->write("Данный товар недоступен или не существует");
        }
    }

    public function deleteItem(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $item = $this->itemService->getItem($id);
        if(isset($item)) {
            $this->itemService->deleteItem($id);
            return $response->getBody()->write("Товар " . $item->getName() . " удален");
        }
        else{
            return $response->getBody()->write("Данный товар недоступен или не существует");
        }
    }

    public function getItemInfo(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $item = $this->itemService->getItem($id);
        if(isset($item)) {
            return $response->getBody()->write("Ифнормация о товаре\nId: " . $item->getId() . "\nName: " . $item->getName()
                . "\nType: " . $item->getType() . "\nSize: " . $item->getSize() . "\nPrice: " . $item->getPrice());
        }
        else{
            return $response->getBody()->write("Данный склад недоступен или не существует");
        }
    }

    public  function getItemsList(Request $request, Response $response, $args)
    {
        $items = $this->itemService->getAllItem();
        if(!isset($items))
            return $response->getBody()->write('У вас не зарегестрированных товаров');
        if(isset($items)) {
            $output = "Список всех товаров\n";
            foreach ($items as $item) {
                $output = $output . "Id: " . $item->getId() . " Name: " . $item->getName() . "\n";
            }
            return $response->getBody()->write($output);
        }
        else{
            return $response->getBody()->write("Данный склад пуст");
        }
    }
}