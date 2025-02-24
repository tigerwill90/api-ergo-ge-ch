<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Domains\UsersDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Exceptions\UniqueException;
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
        $token = $request->getAttribute('token');
        $scopes = explode(' ', $token['scope']);
        // check if admin or self update, do not disclose any information about other user, return 404
        if (!in_array('admin', $scopes, true) && $token['user_id'] !== (int) $request->getAttribute('id')) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_NOT_FOUND, 'No user entity found for this user id : ' . $request->getAttribute('id'),
                    [],
                    'Impossible de mettre à jour cet utilisateur. La resource n\'existe pas'
                ))
                ->addMeta()
                ->throwResponse($response, 404);
        }

        if ($this->validatorManager->validate(['update_user'], $request)) {
            try {
                $user = $this->usersDao->getUser((int)$request->getAttribute('id'));
                $params = $request->getParsedBody();

                // Patch update
                if (!empty($params['email'])) {
                    $user->setEmail($params['email']);
                }

                if (!empty($params['password'])) {
                    $user->setHashedPassword($this->auth->hashPassword($params['password']));
                }

                if (!empty($params['first_name'])) {
                    $user->setFirstname($params['first_name']);
                }

                if (!empty($params['last_name'])) {
                    $user->setLastname($params['last_name']);
                }

                if ($params['active'] !== null) {
                    // Only admin user can change activation state
                    if (!in_array('admin', $scopes, true)) {
                        return $this->dataWrapper
                            ->addEntity(new Error(
                                Error::ERR_FORBIDDEN, 'Insufficient privileges to update active state',
                                [],
                                'Action impossible, vous n\'avez pas les privilèges requis pour mettre à jour l\'état de l\'utilisateur'
                            ))
                            ->addMeta()
                            ->throwResponse($response, 403);
                    }
                    $user->setActive($params['active']);
                }

                if (!empty($params['roles'])) {
                    // Only admin user can change roles
                    if (!in_array('admin', $scopes, true)) {
                        return $this->dataWrapper
                            ->addEntity(new Error(
                                Error::ERR_FORBIDDEN, 'Insufficient privileges to update roles',
                                [],
                                'Action impossible, vous n\'avez pas les privilèges requis pour mettre à jour les roles de l\'utilisateur'
                            ))
                            ->addMeta()
                            ->throwResponse($response, 403);
                    }
                    $user->setRoles(implode(' ', $params['roles']));
                }

                // User without admin privilege can only remove offices
                if ($params['offices_id'] !== null) {
                    if (!in_array('admin', $scopes, true) && !empty(array_diff((array) $params['offices_id'], $user->getOfficesId()))) {
                        return $this->dataWrapper
                            ->addEntity(new Error(
                                Error::ERR_FORBIDDEN, 'Insufficient privileges to add new offices',
                                [],
                                'Action impossible, vous n\'avez pas les privilèges requis pour ajouter des nouveaux cabinets'
                            ))
                            ->addMeta()
                            ->throwResponse($response, 403);
                    }
                    $user->setOfficesId((array) $params['offices_id']);
                }

                try {
                    $this->usersDao->updateUser($user);

                    return $this->dataWrapper
                        ->addEntity($user)
                        ->addMeta()
                        ->throwResponse($response);
                } catch (UniqueException $e) {
                    return $this->dataWrapper
                        ->addEntity(new Error(
                            Error::ERR_CONFLICT, 'This user email already exist',
                            [],
                            'Impossible de mettre à jour cet utilisateur, l\'email doit être unique'
                            ))
                        ->addMeta()
                        ->throwResponse($response, 409);
                }
            } catch (NoEntityException $e) {
                return $this->dataWrapper
                    ->addEntity(new Error(
                        Error::ERR_NOT_FOUND, 'No user entity found for this id : ' . $request->getAttribute('id'),
                        [],
                        'Impossible de mettre à jour cet utilisateur. La ressource n\'existe pas'
                    ))
                    ->addMeta()
                    ->throwResponse($response, 404);
            }
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