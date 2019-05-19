<?php

namespace Ergo\Services;

use Ergo\Business\User;
use Ergo\Domains\UsersDao;
use Firebase\JWT\JWT;
use Psr\Log\LoggerInterface;
use RandomLib\Generator;

class Auth
{
    /** @var UsersDao  */
    private $userDao;

    /** @var Generator  */
    private $generator;

    /** @var LoggerInterface  */
    private $logger;

    private const TOKEN_CHAR_GEN = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function __construct(UsersDao $usersDao, Generator $generator, LoggerInterface $logger = null)
    {
        $this->userDao = $usersDao;
        $this->generator = $generator;
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


    public function createJwt(User $user, int $exp) : string
    {
        $token = [
            'iss' => getenv('DOMAIN_NAME'),
            'iat' => time(),
            'exp' => $exp,
            'jti' => $this->generator->generateString(10, self::TOKEN_CHAR_GEN),
            'scope' => $user->getRoles(),
            'user_id' => $user->getId()
        ];

        return JWT::encode($token, getenv('API_SECRET'));
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
