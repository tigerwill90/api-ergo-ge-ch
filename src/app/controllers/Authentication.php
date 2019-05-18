<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Domains\UsersDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\DataWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class Authentication
{
    /** @var UsersDao */
    private $userDao;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var \Ergo\Services\Authentication  */
    private $authentication;

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(UsersDao $usersDao, DataWrapper $dataWrapper, \Ergo\Services\Authentication $authentication, LoggerInterface $logger = null)
    {
        $this->userDao = $usersDao;
        $this->dataWrapper = $dataWrapper;
        $this->logger = $logger;
        $this->authentication = $authentication;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws \Exception
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $header = $request->getHeader('Authorization');

        // empty header, 401

        if (empty($header)) {
            return $this->dataWrapper
                ->addEntity(new Error('Unauthorized', 'Use http basic authentication to connect'))
                ->throwResponse($response, 401);
        }

        $basicAuth = explode('Basic ', $header[0]);
        if (count($basicAuth) !== 2) {
            return $this->dataWrapper
                ->addEntity(new Error('Unauthorized', 'Incorrect http basic authentication scheme'))
                ->throwResponse($response, 401);
        }

        $emailPassword = explode(':', base64_decode($basicAuth[1]));
        if (count($emailPassword) !== 2) {
            return $this->dataWrapper
                ->addEntity(new Error('Unauthorized', 'Incorrect http basic authentication scheme'))
                ->throwResponse($response, 401);
        }

        try {
            $user = $this->userDao
                ->authenticateUser($emailPassword[0]);

            if (!$this->authentication->verifyPassword($emailPassword[1], $user)) {
                return $this->dataWrapper
                    ->addEntity(new Error('Unauthorized', 'Invalid email or password'))
                    ->throwResponse($response, 401);
            }
        } catch (NoEntityException $e) {
            return $this->dataWrapper
                ->addEntity(new Error('Unauthorized', 'Invalid email or password'))
                ->throwResponse($response, 401);
        } catch (\Exception $e) {
            throw $e;
        }

        return $this->dataWrapper
            ->addEntity($user)
            ->throwResponse($response, 401);
    }

    /**
     * @param string $message
     * @param array|null $context
     */
    private function log(string $message, array $context = []) : void
    {
        if ($this->logger !== null) {
            $this->logger->debug($message, $context);
        }
    }
}
