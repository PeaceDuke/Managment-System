<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 11.09.2018
 * Time: 17:41
 */

namespace App\Repository;


use App\Model\Item;

class ItemRepository
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

    public function addNewItem($name, $type, $price, $size)
    {
        $query = $this->db->prepare('INSERT INTO Item (Owner_id, Name, Type, Price, Size) 
            VALUES (?, ?, ?, ?, ?)');
        try {
            $query->execute([$_SESSION['userId'], $name, $type, $price, $size]);
        } catch (\PDOException $exception) {
            throw new \Exception('400 Bad request Ошибка при добавлении в базу данных: ' . $exception->getMessage(), 400);
        }
        $query = $this->db->prepare('SELECT LAST_INSERT_ID()');
        $query->execute();
        $res = $query->fetch(\PDO::FETCH_ASSOC);
        $item = new Item($res['LAST_INSERT_ID()'], $name, $type, $price, $size);
        return $item;
    }

    public function updateItem($item, $name, $type, $price, $size)
    {
        $query = $this->db->prepare('UPDATE Item SET Name = ?, Type = ?, Price = ?, Size = ? WHERE id = ?');
        $name = (!isset($name) ? $item->getName() : $name);
        $type = (!isset($type) ? $item->getType() : $type);
        $price = (!isset($price) ? $item->getPrice() : $price);
        $size = (!isset($size) ? $item->getSize() : $size);
        try {
            $query->execute([$name, $type, $price, $size, $item->getId()]);
        } catch (\PDOException $exception) {
            throw new \Exception('400 Bad request Ошибка при добавлении в базу данных: ' . $exception->getMessage(), 400);
        }
        $item = new Item($item->getId(), $name, $type, $price, $size);
        return $item;
    }

    public function deleteItem($itemId)
    {
        $query = $this->db->prepare('DELETE FROM Item WHERE id = ?');
        $query->execute([$itemId]);
    }

    public function getItem($itemId)
    {
        $query = $this->db->prepare('SELECT * FROM Item WHERE id = ? AND Owner_id = ? ');
        $query->execute([$itemId, $_SESSION['userId']]);
        $res = $query->fetch(\PDO::FETCH_ASSOC);
        if ($res) {
            return new Item($res['id'], $res['Name'], $res['Type'], $res['Price'], $res['Size']);
        }
        return null;
    }

    public function getAllItem()
    {
        $query = $this->db->prepare('SELECT * FROM Item WHERE Owner_id = ?');
        $query->execute([$_SESSION['userId']]);
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