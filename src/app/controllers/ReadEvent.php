<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Domains\EventsDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\DataWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class ReadEvent
{
    /** @var EventsDao */
    private $eventsDao;

    /** @var DataWrapper */
    private $dataWrapper;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(EventsDao $eventsDao, DataWrapper $dataWrapper, LoggerInterface $logger = null)
    {
        $this->eventsDao = $eventsDao;
        $this->dataWrapper = $dataWrapper;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $event = $this->eventsDao->getEvent($request->getAttribute('id'));
        } catch (NoEntityException $e) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_NOT_FOUND, $e->getMessage(),
                    [],
                    'Aucun évènement trouvé'
                ))
                ->addMeta()
                ->throwResponse($response, 404);
        }

        return $this->dataWrapper
            ->addEntity($event)
            ->addMeta()
            ->throwResponse($response);

    }

    private function log(string $message, array $context = []): void
    {
        if ($this->logger !== null) {
            $this->logger->debug($message, $context);
        }
    }
}