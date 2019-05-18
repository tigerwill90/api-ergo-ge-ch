<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Domains\UsersDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\Auth;
use Ergo\Services\DataWrapper;
use Ergo\Services\Validators\ValidatorManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class UpdateUser
{
    /** @var ValidatorManagerInterface  */
    private $validatorManager;

    /** @var UsersDao  */
    private $usersDao;

    /** @var Auth  */
    private $auth;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(ValidatorManagerInterface $validatorManager, UsersDao $usersDao, Auth $auth, DataWrapper $dataWrapper, LoggerInterface $logger = null)
    {
        $this->validatorManager = $validatorManager;
        $this->usersDao = $usersDao;
        $this->auth = $auth;
        $this->dataWrapper = $dataWrapper;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        if ($this->validatorManager->validate(['update_user'], $request)) {
            try {
                $user = $this->usersDao->getUser((int)$request->getAttribute('id'));
            } catch (NoEntityException $e) {
                return $this->dataWrapper
                    ->addEntity(new Error(Error::ERR_NOT_FOUND, 'No user entity found for this id : ' . $request->getAttribute('id')))
                    ->throwResponse($response, 404);
            }
        }

        return $this->dataWrapper
            ->addEntity(new Error(
                Error::ERR_BAD_REQUEST,
                'The request could not be understood by the server due to malformed syntax',
                $this->validatorManager->getErrorsMessages()
            ))
            ->throwResponse($response, 400);
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