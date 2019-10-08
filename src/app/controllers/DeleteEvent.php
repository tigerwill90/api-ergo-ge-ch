<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Domains\EventsDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\DataWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class DeleteEvent
{
    /** @var EventsDao */
    private $eventsDao;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(EventsDao $eventsDao, DataWrapper $dataWrapper, LoggerInterface $logger = null)
    {
        $this->eventsDao = $eventsDao;
        $this->dataWrapper = $dataWrapper;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $token = $request->getAttribute('token');
        $scope = explode(' ', $token['scope']);

        if (!in_array('admin', $scope, true)) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_FORBIDDEN, 'Insufficient privileges to delete an events',
                    [],
                    'Action impossible, vous n\'avez pas les privilèges requis'
                ))
                ->addMeta()
                ->throwResponse($response, 403);
        }

        try {
            $this->eventsDao->deleteEvent($request->getAttribute('id'));
        } catch (NoEntityException $e) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_NOT_FOUND, $e->getMessage(),
                    [],
                    'Suppression impossible, cet évènement n\'existe pas'
                ))
                ->addMeta()
                ->throwResponse($response, 404);
        }

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
