<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 25.02.2019
 * Time: 19:31
 */

namespace Ergo\Business;

interface EntityInterface
{
    public function getEntity() : array;

    // TODO getCollectionEntity() : array;
}
