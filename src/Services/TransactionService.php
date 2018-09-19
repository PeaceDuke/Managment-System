<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 12.09.2018
 * Time: 12:44
 */

namespace App\Services;


use App\Model\ItemPack;
use App\Model\Transaction;
use App\Model\Warehouse;

class TransactionService extends BaseService
{
    public function __construct($db)
    {
        parent::__construct($db);
    }

    public function addNewTransaction($warehouseIn, $warehouseOut, array $items)
    {
        $query = $this->db->prepare('SELECT MAX(id) FROM Transaction');
        $query->execute();
        $id = $query->fetch();
        $id = $id['MAX(id)'];
        $id++;
        foreach ($items as $item) {
            $query = $this->db->prepare('INSERT INTO Transaction (id, Item_id, Whin_id, Whout_id, Quantity, Date) 
                VALUES(:id, :item, :in, :out, :quantity, :date)');
            $in = isset($warehouseIn) ? $warehouseIn->getId() : null;
            $out = isset($warehouseOut) ? $warehouseOut->getId() : null;
            $time = new \DateTime();
            $query->bindParam(':id', $id, \PDO::PARAM_INT);
            $query->bindParam(':item', $item->getId(), \PDO::PARAM_INT);
            $query->bindParam(':in', $in, \PDO::PARAM_INT);
            $query->bindParam(':out', $out, \PDO::PARAM_INT);
            $query->bindParam(':quantity', $item->getQuantity(), \PDO::PARAM_INT);
            $query->bindParam(':date', $time->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
            $query->execute();
        }
    }

    public function getItemMovement($itemId, \DateTime $date)
    {
        $query = $this->db->prepare('SELECT * FROM Transaction WHERE Item_id = :id AND Date > :date');
        $query->bindParam(':id', $itemId, \PDO::PARAM_INT);
        $query->bindParam(':date', $date->format("Y-m-d H:i:s"), \PDO::PARAM_STR);
        $query->execute();
        $res = $query->fetchAll();
        if(isset($res[0])){
            $transactionList =[];
            $t = 0;
            foreach ($res as $transaction)
            {
                $transactionList[$t] = new Transaction($transaction['Whin_id'], $transaction['Whout_id'], $transaction['Item_id'], $transaction['Quantity'], new \DateTime($transaction['Date']));
                $t++;
            }
            return $transactionList;
        }
    }

    public function getMovementOnWarehouse($warehouseId, \DateTime $date)
    {
        $query = $this->db->prepare('SELECT * FROM Transaction WHERE (Whin_id = :id OR Whout_id  = :id) AND Date > :date');
        $query->bindParam(':id', $warehouseId, \PDO::PARAM_INT);
        $query->bindParam(':date', $date->format("Y-m-d H:i:s"), \PDO::PARAM_STR);
        $query->execute();
        $res = $query->fetchAll();
        if(isset($res[0])){
            $transactionList =[];
            $t = 0;
            foreach ($res as $transaction)
            {
                $transactionList[$t] = new Transaction($transaction['Whin_id'], $transaction['Whout_id'], $transaction['Item_id'], $transaction['Quantity'], new \DateTime($transaction['Date']));
                $t++;
            }
            return $transactionList;
        }
    }

}