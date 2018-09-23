<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 03.09.2018
 * Time: 8:43
 */

namespace App\Tests\Model;

use PHPUnit\Framework\TestCase;
require __DIR__ . '/../../src/Model/ItemPack.php';
require __DIR__ . '/../../src/Model/Item.php';
use App\Model\ItemPack;
use App\Model\Item;

class ItemPackTest extends TestCase
{
    /**
     * @var ItemPack
     */
    private $itemPack;
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