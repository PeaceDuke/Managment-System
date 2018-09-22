<?php

namespace App\Services;

use App\Model\Warehouse;
use App\Model\ItemPack;
use App\Model\Item;
use App\Repository\WarehouseRepository;
use App\Repository\TransactionRepository;
use App\Repository\ItemRepository;

class WarehouseService
{
    private $warehouseRepository;
    private $transactionRepository;
    private $itemRepository;

    public function __construct(WarehouseRepository $warehouseRepository, TransactionRepository $transactionRepository, ItemRepository $itemRepository)
    {
        $this->warehouseRepository = $warehouseRepository;
        $this->itemRepository = $itemRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function addNewWarehouse($address, $capacity)
    {
        if (isset($address) && isset($capacity)) {
            return $this->warehouseRepository->addNewWarehouse($address, $capacity);
        } else {
            throw new \Exception("400 Bad Request Указаны не все параметры!", 400);
        }
    }

    public function updateWarehouse($warehouseId, $address, $capacity)
    {
        $warehouse = $this->warehouseRepository->getWarehouse($warehouseId);
        if (isset($warehouse)) {
            return $this->warehouseRepository->updateWarehouse($warehouseId, $address, $capacity);
        } else {
            throw new \Exception("404 Not Found Данный склад не доступен или не существует", 404);
        }

    }

    public function deleteWarehouse($warehouseId)
    {
        $warehouse = $this->warehouseRepository->getWarehouse($warehouseId);
        if (isset($warehouse)) {
            $this->warehouseRepository->deleteWarehouse($warehouseId);
            return $warehouse->getAddress();
        } else {
            throw new \Exception("404 Not Found Данный склад не доступен или не существует", 404);
        }
    }

    public function getWarehouse($warehouseId)
    {
        $warehouse = $this->warehouseRepository->getWarehouse($warehouseId);
        if (isset($warehouse)) {
            return $this->warehouseRepository->getItemsInWarehouse($warehouse);
        } else {
            throw new \Exception("404 Not Found Данный склад не доступен или не существует", 404);
        }
    }

    public function getWarehouseInfo($warehouseId)
    {
        $warehouse = $this->warehouseRepository->getWarehouse($warehouseId);
        if (isset($warehouse)) {
            return $warehouse;
        } else {
            throw new \Exception("404 Not Found Данный склад не доступен или не существует", 404);
        }
    }

    public function getAllWarehouses()
    {
        return $this->warehouseRepository->getAllWarehouses();
    }

    public function moveItemsToWarehouse($whOut_id, $whIn_id, $items)
    {
        $whOut = $this->warehouseRepository->getWarehouse($whOut_id);
        $whIn = $this->warehouseRepository->getWarehouse($whIn_id);
        if (isset($whOut)) {
            if (isset($whIn)) {
                if (isset($items)) {
                    $itemsList = [];
                    $totalSize = 0;
                    foreach ($items as $key => $val) {
                        $item = $this->itemRepository->getItem($key);
                        if (is_null($item))
                            throw new \Exception("400 Bad Request Товар с id:" . $key . " недоступен или не существует", 400);
                        $itemsList[$key] = new ItemPack($item, $val);
                        if ($whOut->getItemPack($key)->getQuantity() < $val) {
                            throw new \Exception('400 Bad Request На складе по адресу ' . $whOut->getAddress() . ' недостаточно товара ' . $item->getName(), 400);
                        }
                        $totalSize += $item->getSize() * $val;
                    }
                    if ($totalSize > $whIn->getRemainingSpace()) {
                        throw new \Exception('400 Bad Request На складе по адресу ' . $whIn->getAddress() . ' недостаточно места', 400);
                    }
                    $output = "Со склада по адресу " . $whOut->getAddress()
                        . " на склад по адресу " . $whIn->getAddress() . " отправленно: \n";
                    foreach ($itemsList as $item) {
                        $this->warehouseRepository->addItemInWarehouse($whIn, $item->getItem(), $item->getQuantity());
                        $this->warehouseRepository->removeItemFromWarehouse($whOut, $item->getItem(), $item->getQuantity());
                        $output = $output . $item->getName() . " " . $item->getQuantity() . "\n";
                    }
                    $this->transactionRepository->addNewTransaction($whIn, $whOut, $itemsList);
                    return $output;
                } else {
                    throw new \Exception("400 Bad Request Не указаны товары для отправки", 400);
                }
            } else {
                throw new \Exception("400 Bad Request Склад назначения не доступен или не существует", 400);
            }
        } else {
            throw new \Exception("404 Not Found Данный склад не доступен или не существует", 404);
        }
    }

    public function requestItemsToWarehouse($whIn_id, $items)
    {
        $whIn = $this->warehouseRepository->getWarehouse($whIn_id);
        if (isset($whIn)) {
            if (isset($items)) {
                $itemsList = [];
                $totalSize = 0;
                foreach ($items as $key => $val) {
                    $item = $this->itemRepository->getItem($key);
                    if (is_null($item))
                        throw new \Exception("400 Bad Request Товар с id:" . $key . " недоступен или не существует", 400);
                    $itemsList[$key] = new ItemPack($item, $val);
                    $totalSize += $item->getSize() * $val;
                }
                if ($totalSize > $whIn->getRemainingSpace()) {
                    throw new \Exception('400 Bad Request На складе по адресу ' . $whIn->getAddress() . ' недостаточно места', 400);
                }
                $output = "На склад по адресу " . $whIn->getAddress() . " отправленно: \n";
                foreach ($itemsList as $item) {
                    $this->warehouseRepository->addItemInWarehouse($whIn, $item->getItem(), $item->getQuantity());
                    $output = $output . $item->getName() . " " . $val . "\n";
                }
                $this->transactionRepository->addNewTransaction($whIn, null, $itemsList);
                return $output;
            } else {
                throw new \Exception("400 Bad Request Не указаны товары для отправки", 400);
            }
        } else {
            throw new \Exception("404 Not Found Данный склад не доступен или не существует", 404);
        }
    }

    public function exportItemsFromWarehouse($whOut_id, $items)
    {
        $whOut = $this->warehouseRepository->getWarehouse($whOut_id);
        if (isset($whOut)) {
            if (isset($items)) {
                $itemsList = [];
                foreach ($items as $key => $val) {
                    $item = $this->itemRepository->getItem($key);
                    if (is_null($item))
                        throw new \Exception("400 Bad Request Данный товар недоступен или не существует", 400);
                    $itemsList[$key] = new ItemPack($item, $val);
                    if ($whOut->getItemPack($key)->getQuantity() < $val) {
                        throw new \Exception('400 Bad Request На складе по адресу ' . $whOut->getAddress() . ' недостаточно товара ' . $item->getName(), 400);
                    }
                }
                $output = "Со склада по адресу " . $whOut->getAddress() . " отправленно: \n";
                foreach ($itemsList as $item) {
                    $this->warehouseRepository->removeItemFromWarehouse($whOut, $item->getItem(), $item->getQuantity());
                    $output = $output . $item->getName() . " " . $item->getQuantity() . "\n";
                }
                $this->transactionRepository->addNewTransaction(null, $whOut, $itemsList);
                return $output;
            } else {
                throw new \Exception("400 Bad Request Не указаны товары для отправки", 400);
            }
        } else {
            throw new \Exception("404 Not Found Данный склад не доступен или не существует", 404);
        }
    }

    public function getMovementOnWarehouse($warehouseId)
    {
        $warehouse = $this->warehouseRepository->getWarehouseInfo($warehouseId);
        if (isset($warehouse)) {
            $transactions = $this->transactionRepository->getMovementOnWarehouse($warehouseId, new \DateTime('2000-01-01'));
            if (isset($transactions)) {
                $output = 'Движение товаров с участием склада: ' . $warehouse->getAddress() . "\n";
                foreach ($transactions as $transaction) {
                    $out = $this->transactionRepository->getWarehouseInfo($transaction->getWarehouseOut());
                    $in = $this->transactionRepository->getWarehouseInfo($transaction->getWarehouseIn());
                    $output = $output . "Из склада по адресу: " . (isset($out) ? $out->getAddress() : '*Адрес поставщика*')
                        . " в склад по адрессу: " . (isset($in) ? $in->getAddress() : '*Адрес приемщика*')
                        . " отправленно " . $this->itemRepository->getItem($transaction->getItem())->getName()
                        . " в колличестве " . $transaction->getQuantity() . " ед. Запись от " . $transaction->getDate()->format("Y-m-d H:i:s") . "\n";
                }
                return $output;
            } else {
                return "Не найдено перемещений по данному складу";
            }
        } else {
            throw new \Exception("404 Not Found Данный склад не доступен или не существует", 404);
        }
    }

    public function getWarehouseStateOnDate($warehouseId, $date)
    {
        $warehouse = $this->warehouseRepository->getWarehouse($warehouseId);
        if (isset($warehouse)) {
            $transactions = array_reverse($this->transactionRepository->getMovementOnWarehouse($warehouseId, $date));
            if (isset($transactions)) {
                foreach ($transactions as $transaction) {
                    if ($transaction->getWarehouseOut() == $warehouse->getId()) {
                        $warehouse->addItem(new ItemPack($this->itemRepository->getItem($transaction->getItem()), $transaction->getQuantity()));
                    }
                    if ($transaction->getWarehouseIn() == $warehouse->getId()) {
                        $warehouse->removeItem(new ItemPack($this->itemRepository->getItem($transaction->getItem()), $transaction->getQuantity()));
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
                return $output;
            } else {
                return "Данный склад пуст с момента добавления";
            }
        } else {
            throw new \Exception("404 Not Found Данный склад не доступен или не существует", 400);
        }
    }
}