<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 27.11.2018
 * Time: 21:59
 */
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @param ContainerInterface $c
 * @return Closure
 */
$container['errorHandler'] = function (ContainerInterface $c) : Closure {
    return function (ServerRequestInterface $request, ResponseInterface $response, Exception $e) use ($c) : ResponseInterface {
        error_log($e->getMessage()); // TODO suppress
        $body = $response->getBody();
        $body->write(json_encode(['error' => 'something goes wrong']));
        return $response
                ->withBody($body)
                ->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
    };
};

/**
 * @param ContainerInterface $c
 * @return Closure
 */
$container['notAllowedHandler'] = function (ContainerInterface $c) : Closure {
    return function (ServerRequestInterface $request, ResponseInterface $response, array $methods) use ($c) : ResponseInterface {
        $body = $response->getBody();
        $body->write(json_encode(['error' => 'method not allowed']));
        return $response
            ->withBody($body)
            ->withStatus(405)
            ->withHeader('Allow', implode(', ', $methods));
    };
};

/**
 * @param ContainerInterface $c
 * @return Closure
 */
$container['notFoundHandler'] = function (ContainerInterface $c) : Closure {
    return function (ServerRequestInterface $request, ResponseInterface $response) use ($c) : ResponseInterface {
        $body = $response->getBody();
        $body->write(json_encode(['error' => 'resource not found']));
        return $response
            ->withBody($body)
            ->withStatus(404);
        };
};

