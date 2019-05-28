<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Domains\OfficesDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\DataWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class ReadUsersOffices
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
        $id = $request->getAttribute('id');
        $params = $request->getQueryParams();
        try {
            $offices = $this->officesDao->getOfficesByUserId($id, $params['attribute'], $params['sort']);
        } catch (NoEntityException $e) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_NOT_FOUND, $e->getMessage(),
                    [],
                    'Aucun cabinet trouvé pour cet utilisateur'
                ))
                ->addMeta()
                ->throwResponse($response, 404);
        }

        return $this->dataWrapper
            ->addCollection($offices)
            ->addMeta()
            ->throwResponse($response);
    }

    private function log(string $message, array $context = []) : void
    {
        if ($this->logger !== null) {
            $this->logger->debug($message, $context);
        }
    }
}
