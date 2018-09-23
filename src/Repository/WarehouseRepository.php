<?php

namespace App\Repository;

use App\Model\Warehouse;
use App\Model\ItemPack;
use App\Model\Item;

class WarehouseRepository
{
    private $db;

    /**
     * BaseService constructor.
     * @param $db \PDO
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function addNewWarehouse($address, $capacity)
    {
        $query = $this->db->prepare('INSERT INTO Warehouse (Owner_id, Address, Capacity) VALUES (?, ?, ?)');
        try {
            $query->execute([$_SESSION['userId'], $capacity, $address]);
        } catch (\PDOException $exception) {
            throw new \Exception('400 Bad request Ошибка при добавлении в базу данных: ' . $exception->getMessage(), 400);
        }
        $query = $this->db->prepare('SELECT LAST_INSERT_ID()');
        $query->execute();
        $res = $query->fetch(\PDO::FETCH_ASSOC);
        $warehouse = new Warehouse($res['LAST_INSERT_ID()'], $address, [], $capacity);
        return $warehouse;
    }

    public function updateWarehouse($warehouseId, $address, $capacity)
    {
        $warehouse = $this->getWarehouseInfo($warehouseId);
        $query = $this->db->prepare('UPDATE Warehouse SET Address = ?, Capacity = ? WHERE id = ?');
        $address = !isset($address) ? $warehouse->getAddress() : $address;
        $capacity = !isset($capacity) ? $warehouse->getCapacity() : $capacity;
        try {
            $query->execute([$address, $capacity, $warehouseId]);
        } catch (\PDOException $exception) {
            throw new \Exception('400 Bad request Ошибка при добавлении в базу данных: ' . $exception->getMessage(), 400);
        }
        $warehouse = new Warehouse($warehouse->getId(), $address, [], $capacity);
        return $warehouse;
    }

    public function deleteWarehouse($warehouseId)
    {
        $query = $this->db->prepare('DELETE FROM Warehouse WHERE id = ?');
        $query->execute([$warehouseId]);
    }

    public function getWarehouse($warehouseId)
    {
        $query = $this->db->prepare('SELECT * FROM Warehouse WHERE id = ? AND Owner_id = ?');
        $query->execute([$warehouseId, $_SESSION['userId']]);
        $res = $query->fetch(\PDO::FETCH_ASSOC);
        if($res){
            $warehouse = new Warehouse($res['id'], $res['Address'], [], $res['Capacity']);
            return $warehouse;
        }
        return null;
    }

    public function getItemsInWarehouse(Warehouse $warehouse)
    {
        $query = $this->db->prepare('SELECT * FROM StoredItems INNER JOIN Item ON Item_id = Item.id WHERE StoredItems.Warehouse_id = ?');
        $query->execute([$warehouse->getId()]);
        $res = $query->fetchAll(\PDO::FETCH_ASSOC);
        if(isset($res[0])){
            foreach ($res as $item)
            {
                $warehouse->addItem(new ItemPack(new Item($item['id'], $item['Name'], $item['Type'], $item['Price'], $item['Size'] ), $item['Quantity']));
            }
        }
        return $warehouse;
    }

    public function getAllWarehouses()
    {
        $query = $this->db->prepare('SELECT * FROM Warehouse WHERE Owner_id = ?');;
        $query->execute([$_SESSION['userId']]);
        $res = $query->fetchAll(\PDO::FETCH_ASSOC);
        if(isset($res[0])){
            $warehouses = [];
            foreach ($res as $warehouse)
            {
                $warehouses[$warehouse['id']] = new Warehouse($warehouse['id'], $warehouse['Address'], [], $warehouse['Capacity']);
            }
            return $warehouses;
        }
        return null;
    }

    public function addItemInWarehouse(Warehouse $warehouse, $itemId, $quantity)
    {
        $itemPack = $warehouse->getItemPack($itemId);
        if(isset($itemPack)) {
            $request = 'UPDATE StoredItems SET Quantity = :quantity WHERE Warehouse_id = :warehouse AND Item_id = :item';
            $quantity += $itemPack->getQuantity();
        } else {
            $request = 'INSERT INTO StoredItems (Warehouse_id, Item_id, Quantity) VALUES(:warehouse, :item, :quantity)';
        }
        $query = $this->db->prepare($request);
        $query->bindParam(':warehouse', $warehouse->getId(), \PDO::PARAM_INT);
        $query->bindParam(':item', $itemId, \PDO::PARAM_INT);
        $query->bindParam(':quantity', $quantity, \PDO::PARAM_INT);
        $query->execute();
    }

    public function removeItemFromWarehouse(Warehouse $warehouse, $itemId, $quantity)
    {
        $itemPack = $warehouse->getItemPack($itemId);
        if($itemPack->getQuantity() == $quantity) {
            $request = 'DELETE FROM StoredItems WHERE Warehouse_id = :warehouse AND Item_id = :item AND Quantity = :quantity';
        } else {
            $request = 'UPDATE StoredItems SET Quantity = :quantity WHERE Warehouse_id = :warehouse AND Item_id = :item';
            $quantity = $warehouse->getItemPack($itemId)->getQuantity() - $quantity;
        }
        $query = $this->db->prepare($request);
        $query->bindParam(':warehouse', $warehouse->getId(), \PDO::PARAM_INT);
        $query->bindParam(':item', $itemId, \PDO::PARAM_INT);
        $query->bindParam(':quantity', $quantity, \PDO::PARAM_INT);
        $query->execute();
    }

    public function getWarehousesWithItem($itemId)
    {
        $query = $this->db->prepare('SELECT Warehouse_id, Item_id, Name, Type, Price, Size, Address, Capacity, Quantity FROM StoredItems 
            INNER JOIN Warehouse ON StoredItems.Warehouse_id = Warehouse.id 
            INNER JOIN Item ON StoredItems.Item_id = Item.id
            WHERE Item_id = 1;');
        $query->execute([$itemId]);
        $res = $query->fetchAll(\PDO::FETCH_ASSOC);
        if(isset($res[0])){
            $warehouses = [];
            foreach ($res as $warehouse)
            {
                $warehouses[$warehouse['Warehouse_id']] = new Warehouse($warehouse['Warehouse_id'], $warehouse['Address'],
                    [new ItemPack(new Item($warehouse['Item_id'], $warehouse['Name'], $warehouse['Type'],
                        $warehouse['Price'], $warehouse['Size']), $warehouse['Quantity'])], $warehouse['Capacity']);
            }
            return $warehouses;
        }
        return null;
    }
}