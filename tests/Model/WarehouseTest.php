<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 03.09.2018
 * Time: 8:37
 */

namespace App\Tests\Model;

use PHPUnit\Framework\TestCase;
use App\Model\Warehouse;
use App\Model\ItemPack;
use App\Model\Item;

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
        $this->fixture[2] = new Warehouse(2,'2', [new ItemPack($this->items[1],5)], 10);
        $this->fixture[3] = new Warehouse(3,'3', [new ItemPack($this->items[1],2), new ItemPack($this->items[2],2)], 10);
    }

    protected function tearDown()
    {
        $this->fixture = NULL;
    }

    public function testAddItem()
    {
        $itemPack = new ItemPack($this->items[1],1);
        $this->fixture[1]->addItem($itemPack);
        $this->assertEquals(new Warehouse(1,'1', [new ItemPack($this->items[1],1)], 10), $this->fixture[1]);
        $this->fixture[2]->addItem($itemPack);
        $this->assertEquals(new Warehouse(2,'2', [new ItemPack($this->items[1],6)], 10), $this->fixture[2]);
        $this->fixture[3]->addItem($itemPack);
        $this->assertEquals(new Warehouse(3,'3', [new ItemPack($this->items[1],3), new ItemPack($this->items[2],2)], 10), $this->fixture[3]);
    }

    public function testRemoveItem()
    {
        $itemPack = new ItemPack($this->items[1],1);
        $this->fixture[2]->addItem($itemPack);
        $this->assertEquals(new Warehouse(2,'2', [new ItemPack($this->items[1],4)], 10), $this->fixture[2]);
        $this->fixture[3]->addItem($itemPack);
        $this->assertEquals(new Warehouse(3,'3', [new ItemPack($this->items[1],2), new ItemPack($this->items[2],2)], 10), $this->fixture[3]);
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