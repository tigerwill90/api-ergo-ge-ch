<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Domains\UsersDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\DataWrapper;
use Ergo\Services\Auth;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class Authentication
{
    /** @var UsersDao */
    private $userDao;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var Auth  */
    private $auth;

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(UsersDao $usersDao, DataWrapper $dataWrapper, Auth $auth, LoggerInterface $logger = null)
    {
        $this->userDao = $usersDao;
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
        $header = $request->getHeader('Authorization');

        // empty header, 401

        if (empty($header)) {
            return $this->dataWrapper
                ->addEntity(new Error(Error::ERR_UNAUTHORIZED, 'Use http basic authentication to connect'))
                ->throwResponse($response, 401);
        }

        $basicAuth = explode('Basic ', $header[0]);
        if (count($basicAuth) !== 2) {
            return $this->dataWrapper
                ->addEntity(new Error(Error::ERR_UNAUTHORIZED, 'Incorrect http basic authentication scheme'))
                ->throwResponse($response, 401);
        }

        // explode at the first occurrence of delimiter, keep password with special ":" char
        $emailPassword = explode(':', base64_decode($basicAuth[1]), 2);
        if (count($emailPassword) !== 2) {
            return $this->dataWrapper
                ->addEntity(new Error(Error::ERR_UNAUTHORIZED, 'Incorrect http basic authentication scheme'))
                ->throwResponse($response, 401);
        }

        try {
            $user = $this->userDao
                ->authenticateUser($emailPassword[0]);

            if (!$this->auth->verifyPassword($emailPassword[1], $user)) {
                return $this->dataWrapper
                    ->addEntity(new Error(Error::ERR_UNAUTHORIZED, 'Invalid email or password'))
                    ->throwResponse($response, 401);
            }
        } catch (NoEntityException $e) {
            return $this->dataWrapper
                ->addEntity(new Error(Error::ERR_UNAUTHORIZED, 'Invalid email or password'))
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


        return $this->dataWrapper
            ->addArray($data)
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
