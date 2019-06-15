<?php

namespace Ergo\Controllers;

use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use Ergo\Business\Error;
use Ergo\Domains\UsersDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Exceptions\UniqueException;
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

    private const COOKIE_LENGTH = 100;

    private const TIMEOUT = 5;

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

        $cookieValue = $this->auth->generateUniqueCookieValue(self::TIMEOUT, self::COOKIE_LENGTH);

        $response = FigResponseCookies::set($response, SetCookie::create('ase')
            ->withHttpOnly()
            ->withDomain(getenv('DOMAIN_NAME'))
            ->withPath('/')
            ->withExpires(time() + getenv('COOKIE_EXPIRATION'))
            ->withValue($cookieValue)
            ->withSecure(!filter_var(getenv('DEBUG'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE))
        );

        // store unique cookie value in database
        $user->setCookieValue($cookieValue);
        try {
            $this->usersDao->updateUser($user);
        } catch (UniqueException $e) {
            throw new \RuntimeException($e->getMessage());
        } catch (NoEntityException $e) {
            throw new \RuntimeException($e->getMessage());
        }

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
