<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 19.12.2018
 * Time: 15:40
 */

/**
 * @return \Tuupola\Middleware\CorsMiddleware
 */
$container['corsMiddleware'] = function () : \Tuupola\Middleware\CorsMiddleware
{
    return new \Tuupola\Middleware\CorsMiddleware([
        'origin' => explode(',', getenv('ORIGIN')),
        'methods' => ['GET'],
        'headers.allow' => [],
        'headers.expose' => [],
        'credentials' => false,
        'cache' => 0
    ]);
};

$app->add('corsMiddleware');