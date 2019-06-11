<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Domains\UsersDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\DataWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class DeleteUser
{
    /** @var UsersDao  */
    private $usersDao;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(UsersDao $usersDao, DataWrapper $dataWrapper, LoggerInterface $logger = null)
    {
        $this->usersDao = $usersDao;
        $this->dataWrapper = $dataWrapper;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $token = $request->getAttribute('token');
        $scopes = explode(' ', $token['scope']);
        if (!in_array('admin', $scopes, true)) {
            // 403 only for self delete without privilege
            if ($token['user_id'] === (int) $request->getAttribute('id')) {
                return $this->dataWrapper
                    ->addEntity(new Error(
                        Error::ERR_FORBIDDEN, 'Insufficient privileges to delete user',
                        [],
                        'Action impossible, vous n\'avez pas les privilèges requis'
                    ))
                    ->addMeta()
                    ->throwResponse($response, 403);
            }
            // do not disclose any information about other user, return 404
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_NOT_FOUND, 'No entity found for this user id : ' . $request->getAttribute('id'),
                    [],
                    'Suppression impossible, cet utilisateur n\'existe pas'
                ))
                ->addMeta()
                ->throwResponse($response, 404);
        }

        try {
            $this->usersDao->deleteUser((int) $request->getAttribute('id'));
            return $response;
        } catch (NoEntityException $e) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_NOT_FOUND, $e->getMessage(),
                    [],
                    'Suppression impossible, cet utilisateur n\'existe pas'
                    ))
                ->addMeta()
                ->throwResponse($response, 404);
        }
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
