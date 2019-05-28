<?php

namespace Ergo\Controllers;

use Ergo\Business\Category;
use Ergo\Business\Error;
use Ergo\domains\CategoriesDao;
use Ergo\Exceptions\UniqueException;
use Ergo\Services\DataWrapper;
use Ergo\Services\Validators\ValidatorManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class CreateCategory
{
    /** @var ValidatorManagerInterface  */
    private $validatorManager;

    /** @var CategoriesDao  */
    private $categoriesDao;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(ValidatorManagerInterface $validatorManager, CategoriesDao $categoriesDao, DataWrapper $dataWrapper, LoggerInterface $logger = null)
    {
        $this->validatorManager = $validatorManager;
        $this->categoriesDao = $categoriesDao;
        $this->dataWrapper = $dataWrapper;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $token = $request->getAttribute('token');
        $scopes = explode(' ', $token['scope']);
        // Only admin can add category
        if (!in_array('admin', $scopes, true)) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_FORBIDDEN, 'Insufficient privileges to create a new category',
                    [],
                    'Action impossible, vous n\'avez pas les privilèges requis'
                ))
                ->throwResponse($response, 403);
        }

        if ($this->validatorManager->validate(['category'], $request)) {
            $params = $request->getParsedBody();
            $category = new Category($params);

            try {
                $this->categoriesDao->createCategory($category);
            } catch (UniqueException $e) {
                return $this->dataWrapper
                    ->addEntity(new Error(
                        Error::ERR_CONFLICT,
                        $e->getMessage(),
                        [],
                        'Cette catégorie existe déjà'
                    ))
                    ->throwResponse($response, 409);
            }

            return $this->dataWrapper
                ->addEntity($category)
                ->throwResponse($response, 201);
        }

        return $this->dataWrapper
            ->addEntity(new Error(
                Error::ERR_BAD_REQUEST,
                'The request could not be understood by the server due to malformed syntax',
                $this->validatorManager->getErrorsMessages(),
                'Une erreur de validation est survenu'
            ))
            ->throwResponse($response, 400);
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
