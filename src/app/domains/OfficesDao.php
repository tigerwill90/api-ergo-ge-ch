<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 02.12.2018
 * Time: 01:43
 */

namespace Ergo\Domains;

use PDO;

class OfficesDao
{
    /* @var PDO */
    private $pdo;

    public function __construct( $pdo)
    {
        $this->pdo = $pdo;
    }
}