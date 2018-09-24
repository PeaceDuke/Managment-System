<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 03.09.2018
 * Time: 8:37
 */

namespace App\Tests\Model;

use PHPUnit\Framework\TestCase;

class WarehouseTest extends TestCase
{
    /**
     * @var Warehouse
     */
    private $fixture;
    private $items;

    protected function setUp()
    {
        $this->items[1] = new Item(1,'1','1',10,1);
        $this->items[2] = new Item(2,'2','2',30,3);
        $this->fixture[1] = new Warehouse(1,'1', [], 10);
        $this->fixture[2] = new Warehouse(2,'2', [1 => new ItemPack($this->items[1],5)], 10);
        $this->fixture[3] = new Warehouse(3,'3', [1 => new ItemPack($this->items[1],2), 2 =>  new ItemPack($this->items[2],2)], 10);
    }

    protected function tearDown()
    {
        $this->fixture = NULL;
    }

    public function testAddItem()
    {
        $itemPack = new ItemPack($this->items[1],1);
        $this->fixture[1]->addItem($itemPack);
        $this->assertEquals(new Warehouse(1,'1', [1 => new ItemPack($this->items[1],1)], 10), $this->fixture[1]);
        $this->fixture[2]->addItem($itemPack);
        $this->assertEquals(new Warehouse(2,'2', [1 => new ItemPack($this->items[1],6)], 10), $this->fixture[2]);
        $this->fixture[3]->addItem($itemPack);
        $this->assertEquals(new Warehouse(3,'3', [1 => new ItemPack($this->items[1],3), 2 =>  new ItemPack($this->items[2],2)], 10), $this->fixture[3]);
    }

    public function testRemoveItem()
    {
        $itemPack = new ItemPack($this->items[1],1);
        $this->fixture[2]->removeItem($itemPack);
        $this->assertEquals(new Warehouse(2,'2', [1 => new ItemPack($this->items[1],4)], 10), $this->fixture[2]);
        $this->fixture[3]->removeItem($itemPack);
        $this->assertEquals(new Warehouse(3,'3', [1 => new ItemPack($this->items[1],1), 2 => new ItemPack($this->items[2],2)], 10), $this->fixture[3]);
    }

    public function testFindItem()
    {
        $this->assertTrue($this->fixture[2]->findItem(1));
        $this->assertFalse($this->fixture[1]->findItem(1));
    }

    public function testCalcAllItemPrice()
    {
        $this->assertEquals(0, $this->fixture[1]->calcAllItemPrice());
        $this->assertEquals(50, $this->fixture[2]->calcAllItemPrice());
        $this->assertEquals(80, $this->fixture[3]->calcAllItemPrice());

    }
}
/**
 * Created by PhpStorm.
 * User: User
 * Date: 03.09.2018
 * Time: 8:37
 */
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

    public function checkItemPack($id)
    {
        return isset($this->itemPacks[$id]);
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

/**
 * Created by PhpStorm.
 * User: User
 * Date: 03.09.2018
 * Time: 8:43
 */
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