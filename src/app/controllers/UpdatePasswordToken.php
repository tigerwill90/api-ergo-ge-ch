<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Business\User;
use Ergo\Domains\UsersDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Exceptions\UniqueException;
use Ergo\Services\Auth;
use Ergo\Services\DataWrapper;
use Ergo\Services\Validators\ValidatorManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class UpdatePasswordToken
{
    /** @var ValidatorManager  */
    private $validatorManager;

    /** @var UsersDao  */
    private $usersDao;

    /** @var Auth  */
    private $auth;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var LoggerInterface  */
    private $logger;

    private const TIMEOUT = 5;
    private const RANDOM_VALUE_LENGTH = 100;

    public function __construct(ValidatorManager $validatorManager, UsersDao $usersDao, Auth $auth, DataWrapper $dataWrapper, LoggerInterface $logger = null)
    {
        $this->validatorManager = $validatorManager;
        $this->usersDao = $usersDao;
        $this->auth = $auth;
        $this->dataWrapper = $dataWrapper;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        if ($this->validatorManager->validate(['update_password_token'], $request)) {
            $params = $request->getParsedBody();

            try {
                $this->auth->decodeJwt($params['token']);
            } catch (\Exception $e) {
                return $this->dataWrapper
                    ->addEntity(new Error(
                        Error::ERR_UNAUTHORIZED,
                        'The reset token is invalid or expired',
                        [],
                        'Désolé, nous n\'avons pas réussi à activer votre compte. Si le problème persiste, merci de prendre contacte avec nous'
                    ))
                    ->addMeta()
                    ->throwResponse($response, 401);
            }

            try {
                $user = $this->usersDao->getUserByToken($params['token'], 'token');
                try {
                    $this->resetToken($user);
                    $user->setHashedPassword($this->auth->hashPassword(base64_decode($params['password'])));
                    $user->setActive(true);
                    $this->usersDao->updateUser($user);
                } catch (NoEntityException $e) {
                    return $this->dataWrapper
                        ->addEntity(new Error(
                            Error::ERR_NOT_FOUND,
                            'No entity found for this user',
                            [],
                            'Désolé, ce compte ne semble pas exister'
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
                        ->throwResponse($response, 401);
                }
            } catch (NoEntityException $e) {
                return $this->dataWrapper
                    ->addEntity(new Error(
                        Error::ERR_UNAUTHORIZED,
                        'The reset token is invalid or expired',
                        [],
                        'Désolé, nous n\'avons pas réussi à activer votre compte. Si le problème persiste, merci de prendre contacte avec nous'
                    ))
                    ->addMeta()
                    ->throwResponse($response, 401);
            }

            return $this->dataWrapper
                ->addEntity($user)
                ->addMeta()
                ->throwResponse($response);
        }

        return $this->dataWrapper
            ->addEntity(new Error(
                Error::ERR_BAD_REQUEST,
                'The request could not be understood by the server due to malformed syntax',
                $this->validatorManager->getErrorsMessages(),
                'Une erreur de validation est survenu'
            ))
            ->addMeta()
            ->throwResponse($response, 400);
    }

    /**
     * @param User $user
     * @throws NoEntityException
     * @throws UniqueException
     */
    public function resetToken(User $user): void
    {
        $randomValue = $this->auth->generateRandomValue(self::RANDOM_VALUE_LENGTH);
        $timeout = 0;
        while ($this->usersDao->isResetJwtExist($randomValue)) {
            $randomValue = $this->auth->generateRandomValue(self::RANDOM_VALUE_LENGTH);
            if ($timeout >= self::TIMEOUT) {
                throw new \RuntimeException('Unable to generate unique random value');
            }
            $timeout++;
        }

        $user->setResetJwt($randomValue);
        $this->usersDao->updateUser($user);
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