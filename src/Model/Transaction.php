<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 12.09.2018
 * Time: 12:10
 */

namespace App\Model;


class Transaction
{
    private $warehouseIn;
    private $warehouseOut;
    private $item;
    private $quantity;
    /**
     * @var \DateTime
     */
    private $date;

    public function __construct($warehouseIn, $warehouseOut, $item, $quantity, \DateTime $date)
    {
        $this->item = $item;
        $this->date = $date;
        $this->quantity = $quantity;
        $this->warehouseIn = $warehouseIn;
        $this->warehouseOut = $warehouseOut;
    }

    /**
     * @return Warehouse
     */
    public function getWarehouseIn()
    {
        return $this->warehouseIn;
    }

    /**
     * @return Warehouse
     */
    public function getWarehouseOut()
    {
        return $this->warehouseOut;
    }

    public function getItem()
    {
        return $this->item;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }




}