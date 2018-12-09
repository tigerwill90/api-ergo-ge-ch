<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 02.12.2018
 * Time: 01:31
 */

namespace Ergo\Business;


class Therapist
{
    // @var String[]
    private $emails = [];

    // @var String[]
    private $phones = [];

    // @var String[]
    private $fax = [];

    public function __construct(array $therapist)
    {
    }
}