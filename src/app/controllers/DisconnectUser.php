<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Domains\UsersDao;
use Ergo\Exceptions\UniqueException;
use Ergo\Services\Auth;
use Ergo\Services\DataWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class DisconnectUser
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
        $token = $request->getAttribute('token');
        $scopes = explode(' ', $token['scope']);
        $id = $request->getAttribute('id');
        // check if admin or self update, do not disclose any information about other user, return 404
        if ($token['user_id'] !== (int) $id && !in_array('admin', $scopes, true)) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_NOT_FOUND, 'No user entity found for this user id : ' . $request->getAttribute('id'),
                    [],
                    'Déconnexion impossible, cet utilisateur n\'existe pas'
                ))
                ->addMeta()
                ->throwResponse($response, 404);
        }

        $cookieValue = $this->auth->generateRandomValue(self::COOKIE_LENGTH);
        $timeout = 0;
        while ($this->usersDao->isCookieValueExist($cookieValue)) {
            $cookieValue = $this->auth->generateRandomValue(self::COOKIE_LENGTH);
            if ($timeout >= self::TIMEOUT) {
                throw new \RuntimeException('Unable to generate unique cookie value');
            }
            $timeout++;
        }

        try {
            $this->usersDao->updateCookieValue($id, $cookieValue);
        } catch (UniqueException $e) {
            throw new \RuntimeException($e->getMessage());
        }

        return $response;
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
