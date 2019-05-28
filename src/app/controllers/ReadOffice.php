<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 25.02.2019
 * Time: 21:30
 */

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Domains\OfficesDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\DataWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class ReadOffice
{
    /** @var LoggerInterface */
    private $logger;

    /** @var OfficesDao */
    private $officesDao;

    /** @var DataWrapper */
    private $wrapper;

    public function __construct(OfficesDao $officesDao, DataWrapper $wrapper, LoggerInterface $logger = null)
    {
        $this->officesDao = $officesDao;
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
        $attribute = $request->getAttribute('attribute');
        try {
            $office = $this->officesDao->getOffice($attribute);
        } catch (NoEntityException $e) {
            return $this->wrapper
                ->addEntity(new Error(
                    Error::ERR_NOT_FOUND, $e->getMessage(),
                    [],
                    'Ce cabinet n\'existe pas'
                ))
                ->addMeta()
                ->throwResponse($response, 404);
        } catch (\Exception $e) {
            throw $e;
        }

        return $this->wrapper
            ->addEntity($office)
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