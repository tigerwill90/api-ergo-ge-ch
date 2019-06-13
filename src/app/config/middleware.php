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
        'headers.allow' => ['Content-Type', 'Authorization'],
        'headers.expose' => [],
        'credentials' => true,
        'cache' => 0
    ]);
};

/**
 * @return \Tuupola\Middleware\JwtAuthentication
 */
$container['jwtAuthentication'] = static function () : Tuupola\Middleware\JwtAuthentication
{
    return new \Tuupola\Middleware\JwtAuthentication([
        'secret' => getenv('API_SECRET'),
        'algorithm' => 'HS256',
        'secure' => true,
        'relaxed' => ['localhost'],
        'error' => function (\Psr\Http\Message\ResponseInterface $response, array $arguments) {
            $error = new \Ergo\Business\Error(
                \Ergo\Business\Error::ERR_UNAUTHORIZED, $arguments['message'],
                [],
                'Le jeton d\'accès est invalide ou n\'a pas été trouvé'
            );
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
                    '/categories' => ['POST', 'PUT', 'DELETE'],
                ],
                'ignore' => [
                    '/users/[0-9]+/offices' => 'GET',
                    '/users/activate' => 'PATCH'
                ]
            ])
        ]
    ]);
};

/**
 * @return Closure
 */
$container['httpsOnly'] = static function () : Closure {
    return static function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, callable $next) : \Psr\Http\Message\ResponseInterface {
        if ($request->getUri()->getScheme() !== 'https' && !filter_var(getenv('DEBUG'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)) {
            $error = new \Ergo\Business\Error(
                'Upgrade Required',
                'Only accept https connection',
                [],
                'Impossible de contacter le serveur, la connection n\'est pas sécurisée'
            );
            $body = $response->getBody();
            $body->write(json_encode($error->getEntity()));
            return $response->withBody($body)->withHeader('Content-Type', 'application/json')->withStatus(426);
        }

        return $next($request, $response);
    };
};

$app->add('jwtAuthentication');
$app->add('corsMiddleware');
$app->add('httpsOnly');