<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 03.09.2018
 * Time: 8:43
 */

namespace App\Model;


class ItemPack
{
    /**
     * @var Item
     */
    private $item;
    private $quantity;

    public function __construct(Item $item, $quantity)
    {
        $this->quantity = $quantity;
        $this->item = $item;
    }

    /**
     * @return Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    public function getId()
    {
        return $this->item->getId();
    }

    public function getName()
    {
        return $this->item->getName();
    }

    public function getPrice()
    {
        return $this->item->getPrice();
    }

    public function getSize()
    {
        return $this->item->getSize();
    }

    public function add(ItemPack $itemPack)
    {
        return new self($itemPack->getItem(), $this->quantity + $itemPack->getQuantity());
    }

    public function remove(ItemPack $itemPack)
    {
        return new self($itemPack->getItem(), $this->quantity - $itemPack->getQuantity());
    }
}