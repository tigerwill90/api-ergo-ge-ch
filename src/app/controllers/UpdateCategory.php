<?php

namespace Ergo\Controllers;

use Ergo\Business\Category;
use Ergo\Business\Error;
use Ergo\domains\CategoriesDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Exceptions\UniqueException;
use Ergo\Services\DataWrapper;
use Ergo\Services\Validators\ValidatorManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class UpdateCategory
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
                    Error::ERR_FORBIDDEN, 'Insufficient privileges to update a category',
                    [],
                    'Action impossible, vous n\'avez pas les privilèges requis'
                ))
                ->throwResponse($response, 403);
        }

        if ($this->validatorManager->validate(['category'], $request)) {
            $data['id'] = $request->getAttribute('id');
            try {
                $this->categoriesDao->getCategory($data['id']);
            } catch (NoEntityException $e) {
                return $this->dataWrapper
                    ->addEntity(new Error(
                        Error::ERR_NOT_FOUND, $e->getMessage(),
                        [],
                        'Impossible de mettre à jour cette catégorie. La ressource n\'existe pas'
                    ))
                    ->throwResponse($response, 404);
            }

            $params = $request->getParsedBody();
            $data['name'] =  $params['name'];
            $data['description'] = $params['description'];
            $category = new Category($data);

            try {
                $this->categoriesDao->updateCategory($category);
                return $this->dataWrapper
                    ->addEntity($category)
                    ->throwResponse($response);
            } catch (UniqueException $e) {
                return $this->dataWrapper
                    ->addEntity(new Error(
                        Error::ERR_CONFLICT, $e->getMessage(),
                        [],
                        'Impossible de mettre à jour cette catégorie. Le nom doit être unique'
                    ))
                    ->throwResponse($response, 409);
            }
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
