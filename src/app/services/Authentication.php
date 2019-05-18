<?php

namespace Ergo\Services;

use Ergo\Business\User;
use Ergo\Domains\UsersDao;
use Psr\Log\LoggerInterface;

class Authentication
{
    /** @var UsersDao  */
    private $userDao;

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(UsersDao $usersDao, LoggerInterface $logger = null)
    {
        $this->userDao = $usersDao;
        $this->logger = $logger;
    }

    /**
     * @param string $password
     * @param User $user
     * @return bool
     * @throws \Exception
     */
    public function verifyPassword(string $password, User $user) : bool
    {
        if (password_verify($password, $user->getHashedPassword())) {
            if (password_needs_rehash($user->getHashedPassword(), PASSWORD_DEFAULT)) {
                $user->setHashedPassword($this->hashPassword($password));
                try {
                    $this->userDao->updateUser($user);
                } catch (\Exception $e) {
                    throw $e;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @param string $password
     * @return string
     */
    public function hashPassword(string $password) : string
    {
        return password_hash($password, PASSWORD_DEFAULT);
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
