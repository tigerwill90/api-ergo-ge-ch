<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 26.02.2019
 * Time: 14:00
 */

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\domains\CategoriesDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\DataWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class ReadCategoriesOffice
{
    /** @var LoggerInterface */
    private $logger;

    /** @var CategoriesDao */
    private $categoriesDao;

    /** @var DataWrapper */
    private $wrapper;

    public function __construct(CategoriesDao $categoriesDao, DataWrapper $wrapper, LoggerInterface $logger = null)
    {
        $this->categoriesDao = $categoriesDao;
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
            $categories = $this->categoriesDao->getCategoriesByOffice($request->getAttribute('id'));
        } catch (NoEntityException $e) {
            return $this->wrapper
                ->addEntity(new Error(
                    Error::ERR_NOT_FOUND, $e->getMessage(),
                    [],
                    'Aucune catégorie trouvée pour ce cabinet'
                ))
                ->addMeta()
                ->throwResponse($response, 404);

        }

        return $this->wrapper
            ->addCollection($categories)
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
