<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Domains\UsersDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Exceptions\UniqueException;
use Ergo\Services\Auth;
use Ergo\Services\DataWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class RevokeUser
{
    /** @var UsersDao  */
    private $usersDao;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var Auth  */
    private $auth;

    /** @var LoggerInterface  */
    private $logger;

    private const TIMEOUT = 5;

    private const COOKIE_VALUE_LENGTH = 100;

    public function __construct(UsersDao $usersDao, DataWrapper $dataWrapper, Auth $auth, LoggerInterface $logger = null)
    {
        $this->usersDao = $usersDao;
        $this->dataWrapper = $dataWrapper;
        $this->auth = $auth;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {

        $scopes = explode(' ', $request->getAttribute('token')['scope']);
        if (!in_array('admin', $scopes, true)) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_FORBIDDEN, 'Insufficient privileges to revoke a user',
                    [],
                    'Action impossible, vous n\'avez pas les privilèges requis'
                ))
                ->addMeta()
                ->throwResponse($response, 403);
        }

        $attribute = $request->getAttribute('attribute');
        try {
            $user = $this->usersDao->getUser($attribute);
        } catch (NoEntityException $e) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_NOT_FOUND,
                    'No user entity found for this attribute : ' . $attribute,
                    [],
                    'Cet utilisateur n\'existe pas'
                ))
                ->addMeta()
                ->throwResponse($response, 404);
        }

        $cookieValue = $this->auth->generateUniqueCookieValue(self::TIMEOUT, self::COOKIE_VALUE_LENGTH);
        $resetJwt = $this->auth->generateUniqueResetJwt(self::TIMEOUT, 0);
        $user
            ->setResetJwt($resetJwt)
            ->setCookieValue($cookieValue)
            ->setActive(false);

        try {
            $this->usersDao->updateUser($user);
        } catch (NoEntityException $e) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_NOT_FOUND,
                    'No entity found for this user',
                    [],
                    'Désolé, ce compte ne semble plus exister'
                ))
                ->addMeta()
                ->throwResponse($response, 404);
        } catch (UniqueException $e) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_CONFLICT,
                    'An unexpected conflict occurred while updating the database',
                    [],
                    'Désolé, nous n\'avons pas réussi à activer votre compte. Si le problème persiste, merci de prendre contacte avec nous'
                ))
                ->addMeta()
                ->throwResponse($response, 409);
        }

        return $response;
    }
}