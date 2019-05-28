<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Domains\UsersDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\DataWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class ReadUsers
{
    private $usersDao;
    private $dataWrapper;
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
        // Only admin can list users
        if (!in_array('admin', $scopes, true)) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_FORBIDDEN, 'Insufficient privileges to list users',
                    [],
                    'Action impossible, vous n\'avez pas les privilèges requis'
                ))
                ->throwResponse($response, 403);
        }

        $params = $request->getQueryParams();
        try {
            $users = $this->usersDao->getUsers($params['attribute'], $params['sort']);
            return $this->dataWrapper
                ->addCollection($users)
                ->throwResponse($response, 200);
        } catch (NoEntityException $e) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_NOT_FOUND, $e->getMessage(),
                    [],
                    'Aucun utilisateur trouvé'
                ))
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
