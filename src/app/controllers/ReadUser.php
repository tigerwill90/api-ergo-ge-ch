<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Domains\UsersDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\DataWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class ReadUser
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
        try {
            $user = $this->usersDao->getUser($request->getAttribute('attribute'));

            $token = $request->getAttribute('token');
            $scopes = explode(' ', $token['scope']);
            // check if admin or self read, do not disclose any information about other user, return 404
            if (!in_array('admin', $scopes, true) && $token['user_id'] !== $user->getId()) {
                return $this->dataWrapper
                    ->addEntity(new Error(Error::ERR_NOT_FOUND, 'No user entity found for this attribute : ' . $request->getAttribute('attribute')))
                    ->throwResponse($response, 404);
            }

            return $this->dataWrapper
                ->addEntity($user)
                ->throwResponse($response);
        } catch (NoEntityException $e) {
            return $this->dataWrapper
                ->addEntity(new Error(Error::ERR_NOT_FOUND, 'No user entity found for this attribute : ' . $request->getAttribute('attribute')))
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
