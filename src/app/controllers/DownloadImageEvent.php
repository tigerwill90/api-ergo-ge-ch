<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Domains\EventsDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\DataWrapper;
use Ergo\Services\FileUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Http\Stream;

final class DownloadImageEvent
{
    /** @var EventsDao */
    private $eventDao;

    /** @var LoggerInterface  */
    private $logger;

    /** @var FileUtility */
    private $utils;

    /** @var DataWrapper  */
    private $wrapper;

    private const PATH = __DIR__ . '/../../assets/images/';

    public function __construct(EventsDao  $eventsDao, FileUtility $utils , DataWrapper $wrapper, LoggerInterface $logger = null)
    {
        $this->eventDao = $eventsDao;
        $this->logger = $logger;
        $this->utils = $utils;
        $this->wrapper = $wrapper;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $id = $request->getAttribute('id');
        try {
            $event = $this->eventDao->getEvent($id);
        } catch (NoEntityException $e) {
            return $this->wrapper
                ->addEntity(new Error(
                    Error::ERR_NOT_FOUND, $e->getMessage(),
                    [],
                    'Cet évènement n\'existe pas'
                ))
                ->addMeta()
                ->throwResponse($response, 404);
        }

        if (!file_exists(self::PATH . $event->getImgId())) {
            return $this->wrapper
                ->addEntity(new Error(
                    Error::ERR_NOT_FOUND, 'No image found for this event id : ' . $id,
                    [],
                    'Aucune image trouvée pour cet évènement'
                ))
                ->addMeta()
                ->throwResponse($response, 404);
        }

        $fh = fopen(self::PATH . $event->getImgId(), 'rb');
        $ext = pathinfo($event->getImgName(), PATHINFO_EXTENSION);
        $stream = new Stream($fh);
        return $response
            ->withBody($stream)
            ->withHeader('Content-Type', 'image/' . ($ext === 'svg' ? $ext . '+xml' : $ext));
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
