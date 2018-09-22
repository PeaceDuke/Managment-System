<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 03.09.2018
 * Time: 8:40
 */

namespace App\Model;


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