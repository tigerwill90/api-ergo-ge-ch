<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\domains\CategoriesDao;
use Ergo\Exceptions\IntegrityConstraintException;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\DataWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class DeleteCategory
{
    /** @var CategoriesDao  */
    private $categoriesDao;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(CategoriesDao $categoriesDao, DataWrapper $dataWrapper, LoggerInterface $logger = null)
    {
        $this->categoriesDao = $categoriesDao;
        $this->dataWrapper = $dataWrapper;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $token = $request->getAttribute('token');
        $scopes = explode(' ', $token['scope']);
        // Only admin can delete category, no problem to disclose category here
        if (!in_array('admin', $scopes, true)) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_FORBIDDEN, 'Insufficient privileges to delete a category',
                    [],
                    'Action impossible, vous n\'avez pas les privilèges requis'
                    ))
                ->throwResponse($response, 403);
        }

        try {
            $this->categoriesDao->deleteCategory($request->getAttribute('id'));
        } catch (NoEntityException $e) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_NOT_FOUND, $e->getMessage(),
                    [],
                    'Suppression impossible, cette catégorie n\'existe pas'
                ))
                ->throwResponse($response, 404);
        } catch (IntegrityConstraintException $e) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_CONFLICT, $e->getMessage(),
                    [],
                    'Suppression impossible, cette catégorie est associé à un ou plusieurs ergothérapeutes'
                ))
                ->throwResponse($response, 409);
        }

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
