<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 25.02.2019
 * Time: 22:08
 */

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Domains\OfficesDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\DataWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class ReadOffices
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
        $params = $request->getQueryParams();
        $search = $params['search'];
        try {
            $isSearch = false;
            if (!empty($search) && strlen($search) > 2) {
                $isSearch = true;
                $offices = $this->officesDao->searchOffices($search);
            } else {
                $offices = $this->officesDao->getOffices($params['attribute'], $params['sort']);
            }
        } catch (NoEntityException $e) {
            return $this->wrapper
                ->addEntity(new Error(
                    Error::ERR_NOT_FOUND, $e->getMessage(),
                    $isSearch ? ['search' => $search] : [],
                    $isSearch ? 'Aucun cabinet trouvé correspondant à cet attribut de recherche : ' . $search : 'Aucun cabinet trouvé'
                ))
                ->addMeta()
                ->throwResponse($response, 404);
        }

        return $this->wrapper
            ->addCollection($offices)
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