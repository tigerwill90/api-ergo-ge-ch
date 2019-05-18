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
$container['corsMiddleware'] = static function () : \Tuupola\Middleware\CorsMiddleware
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

$container['jwtAuthentication'] = static function (\Psr\Container\ContainerInterface $c) : Tuupola\Middleware\JwtAuthentication
{
  return new \Tuupola\Middleware\JwtAuthentication([
      'secret' => getenv('API_SECRET'),
      'algorithm' => 'HS256',
      'secure' => true,
      'relaxed' => ['localhost'],
      'logger' => $c->get('appDebug'),
      'error' => function (\Psr\Http\Message\ResponseInterface $response, array $arguments) {
           $error = new \Ergo\Business\Error('Unauthorized', $arguments['message']);
           $body = $response->getBody();
           $body->write(json_encode(['data' => $error->getEntity()]));
           return $response
               ->withHeader('content-type', 'application/json')
               ->withBody($body);
      },
      'rules' => [
          new \Ergo\Services\JwtAuthenticationRuleHelper([
              'path' => [
                  '/users', // protect all method
                  '/offices' => 'POST' // protect only POST method
              ]
          ])
      ]
  ]);
};

$app->add('corsMiddleware');
$app->add('jwtAuthentication');