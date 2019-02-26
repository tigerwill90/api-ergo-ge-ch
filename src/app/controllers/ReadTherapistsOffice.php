<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 26.02.2019
 * Time: 12:08
 */

namespace Ergo\Controllers;


use Ergo\Business\Error;
use Ergo\Domains\TherapistsDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\DataWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class ReadTherapistsOffice
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
            $therapists = $this->therapistsDao->getTherapists($request->getAttribute('id'));
        } catch (NoEntityException $e) {
            return $this->wrapper
                ->addEntity(new Error(Error::ERR_NOT_FOUND, $e->getMessage()))
                ->addMeta()
                ->throwResponse($response, 404);
        } catch (\Exception $e) {
            throw $e;
        }
        return $this->wrapper
                ->addCollection($therapists)
                ->addMeta()
                ->throwResponse($response);
    }

    /**
     * @param string $message
     * @param array $context
     */
    private function log(string $message, array $context = []) : void
    {
        $this->logger->debug($message, $context);
    }
}