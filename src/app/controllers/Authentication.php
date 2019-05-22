<?php

namespace Ergo\Controllers;

use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use Ergo\Business\Error;
use Ergo\Domains\UsersDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Exceptions\UniqueException;
use Ergo\Services\DataWrapper;
use Ergo\Services\Auth;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class Authentication
{
    /** @var UsersDao */
    private $usersDao;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var Auth  */
    private $auth;

    /** @var LoggerInterface  */
    private $logger;

    private const COOKIE_LENGTH = 100;

    private const TIMEOUT = 5;

    public function __construct(UsersDao $usersDao, DataWrapper $dataWrapper, Auth $auth, LoggerInterface $logger = null)
    {
        $this->usersDao = $usersDao;
        $this->dataWrapper = $dataWrapper;
        $this->logger = $logger;
        $this->auth = $auth;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws \Exception
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // TODO protect with recaptcha
        $header = $request->getHeader('Authorization');

        // empty header, 401
        if (empty($header)) {
            return $this->dataWrapper
                ->addEntity(new Error(Error::ERR_UNAUTHORIZED, 'Use http basic authentication to connect'))
                ->addMeta()
                ->throwResponse($response, 401);
        }

        $basicAuth = explode('Basic ', $header[0]);
        if (count($basicAuth) !== 2) {
            return $this->dataWrapper
                ->addEntity(new Error(Error::ERR_UNAUTHORIZED, 'Incorrect http basic authentication scheme'))
                ->addMeta()
                ->throwResponse($response, 401);
        }

        // explode at the first occurrence of delimiter, keep password with special ":" char
        $emailPassword = explode(':', base64_decode($basicAuth[1]), 2);
        if (count($emailPassword) !== 2) {
            return $this->dataWrapper
                ->addEntity(new Error(Error::ERR_UNAUTHORIZED, 'Incorrect http basic authentication scheme'))
                ->addMeta()
                ->throwResponse($response, 401);
        }

        try {
            $user = $this->usersDao
                ->authenticateUser($emailPassword[0]);

            if (!$this->auth->verifyPassword($emailPassword[1], $user)) {
                return $this->dataWrapper
                    ->addEntity(new Error(Error::ERR_UNAUTHORIZED, 'Invalid email or password'))
                    ->addMeta()
                    ->throwResponse($response, 401);
            }
        } catch (NoEntityException $e) {
            return $this->dataWrapper
                ->addEntity(new Error(Error::ERR_UNAUTHORIZED, 'Invalid email or password'))
                ->addMeta()
                ->throwResponse($response, 401);
        } catch (\Exception $e) {
            throw $e;
        }

        $exp = time() + getenv('TOKEN_EXPIRATION');

        $data = [
          'user' => $user->getEntity(),
          'authorization' => [
              'access_token' => $this->auth->createJwt($user, $exp),
              'token_type' => 'jwt',
              'expires_in' => $exp,
              'scope' => $user->getRoles()
          ]
        ];

        $cookieValue = $this->auth->generateRandomValue(self::COOKIE_LENGTH);
        $timeout = 0;
        while ($this->usersDao->isCookieValueExist($cookieValue)) {
            $cookieValue = $this->auth->generateRandomValue(self::COOKIE_LENGTH);
            if ($timeout >= self::TIMEOUT) {
                throw new \RuntimeException('Unable to generate unique cookieValue');
            }
            $timeout++;
        }

        $response = FigResponseCookies::set($response, SetCookie::create('ase')
            ->withHttpOnly()
            ->withDomain(getenv('DOMAIN_NAME'))
            ->withPath('/')
            ->withExpires(time() + getenv('COOKIE_EXPIRATION'))
            ->withValue($cookieValue)
            ->withSecure(!filter_var(getenv('DEBUG'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE))
        );

        // store unique cookie value in database
        $user->setCookieValue($cookieValue);
        try {
            $this->usersDao->updateUser($user);
        } catch (UniqueException $e) {
            throw new \RuntimeException($e->getMessage());
        }

        return $this->dataWrapper
            ->addArray($data)
            ->addMeta()
            ->throwResponse($response);
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
