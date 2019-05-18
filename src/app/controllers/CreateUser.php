<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Business\User;
use Ergo\Domains\OfficesDao;
use Ergo\Domains\UsersDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Exceptions\UniqueException;
use Ergo\Services\Auth;
use Ergo\Services\DataWrapper;
use Ergo\Services\Validators\ValidatorManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class CreateUser
{
    /** @var ValidatorManagerInterface  */
    private $validatorManager;

    /** @var UsersDao  */
    private $usersDao;

    /** @var OfficesDao  */
    private $officesDao;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var Auth  */
    private $authentication;

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(ValidatorManagerInterface $validatorManager ,UsersDao $usersDao, OfficesDao $officesDao, DataWrapper $dataWrapper, Auth $authentication, LoggerInterface $logger = null)
    {
        $this->validatorManager = $validatorManager;
        $this->usersDao = $usersDao;
        $this->officesDao = $officesDao;
        $this->dataWrapper = $dataWrapper;
        $this->authentication = $authentication;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        if ($this->validatorManager->validate(['create_user'], $request)) {
            $params = $request->getParsedBody();
            $data['email'] = $params['email'];
            $data['hashedPassword'] = $this->authentication->hashPassword($params['password']);
            $data['roles'] = implode(' ', $params['roles']);
            $officesId = array_unique((array) $params['offices_id'], SORT_REGULAR);
            $user = new User($data, $officesId);

            if (!empty($officesId)) {
                try {
                    $officesName = $this->officesDao->getOfficeNameByOfficesId($officesId);
                    $user->setOfficesName($officesName);

                } catch (NoEntityException $e) {
                    return $this->dataWrapper
                        ->addEntity(new Error(Error::ERR_NOT_FOUND, $e->getMessage()))
                        ->throwResponse($response, 404);
                }
            }

            try {
                $this->usersDao->createUser($user);
            } catch (UniqueException $e) {
                return $this->dataWrapper
                    ->addEntity(new Error(Error::ERR_CONFLICT, 'This user already exist'))
                    ->throwResponse($response, 409);
            }

            return $this->dataWrapper
                ->addEntity($user)
                ->throwResponse($response, 201);
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
