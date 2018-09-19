<?php

namespace App\Services;

use App\Model\Warehouse;
use App\Model\ItemPack;
use App\Model\Item;

class WarehouseService extends BaseService
{

    public function __construct($db)
    {
        parent::__construct($db);
    }

    public function addNewWarehouse($address, $capacity)
    {
        $query = $this->db->prepare('INSERT INTO Warehouse (Owner_id, Address, Capacity) VALUES (:owner,:address,:capacity)');
        $query->bindParam(':owner', $_SESSION['userId'], \PDO::PARAM_INT);
        $query->bindParam(':capacity', $capacity, \PDO::PARAM_INT);
        $query->bindParam(':address', $address, \PDO::PARAM_STR);
        $query->execute();
        $query = $this->db->prepare('SELECT LAST_INSERT_ID()');
        $query->execute();
        $res = $query->fetch(\PDO::FETCH_ASSOC);
        $warehouse = new Warehouse($res['LAST_INSERT_ID()'], $address, [], $capacity);
        return $warehouse;
    }

    /**
     * @param $warehouse Warehouse
     * @param $address
     * @param $capacity
     * @return Warehouse
     */
    public function updateWarehouse($warehouse, $address, $capacity)
    {
        $query = $this->db->prepare('UPDATE Warehouse SET Address = :address, Capacity = :capacity WHERE id = :id');
        if(!isset($address))
            $address = $warehouse->getAddress();
        if(!isset($capacity))
            $capacity = $warehouse->getCapacity();
        $query->bindParam(':id', $warehouse->getId(), \PDO::PARAM_INT);
        $query->bindParam(':address', $address, \PDO::PARAM_STR);
        $query->bindParam(':capacity', $capacity, \PDO::PARAM_INT);
        $query->execute();
        $warehouse = new Warehouse($warehouse->getId(), $address, [], $capacity);
        return $warehouse;
    }

    public function deleteWarehouse($warehouseId)
    {
        $query = $this->db->prepare('DELETE FROM Warehouse WHERE id = :id');
        $query->bindParam(':id', $warehouseId, \PDO::PARAM_INT);
        $query->execute();
    }

    public function getWarehouse($id)
    {
        $query = $this->db->prepare('SELECT * FROM Warehouse WHERE id = :id AND Owner_id = :owner');
        $query->bindParam(':id', $id, \PDO::PARAM_INT);
        $query->bindParam(':owner', $_SESSION['userId'], \PDO::PARAM_INT);
        $query->execute();
        $res = $query->fetch(\PDO::FETCH_ASSOC);
        if($res){
            $warehouse = new Warehouse($res['id'], $res['Address'], [], $res['Capacity']);
            $query = $this->db->prepare('SELECT * FROM StoredItems INNER JOIN Item ON Item_id = Item.id WHERE StoredItems.Warehouse_id = :id');
            $query->bindParam(':id', $id, \PDO::PARAM_INT);
            $query->execute();
            $res = $query->fetchAll(\PDO::FETCH_ASSOC);
            if(isset($res[0])){
                foreach ($res as $item)
                {
                    $warehouse->addItem(new ItemPack(new Item($item['id'], $item['Name'], $item['Type'], $item['Price'], $item['Size'] ), $item['Quantity']));
                }
            }
            return $warehouse;
        }
        return null;
    }

    public function getWarehouseInfo($id)
    {
        $query = $this->db->prepare('SELECT * FROM Warehouse WHERE id = :id AND Owner_id = :owner');
        $query->bindParam(':id', $id, \PDO::PARAM_INT);
        $query->bindParam(':owner', $_SESSION['userId'], \PDO::PARAM_INT);
        $query->execute();
        $res = $query->fetch(\PDO::FETCH_ASSOC);
        if($res){
            $warehouse = new Warehouse($res['id'], $res['Address'], [], $res['Capacity']);
            return $warehouse;
        }
        return null;
    }

    public function getAllWarehouses()
    {
        $query = $this->db->prepare('SELECT * FROM Warehouse WHERE Owner_id = :owner');;
        $query->bindParam(':owner', $_SESSION['userId'], \PDO::PARAM_INT);
        $query->execute();
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

    public function getWarehousesWithItem($itemId)
    {
        $warehouses = $this->getAllWarehouses();
        $warehouseList = [];
        foreach ($warehouses as $warehouse)
        {
            if ($warehouse->findItem($itemId)) {
                $warehouseList[$warehouse->getId()]= $warehouse;
            }
        }
        if(sizeof($warehouseList) == 0)
            return null;
        return $warehouseList;
    }

    public function addItemInWarehouse(Warehouse $warehouse, Item $item, $quantity)
    {
        if($warehouse->findItem($item->getId())) {
            $request = 'UPDATE StoredItems SET Quantity = :quantity WHERE Warehouse_id = :warehouse AND Item_id = :item';
            $quantity += $warehouse->getItemPack($item->getId())->getQuantity();
        } else {
            $request = 'INSERT INTO StoredItems (Warehouse_id, Item_id, Quantity) VALUES(:warehouse, :item, :quantity)';
        }
        $query = $this->db->prepare($request);
        $query->bindParam(':warehouse', $warehouse->getId(), \PDO::PARAM_INT);
        $query->bindParam(':item', $item->getId(), \PDO::PARAM_INT);
        $query->bindParam(':quantity', $quantity, \PDO::PARAM_INT);
        $query->execute();
    }

    public function removeItemFromWarehouse(Warehouse $warehouse, Item $item, $quantity)
    {
        if($warehouse->getItemPack($item->getId())->getQuantity() == $quantity) {
            $request = 'DELETE FROM StoredItems WHERE Warehouse_id = :warehouse AND Item_id = :item AND Quantity = :quantity';
        } else {
            $request = 'UPDATE StoredItems SET Quantity = :quantity WHERE Warehouse_id = :warehouse AND Item_id = :item';
            $quantity = $warehouse->getItemPack($item->getId())->getQuantity() - $quantity;
        }
        $query = $this->db->prepare($request);
        $query->bindParam(':warehouse', $warehouse->getId(), \PDO::PARAM_INT);
        $query->bindParam(':item', $item->getId(), \PDO::PARAM_INT);
        $query->bindParam(':quantity', $quantity, \PDO::PARAM_INT);
        $query->execute();
    }
}