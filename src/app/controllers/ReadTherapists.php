<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Domains\TherapistsDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\DataWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class ReadTherapists
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

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $params = $request->getQueryParams();
        $search = $params['search'];
        try {
            $isSearch = false;
            if (!empty($search) && strlen($search) > 2) {
                $isSearch = true;
                $therapists = $this->therapistsDao->searchTherapists($search);
            } else {
                $therapists = $this->therapistsDao->getTherapists();
            }
        } catch (NoEntityException $e) {
            return $this->wrapper
                ->addEntity(new Error(
                    Error::ERR_NOT_FOUND, $e->getMessage(),
                    $isSearch ? ['search' => $search] : [],
                    $isSearch ? 'Aucun ergothérapeute trouvé correspondant à cet attribut de recherche : ' . $search : 'Aucun ergothérapeute trouvé'
                ))
                ->addMeta()
                ->throwResponse($response, 404);
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
        if ($this->logger !== null) {
            $this->logger->debug($message, $context);
        }
    }
}
