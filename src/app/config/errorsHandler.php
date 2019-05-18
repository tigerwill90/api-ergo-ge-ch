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
        error_log($e->getTraceAsString());
        $body = $response->getBody();
        $error = new \Ergo\Business\Error('Internal server error', 'Oups something goes wrong');
        $data['data'] = $error->getEntity();
        $body->write(json_encode($data));
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
        $error = new \Ergo\Business\Error('Method not allowed', $request->getMethod() . ' method is not allowed');
        $data['data'] = $error->getEntity();
        $body->write(json_encode($data));
        return $response
            ->withBody($body)
            ->withStatus(405)
            ->withHeader('Allow', implode(', ', $methods))
            ->withHeader('Content-Type', 'application/json');
    };
};

/**
 * @param ContainerInterface $c
 * @return Closure
 */
$container['notFoundHandler'] = function (ContainerInterface $c) : Closure {
    return function (ServerRequestInterface $request, ResponseInterface $response) use ($c) : ResponseInterface {
        $body = $response->getBody();
        $body->write(json_encode(['error' => 'Resource not found']));
        return $response
            ->withBody($body)
            ->withStatus(404)
            ->withHeader('Content-Type', 'application/json');
        };
};

