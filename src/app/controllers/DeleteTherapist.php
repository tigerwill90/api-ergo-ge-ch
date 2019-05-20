<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Domains\TherapistsDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\DataWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class DeleteTherapist
{
    /** @var TherapistsDao  */
    private $therapistsDao;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(TherapistsDao $therapistsDao, DataWrapper $dataWrapper, LoggerInterface $logger = null)
    {
        $this->therapistsDao = $therapistsDao;
        $this->dataWrapper = $dataWrapper;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {

        $id = $request->getAttribute('id');
        try {
            $therapist = $this->therapistsDao->getTherapist($id);

            $token = $request->getAttribute('token');
            $scopes = explode(' ', $token['scope']);
            // check if admin or self delete, reject user who try to delete a no owned therapist
            if (!in_array('admin', $scopes, true) && !in_array($therapist->getOfficeId(), $token['offices_id'], true)) {
                return $this->dataWrapper
                    ->addEntity(new Error(Error::ERR_NOT_FOUND, 'No entity found for this therapist id : ' . $id))
                    ->throwResponse($response, 404);
            }

            $this->therapistsDao->deleteTherapist($id);
            return $response;

        } catch (NoEntityException $e) {
            return $this->dataWrapper
                ->addEntity(new Error(Error::ERR_NOT_FOUND, $e->getMessage()))
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
