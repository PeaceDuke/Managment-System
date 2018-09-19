<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 11.09.2018
 * Time: 17:41
 */

namespace App\Services;


use App\Model\Item;
use App\Model\ItemPack;

class ItemService extends BaseService
{
    public function __construct($db)
    {
        parent::__construct($db);
    }

    public function addNewItem($name, $type, $price, $size)
    {
        $query = $this->db->prepare('INSERT INTO Item (Owner_id, Name, Type, Price, Size) 
            VALUES (:owner,:name,:type,:price,:size)');
        $query->bindParam(':owner', $_SESSION['userId'], \PDO::PARAM_INT);
        $query->bindParam(':name', $name, \PDO::PARAM_STR);
        $query->bindParam(':type', $type, \PDO::PARAM_STR);
        $query->bindParam(':price', $price, \PDO::PARAM_INT);
        $query->bindParam(':size', $size, \PDO::PARAM_INT);
        $query->execute();
        $query = $this->db->prepare('SELECT LAST_INSERT_ID()');
        $query->execute();
        $res = $query->fetch(\PDO::FETCH_ASSOC);
        $item = new Item($res['LAST_INSERT_ID()'], $name, $type, $price, $size);
        return $item;
    }

    public function updateItem($item, $name, $type, $price, $size)
    {
        $query = $this->db->prepare('UPDATE Item SET Name = :name, Type = :type, Price = :price, Size = :size WHERE id = :id');
        if (!isset($name))
            $name = $item->getName();
        if (!isset($type))
            $type = $item->getType();
        if (!isset($price))
            $price = $item->getPrice();
        if (!isset($size))
            $size = $item->getSize();
        $query->bindParam(':id', $item->getId(), \PDO::PARAM_INT);
        $query->bindParam(':name', $name, \PDO::PARAM_STR);
        $query->bindParam(':type', $type, \PDO::PARAM_STR);
        $query->bindParam(':price', $price, \PDO::PARAM_INT);
        $query->bindParam(':size', $size, \PDO::PARAM_INT);
        $query->execute();
        $item = new Item($item->getId(), $name, $type, $price, $size);
        return $item;
    }

    public function deleteItem($id)
    {
        $query = $this->db->prepare('DELETE FROM Item WHERE id = :id');
        $query->bindParam(':id', $id, \PDO::PARAM_INT);
        $query->execute();
    }

    /**
     * @param $id
     * @return Item
     */
    public function getItem($id)
    {
        $query = $this->db->prepare('SELECT * FROM Item WHERE id = :id AND Owner_id = :owner ');
        $query->bindParam(':id', $id, \PDO::PARAM_INT);
        $query->bindParam(':owner', $_SESSION['userId'], \PDO::PARAM_INT);
        $query->execute();
        $res = $query->fetch(\PDO::FETCH_ASSOC);
        if ($res) {
            return new Item($res['id'], $res['Name'], $res['Type'], $res['Price'], $res['Size']);
        }
        return null;
    }

    public function getAllItem()
    {
        $query = $this->db->prepare('SELECT * FROM Item WHERE Owner_id = :owner');
        $query->bindParam(':owner', $_SESSION['userId'], \PDO::PARAM_INT);
        $query->execute();
        $res = $query->fetchAll(\PDO::FETCH_ASSOC);
        if (isset($res[0])) {
            $items = [];
            foreach ($res as $item) {
                $items[$item['id']] = new Item($item['id'], $item['Name'], $item['Type'], $item['Price'], $item['Size']);
            }
            return $items;
        }
        return null;
    }
}