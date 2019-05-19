<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Domains\OfficesDao;
use Ergo\Exceptions\IntegrityConstraintException;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\DataWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class DeleteOffice
{
    /** @var OfficesDao  */
    private $officesDao;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(OfficesDao $officesDao, DataWrapper $dataWrapper, LoggerInterface $logger = null)
    {
        $this->officesDao = $officesDao;
        $this->dataWrapper = $dataWrapper;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $scopes = explode(' ', $request->getAttribute('token')['scope']);
        if (!in_array('admin', $scopes, true)) {
            // do not disclose any information about other user, return 404
            return $this->dataWrapper
                ->addEntity(new Error(Error::ERR_NOT_FOUND, 'No entity found for this user id : ' . $request->getAttribute('id')))
                ->throwResponse($response, 404);
        }

        try {
            $this->officesDao->deleteOffice((int) $request->getAttribute('id'));
            return $response;
        } catch (NoEntityException $e) {
            return $this->dataWrapper
                ->addEntity(new Error(Error::ERR_NOT_FOUND, $e->getMessage()))
                ->throwResponse($response, 404);
        } catch (IntegrityConstraintException $e) {
            return $this->dataWrapper
                ->addEntity(new Error(Error::ERR_CONFLICT, $e->getMessage()))
                ->throwResponse($response, 409);
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
