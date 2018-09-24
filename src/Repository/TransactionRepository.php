<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 12.09.2018
 * Time: 12:44
 */

namespace App\Repository;


use App\Model\ItemPack;
use App\Model\Transaction;
use App\Model\Warehouse;

class TransactionRepository
{
    protected $db;

    /**
     * BaseService constructor.
     * @param $db \PDO
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function addNewTransaction($warehouseIn, $warehouseOut, array $items)
    {
        $query = $this->db->prepare('SELECT MAX(id) FROM Transaction');
        $query->execute();
        $id = $query->fetch();
        $id = $id['MAX(id)'];
        $id++;
        foreach ($items as $itemPack) {
            $query = $this->db->prepare('INSERT INTO Transaction (id, Item_id, Whin_id, Whout_id, Quantity, Date) 
                VALUES(?, ?, ?, ?, ?, ?)');
            $time = new \DateTime();
            $query->execute([$id, $itemPack->getId(), $warehouseIn, $warehouseOut, $itemPack->getQuantity(), $time->format('Y-m-d H:i:s')]);
        }
    }

    public function getItemMovement($itemId, \DateTime $date)
    {
        $query = $this->db->prepare('SELECT * FROM Transaction WHERE Item_id = ? AND Date > ?');
        $query->execute([$itemId, $date->format("Y-m-d H:i:s")]);
        $res = $query->fetchAll();
        if(isset($res[0])){
            $transactionList =[];
            $t = 0;
            foreach ($res as $transaction)
            {
                $transactionList[$t] = new Transaction($transaction['Whin_id'], $transaction['Whout_id'],
                    $transaction['Item_id'], $transaction['Quantity'], new \DateTime($transaction['Date']));
                $t++;
            }
            return $transactionList;
        }
        return null;
    }

    public function getMovementOnWarehouse($warehouseId, \DateTime $date)
    {
        $query = $this->db->prepare('SELECT * FROM Transaction WHERE (Whin_id = ? OR Whout_id  = ?) AND Date > ?');
        $query->execute([$warehouseId, $warehouseId, $date->format("Y-m-d H:i:s")]);
        $res = $query->fetchAll();
        if(isset($res[0])){
            $transactionList =[];
            $t = 0;
            foreach ($res as $transaction)
            {
                $transactionList[$t] = new Transaction($transaction['Whin_id'], $transaction['Whout_id'],
                    $transaction['Item_id'], $transaction['Quantity'], new \DateTime($transaction['Date']));
                $t++;
            }
            return $transactionList;
        }
        return null;
    }

}