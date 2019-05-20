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
        'methods' => ['GET', 'POST', 'PATCH', 'DELETE', 'PUT'],
        'headers.allow' => ['Content-Type'],
        'headers.expose' => [],
        'credentials' => false,
        'cache' => 0
    ]);
};

$container['jwtAuthentication'] = static function () : Tuupola\Middleware\JwtAuthentication
{
  return new \Tuupola\Middleware\JwtAuthentication([
      'secret' => getenv('API_SECRET'),
      'algorithm' => 'HS256',
      'secure' => true,
      'relaxed' => ['localhost'],
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
                  '/users' => ['GET', 'PATCH', 'POST', 'DELETE'],
                  '/offices' => ['POST', 'PUT', 'DELETE'],
                  '/therapists' => ['POST', 'PUT', 'DELETE'],
                  '/categories' => ['POST', 'PUT', 'DELETE']
              ]
          ])
      ]
  ]);
};

$app->add('corsMiddleware');
$app->add('jwtAuthentication');