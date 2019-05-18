<?php

namespace Ergo\Controllers;

use Ergo\Domains\UsersDao;
use Ergo\Services\DataWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class CreateUser
{

    /** @var UsersDao  */
    private $usersDao;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var \Ergo\Services\Authentication  */
    private $authentication;

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(UsersDao $usersDao, DataWrapper $dataWrapper, \Ergo\Services\Authentication $authentication, LoggerInterface $logger)
    {
        $this->usersDao = $usersDao;
        $this->dataWrapper = $dataWrapper;
        $this->authentication = $authentication;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        echo 'yolo';
        return $response;
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
