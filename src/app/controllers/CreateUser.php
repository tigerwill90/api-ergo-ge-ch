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

    private const COOKIE_LENGTH = 100;

    private const TIMEOUT = 5;

    public function __construct(ValidatorManagerInterface $validatorManager ,UsersDao $usersDao, OfficesDao $officesDao, DataWrapper $dataWrapper, Auth $authentication, LoggerInterface $logger = null)
    {
        $this->validatorManager = $validatorManager;
        $this->usersDao = $usersDao;
        $this->officesDao = $officesDao;
        $this->dataWrapper = $dataWrapper;
        $this->authentication = $authentication;
        $this->logger = $logger;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws \Exception
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $scopes = explode(' ', $request->getAttribute('token')['scope']);
        if (!in_array('admin', $scopes, true)) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_FORBIDDEN, 'Insufficient privileges to create a new user',
                    [],
                    'Action impossible, vous n\'avez pas les privilèges requis'
                    ))
                ->throwResponse($response, 403);
        }

        if ($this->validatorManager->validate(['create_user'], $request)) {

            $cookieValue = $this->authentication->generateRandomValue(self::COOKIE_LENGTH);
            $timeout = 0;
            while ($this->usersDao->isCookieValueExist($cookieValue)) {
                $cookieValue = $this->authentication->generateRandomValue(self::COOKIE_LENGTH);
                if ($timeout >= self::TIMEOUT) {
                    throw new \RuntimeException('Unable to generate unique cookie value');
                }
                $timeout++;
            }

            $params = $request->getParsedBody();
            $data['email'] = $params['email'];
            $data['hashedPassword'] = $this->authentication->hashPassword($params['password']);
            $data['roles'] = implode(' ', $params['roles']);
            $data['firstname'] = $params['first_name'];
            $data['lastname'] = $params['last_name'];
            $data['active'] = $params['active'];
            $data['cookieValue'] = $cookieValue;
            $officesId = array_unique((array) $params['offices_id'], SORT_REGULAR);
            $user = new User($data, $officesId);

            if (!empty($officesId)) {
                try {
                    $officesName = $this->officesDao->getOfficeNameByOfficesId($officesId);
                    $user->setOfficesName($officesName);
                } catch (NoEntityException $e) {
                    return $this->dataWrapper
                        ->addEntity(new Error(
                            Error::ERR_NOT_FOUND, $e->getMessage(),
                             [],
                            'Impossible de créer cet utilisateur, le/les cabinet/s n\'existe/nt pas'
                        ))
                        ->throwResponse($response, 404);
                }
            }

            try {
                $this->usersDao->createUser($user);
            } catch (UniqueException $e) {
                return $this->dataWrapper
                    ->addEntity(new Error(
                        Error::ERR_CONFLICT,
                        'This user already exist',
                        [],
                        'Impossible de créer cet utilisateur, l\'adresse email existe déjà'
                    ))
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
                $this->validatorManager->getErrorsMessages(),
                'Une erreur de validation est survenu'
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
