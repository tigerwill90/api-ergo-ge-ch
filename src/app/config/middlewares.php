<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 27.11.2018
 * Time: 22:30
 */

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Set json media-type for each response
 * @return Closure
 */
$container['mediaType'] = function () : Closure {
    return function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
        return $next($request, $response)->withHeader('Content-Type', 'application/json');
    };
};

$app->add('mediaType');