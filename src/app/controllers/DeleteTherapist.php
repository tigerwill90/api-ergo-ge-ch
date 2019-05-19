<?php

namespace Ergo\Controllers;

use Ergo\Domains\TherapistsDao;
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
        echo 'yolo';
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
