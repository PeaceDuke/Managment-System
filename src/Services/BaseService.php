<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 11.09.2018
 * Time: 17:45
 */

namespace App\Services;

use App\Services\ItemService;


class BaseService
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
}