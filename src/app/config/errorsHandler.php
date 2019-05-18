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
$container['errorHandler'] = static function (ContainerInterface $c) : Closure {
    return static function (ServerRequestInterface $request, ResponseInterface $response, Exception $e) use ($c) : ResponseInterface {
        error_log($e->getMessage()); // TODO suppress
        error_log($e->getTraceAsString());
        $body = $response->getBody();
        $error = new \Ergo\Business\Error('Internal server error', 'Oups something goes wrong');
        $body->write(json_encode(['data' => $error->getEntity()]));
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
$container['notAllowedHandler'] = static function (ContainerInterface $c) : Closure {
    return static function (ServerRequestInterface $request, ResponseInterface $response, array $methods) use ($c) : ResponseInterface {
        $body = $response->getBody();
        $resource = explode('/', $request->getUri()->getPath());
        $error = new \Ergo\Business\Error('Method not allowed', $request->getMethod() . ' method is not allowed for ' . end($resource) . ' resource');
        $body->write(json_encode(['data' => $error->getEntity()]));
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
$container['notFoundHandler'] = static function (ContainerInterface $c) : Closure {
    return static function (ServerRequestInterface $request, ResponseInterface $response) use ($c) : ResponseInterface {
        $body = $response->getBody();
        $resource = explode('/', $request->getUri()->getPath());
        $error = new \Ergo\Business\Error('Not found', end($resource) . ' isn\'t a resource');
        $body->write(json_encode(['data' => $error->getEntity()]));
        return $response
            ->withBody($body)
            ->withStatus(404)
            ->withHeader('Content-Type', 'application/json');
        };
};

