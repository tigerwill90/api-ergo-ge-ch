<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 25.02.2019
 * Time: 23:08
 */

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Domains\TherapistsDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\DataWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class ReadTherapist
{
    /** @var LoggerInterface */
    private $logger;

    /** @var TherapistsDao */
    private $therapistsDao;

    /** @var DataWrapper */
    private $wrapper;

    public function __construct(TherapistsDao $therapistsDao, DataWrapper $wrapper, LoggerInterface $logger = null)
    {
        $this->therapistsDao = $therapistsDao;
        $this->wrapper = $wrapper;
        $this->logger = $logger;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws \Exception
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        try {
            $therapist = $this->therapistsDao->getTherapist($request->getAttribute('id'));
        } catch (NoEntityException $e) {
            return $this->wrapper
                ->addEntity(new Error(
                    Error::ERR_NOT_FOUND, $e->getMessage(),
                    'Cet ergothérapeute n\'existe pas'
                ))
                ->addMeta()
                ->throwResponse($response, 404);
        }

        return $this->wrapper
            ->addEntity($therapist)
            ->addMeta()
            ->throwResponse($response);
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
