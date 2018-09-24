<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 03.09.2018
 * Time: 8:37
 */

namespace App\Model;


class Warehouse
{
    private $id;
    private $address;
    /**
     * @var array
     */
    private $itemPacks;
    private $capacity;
    private $remainingSpace;

    public function __construct($id, $address, array $itemsPacks, $capacity)
    {
        $this->id = $id;
        $this->address = $address;
        $this->itemPacks = $itemsPacks;
        $this->capacity = $capacity;
        $this->remainingSpace = $capacity;
        foreach ($itemsPacks as $itemsPack) {
            $this->remainingSpace -= $itemsPack->getSize() * $itemsPack->getQuantity();
        }
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return array
     */
    public function getItemPacks()
    {
        return $this->itemPacks;
    }

    /**
     * @return mixed
     */
    public function getCapacity()
    {
        return $this->capacity;
    }

    public function getItemPack($id)
    {
        return $this->itemPacks[$id];
    }

    public function getRemainingSpace()
    {
        return $this->remainingSpace;
    }

    public function addItem(ItemPack $itemPack)
    {
        if(isset($this->itemPacks[$itemPack->getId()])) {
            $this->itemPacks[$itemPack->getId()] = $this->itemPacks[$itemPack->getId()]->add($itemPack);
        }
        else{
            $this->itemPacks[$itemPack->getId()] = $itemPack;
        }
        $this->remainingSpace -= $itemPack->getSize() * $itemPack->getQuantity();
    }

    public function removeItem(ItemPack $itemPack)
    {
        if (isset($this->itemPacks[$itemPack->getId()])) {
            if ($this->itemPacks[$itemPack->getId()]->getQuantity() == $itemPack->getQuantity()) {
                unset($this->itemPacks[$itemPack->getId()]);
            } else {
                $this->itemPacks[$itemPack->getId()] = $this->itemPacks[$itemPack->getId()]->remove($itemPack);
            }
        }
        $this->remainingSpace += $itemPack->getSize() * $itemPack->getQuantity();
    }

    public function findItem($id)
    {
        if (isset($this->itemPacks[$id])) {
            return true;
        }
        return false;
    }

    public function calcAllItemPrice()
    {
        $sum = 0;
        foreach ($this->itemPacks as $itemPack)
        {
            $sum += $itemPack->getPrice() * $itemPack->getQuantity();
        }
        return $sum;
    }

}