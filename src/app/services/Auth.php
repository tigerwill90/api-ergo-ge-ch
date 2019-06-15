<?php

namespace Ergo\Services;

use Ergo\Business\User;
use Ergo\Domains\UsersDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Exceptions\UniqueException;
use Firebase\JWT\JWT;
use Psr\Log\LoggerInterface;
use RandomLib\Generator;

class Auth
{
    /** @var UsersDao  */
    private $usersDao;

    /** @var Generator  */
    private $generator;

    /** @var LoggerInterface  */
    private $logger;

    private const TOKEN_CHAR_GEN = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function __construct(UsersDao $usersDao, Generator $generator, LoggerInterface $logger = null)
    {
        $this->usersDao = $usersDao;
        $this->generator = $generator;
        $this->logger = $logger;
    }

    /**
     * @param string $password
     * @param User $user
     * @return bool
     * @throws NoEntityException
     * @throws UniqueException
     */
    public function verifyPassword(string $password, User $user) : bool
    {
        if (password_verify($password, $user->getHashedPassword())) {
            if (password_needs_rehash($user->getHashedPassword(), PASSWORD_DEFAULT)) {
                $user->setHashedPassword($this->hashPassword($password));
                try {
                    $this->usersDao->updateUser($user);
                } catch (UniqueException $e) {
                    throw $e;
                } catch (NoEntityException $e) {
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
     * @param User $user
     * @param int $exp
     * @return string
     */
    public function createJwt(User $user, int $exp) : string
    {
        $token = [
            'iss' => getenv('FQDN'),
            'iat' => time(),
            'exp' => $exp,
            'jti' => $this->generator->generateString(10, self::TOKEN_CHAR_GEN),
            'scope' => $user->getRoles(),
            'user_id' => $user->getId(),
            'offices_id' => array_map('intval', $user->getOfficesId())
        ];

        return JWT::encode($token, getenv('API_SECRET'));
    }

    /**
     * @param int $exp
     * @return string
     */
    public function createResetJwt(int $exp) : string
    {
        $token = [
            'iss' => getenv('FQDN'),
            'iat' => time(),
            'exp' => $exp,
            'jti' => $this->generator->generateString(10, self::TOKEN_CHAR_GEN)
        ];

        return JWT::encode($token, getenv('API_RESET_SECRET'));
    }

    /**
     * @param string $jwt
     * @return array
     * @throws \Exception
     */
    public function decodeJwt(string $jwt) : array
    {
        try {
            return (array) JWT::decode($jwt, getenv('API_RESET_SECRET'), array('HS256'));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param int $length
     * @return string
     */
    public function generateRandomValue(int $length) : string
    {
        return $this->generator->generateString($length, self::TOKEN_CHAR_GEN);
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

    /**
     * @param int $maxAttempt
     * @param int $cookieLength
     * @return string
     */
    public function generateUniqueCookieValue(int $maxAttempt, int $cookieLength) : string
    {
        $cookieValue = $this->generateRandomValue($cookieLength);
        $timeout = 0;
        while ($this->usersDao->isCookieValueExist($cookieValue)) {
            $cookieValue = $this->generateRandomValue($cookieLength);
            if ($timeout >= $maxAttempt) {
                throw new \RuntimeException('Unable to generate unique cookie value');
            }
            $timeout++;
        }
        return $cookieValue;
    }

    /**
     * @param int $maxAttempt
     * @param int $exp
     * @return string
     */
    public function generateUniqueResetJwt(int $maxAttempt, int $exp) : string
    {
        $resetJwt = $this->createResetJwt($exp);
        $timeout = 0;
        while ($this->usersDao->isResetJwtExist($resetJwt)) {
            $resetJwt = $this->createResetJwt($exp);
            if ($timeout >= $maxAttempt) {
                throw new \RuntimeException('Unable to generate unique jwt');
            }
            $timeout++;
        }
        return $resetJwt;
    }
}
