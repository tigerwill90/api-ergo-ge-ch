<?php

namespace Ergo\Controllers;

use Dflydev\FigCookies\FigRequestCookies;
use Ergo\Business\Error;
use Ergo\Domains\UsersDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\Auth;
use Ergo\Services\DataWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class Token
{
    /** @var Auth  */
    private $auth;

    /** @var UsersDao  */
    private $usersDao;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(Auth $auth, UsersDao $usersDao, DataWrapper $dataWrapper, LoggerInterface $logger = null)
    {
        $this->auth = $auth;
        $this->usersDao = $usersDao;
        $this->dataWrapper = $dataWrapper;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {

        // cookie not found in header (like expired cookie use case)
        $cookie = FigRequestCookies::get($request, getenv('COOKIE_NAME'));
        if ($cookie->getValue() === null) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_UNAUTHORIZED, 'Unable to renew token, user need to authenticate',
                    [],
                    'Impossible de renouveler le jeton d\'accès, reconnexion requise'
                ))
                ->addMeta()
                ->throwResponse($response, 401);
        }

        // cookie value don't match any value in database
        try {
            $user = $this->usersDao->getUserByToken($cookie->getValue());
        } catch (NoEntityException $e) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_UNAUTHORIZED, 'Unable to renew token, user need to authenticate',
                    [],
                    'Impossible de renouveler le jeton d\'accès, reconnexion requise'
                ))
                ->addMeta()
                ->throwResponse($response, 401);
        }

        // renew jwt
        $exp = time() + getenv('TOKEN_EXPIRATION');
        $data = [
            'user' => $user->getEntity(),
            'authorization' => [
                'access_token' => $this->auth->createJwt($user, $exp),
                'token_type' => 'jwt',
                'expires_in' => $exp,
                'scope' => $user->getRoles()
            ]
        ];

        return $this->dataWrapper
            ->addArray($data)
            ->addMeta()
            ->throwResponse($response);
    }

    /**
     * @param string $message
     * @param array $context
     */
    private function log(string $message, array $context = []) : void
    {
        if ($this->logger !== null) {
            $this->logger->debug($message, $context);
        }
    }
}
