<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 11.09.2018
 * Time: 17:41
 */

namespace App\Services;


use App\Model\Item;
use App\Repository\ItemRepository;
use App\Repository\TransactionRepository;
use App\Repository\WarehouseRepository;

class ItemService
{
    private $itemRepository;
    private $transactionRepository;
    private $warehouseRepository;

    public function __construct(ItemRepository $itemRepository, TransactionRepository $transactionRepository, WarehouseRepository $warehouseRepository)
    {
        $this->itemRepository = $itemRepository;
        $this->transactionRepository = $transactionRepository;
        $this->warehouseRepository = $warehouseRepository;
    }

    public function addNewItem($name, $type, $price, $size)
    {
        if(isset($name) && isset($type) && isset($price) && isset($size)) {
            return $this->itemRepository->addNewItem($name, $type, $price, $size);
        } else {
            throw new \Exception("400 Bad Request Указаны не все данные", 400);
        }
    }

    public function updateItem($itemId, $name, $type, $price, $size)
    {
        var_dump($name);
        $item = $this->getItem($itemId);
        if (isset($item)) {
            $transactions = $this->transactionRepository->getItemMovement($itemId, new \DateTime());
            if(!isset($transactions)) {
                return $this->itemRepository->updateItem($item, $name, $type, $price, $size);
            } else {
                throw new \Exception("400 Bad Request За этим товаром числятся перемещения, невозможно удалить", 400);
            }
        } else {
            throw new \Exception("404 Not Found Данный товар недоступен или не существует", 404);
        }
    }

    public function deleteItem($itemId)
    {
        $item = $this->itemRepository->getItem($itemId);
        if(!is_null($item)) {
            $transactions = $this->transactionRepository->getItemMovement($itemId, new \DateTime());
            if(!isset($transactions)) {
                $this->itemRepository->deleteItem($itemId);
                return $item->getName();
            } else {
                throw new \Exception("400 Bad Request За этим товаром числятся перемещения, невозможно удалить", 400);
            }
        } else {
            throw new \Exception("404 Not Found Данный товар недоступен или не существует", 404);
        }
    }

    public function getItem($itemId)
    {
        $item = $this->itemRepository->getItem($itemId);
        if(!is_null($item)) {
            return $item;
        } else {
            throw new \Exception("404 Not Found Данный товар недоступен или не существует", 404);
        }
    }

    public function getAllItem()
    {
        return $this->itemRepository->getAllItem();
    }

    public function getItemInWarehouses($itemId)
    {
        $item = $this->itemRepository->getItem($itemId);
        if (isset($item)) {
            $warehouses = $this->warehouseRepository->getWarehousesWithItem($itemId);
            if (isset($warehouses) && sizeof($warehouses) != 0) {
                $output = "Товар: " . $item->getName() . " есть на складах:\n";
                $sum = 0;
                var_dump($warehouses);
                foreach ($warehouses as $warehouse) {
                    $pack = $warehouse->getItemPack($itemId);
                    $output = $output . $warehouse->getAddress() . ": " . $pack->getQuantity() . "\n";
                    $sum += $pack->getPackPrice();
                }
                $output = $output . "Общая стоимиость: " . $sum . " У.е";
                return $output;
            } else {
                return "Такого товара нет ни на одном складе";
            }
        } else {
            throw new \Exception("404 Not Found Данный товар недоступен или не существует", 404);
        }
    }

    public  function getItemMovement($itemId)
    {
        $item = $this->itemRepository->getItem($itemId);
        if (isset($item)) {
            $transactions = $this->transactionRepository->getItemMovement($itemId, new \DateTime('2000-01-01'));
            if (isset($transactions)) {
                $output = 'Движение товара по складам товара: ' . $item->getName() . "\n";
                foreach ($transactions as $transaction) {
                    $out = $this->warehouseRepository->getWarehouseInfo($transaction->getWarehouseOut());
                    $in = $this->warehouseRepository->getWarehouseInfo($transaction->getWarehouseIn());
                    $output = $output . "Из склада по адресу: " . (isset($out) ? $out->getAddress() : '*Адрес поставщика*')
                        . " в склад по адрессу: " . (isset($in) ? $in->getAddress() : '*Адрес приемщика*')
                        . " отправленно " . $this->itemService->getItem($transaction->getItem())->getName()
                        . " в колличестве " . $transaction->getQuantity() . " ед. Запись от " . $transaction->getDate()->format("Y-m-d H:i:s") . "\n";
                }
                return $output;
            } else {
                return "Данный товар ни разу не перемещался";
            }
        } else {
            throw new \Exception("404 Not Found Данный товар недоступен или не существует", 404);
        }
    }

    public function getItemInWarehousesOnDate($itemId, $date)
    {
        $item = $this->itemRepository->getItem($itemId);
        if (isset($item)) {
            $transactions = array_reverse($this->transactionRepository->getItemMovement($itemId, $date));
            if (isset($transactions)) {
                $warehouses = $this->warehouseRepository->getAllWarehouses();
                foreach ($transactions as $transaction) {
                    if (is_null($transaction->getWarehouseOut())) {
                        $warehouses[$transaction->getWarehouseIn()]->removeItem(new ItemPack($this->itemRepository->getItem($transaction->getItem()), $transaction->getQuantity()));
                    }
                    if (is_null($transaction->getWarehouseIn())) {
                        $warehouses[$transaction->getWarehouseIn()]->addItem(new ItemPack($this->itemRepository->getItem($transaction->getItem()), $transaction->getQuantity()));
                    }
                }
                $output = "Товар на " . $date->format('Y-m-d H:i:s') . " был складах: \n";
                $sum = 0;
                foreach ($warehouses as $warehouse) {
                    $pack = $warehouse->getItemPack($itemId);
                    if (isset($pack)) {
                        $output = $output . $warehouse->getAddress() . ": " . $pack->getQuantity() . "ед.\n";
                        $sum += $pack->getPrice() * $pack->getQuantity();
                    }
                }
                $output = $output . "Общая стоимиость: " . $sum . " У.е";
                return $output;
            } else {
                return "Данный товар не перемещался с момента добавления";
            }
        } else {
            throw new \Exception("404 Not Found Данный товар недоступен или не существует", 404);
        }
    }
}