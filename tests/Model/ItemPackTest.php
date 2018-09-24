<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 03.09.2018
 * Time: 8:43
 */

namespace App\Tests\Model;

use PHPUnit\Framework\TestCase;

class ItemPackTest extends TestCase
{
    /**
     * @var ItemPack
     */
    private $itemPack;

    /**
     * @var Item
     */
    private $item;

    protected function setUp()
    {
        $this->item = new Item(1,'1','1',10,1);
        $this->itemPack = new ItemPack($this->item,10);
    }

    protected function tearDown()
    {
        $this->itemPack = NULL;
    }

    public function testAdd()
    {
        $itemPack = new ItemPack($this->item,20);
        $result = $this->itemPack->add($itemPack);
        $this->assertEquals(new ItemPack($this->item, 30), $result);
    }

    public function testRemove()
    {
        $itemPack = new ItemPack($this->item,5);
        $result = $this->itemPack->remove($itemPack);
        $this->assertEquals(new ItemPack($this->item, 5), $result);
    }

    public function testCalcPackPrice()
    {
        $result = $this->itemPack->calcPackPrice();
        $this->assertEquals(100, $result);
    }
}

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

    public function calcPackPrice()
    {
        $sum = $this->getPrice() * $this->quantity;
        return $sum;
    }
}

/**
 * Created by PhpStorm.
 * User: User
 * Date: 03.09.2018
 * Time: 8:40
 */

class Item
{
    private $id;
    private $name;
    private $type;
    private $price;
    private $size;

    public function __construct($id, $name, $type, $price, $size)
    {
        $this->id = $id;
        $this->type = $type;
        $this->name = $name;
        $this->price = $price;
        $this->size = $size;
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    public function getFullInfo()
    {
        return "Id: " . $this->id . "\nName: " . $this->name . "\nType: " . $this->type . "\nPrice: "
            . $this->price . "\nSize: " . $this->size;
    }
}