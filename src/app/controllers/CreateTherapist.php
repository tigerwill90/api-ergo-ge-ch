<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Business\Therapist;
use Ergo\domains\CategoriesDao;
use Ergo\Domains\TherapistsDao;
use Ergo\Exceptions\IntegrityConstraintException;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\DataWrapper;
use Ergo\Services\Validators\ValidatorManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class CreateTherapist
{
    /** @var ValidatorManagerInterface  */
    private $validatorManager;

    /** @var TherapistsDao  */
    private $therapistsDao;

    /** @var CategoriesDao  */
    private $categoriesDao;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(ValidatorManagerInterface $validatorManager, TherapistsDao $therapistsDao, CategoriesDao $categoriesDao, DataWrapper $dataWrapper, LoggerInterface $logger = null)
    {
        $this->validatorManager = $validatorManager;
        $this->therapistsDao = $therapistsDao;
        $this->categoriesDao = $categoriesDao;
        $this->dataWrapper = $dataWrapper;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        // validate parameter first
        if ($this->validatorManager->validate(['therapist'], $request)) {
            $token = $request->getAttribute('token');
            $scopes = explode(' ', $token['scope']);
            $params = $request->getParsedBody();
            // check if admin or self create, reject user who try to associate a new therapist to an no owned office
            if (!in_array('admin', $scopes, true) && !in_array($params['office_id'], $token['offices_id'], true)) {
                return $this->dataWrapper
                    ->addEntity(new Error(
                        Error::ERR_FORBIDDEN, 'Insufficient privileges to create a new therapist for office id : ' . $params['office_id'],
                        [],
                        'Action impossible, vous n\'avez pas les privilèges requis'
                    ))
                    ->addMeta()
                    ->throwResponse($response, 403);
            }

            $data['title'] = $params['title'];
            $data['firstname'] = $params['first_name'];
            $data['lastname'] = $params['last_name'];
            $data['home'] = $params['home'];
            $data['officeId'] = $params['office_id'];
            $phones = array_unique((array) $params['phones'], SORT_REGULAR);
            $emails = array_unique((array) $params['emails'], SORT_REGULAR);
            $categoriesId = array_unique((array) $params['categories'], SORT_REGULAR);
            $categories = [];

            foreach ($categoriesId as $id) {
                try {
                    $category = $this->categoriesDao->getCategory($id);
                    $categories[] = $category->getEntity();
                } catch (NoEntityException $e) {
                    return $this->dataWrapper
                        ->addEntity(new Error(
                            Error::ERR_NOT_FOUND, $e->getMessage(),
                            [],
                            'Impossible de de créer cet ergothérapeute, certaines catégories n\'existe pas'
                        ))
                        ->addMeta()
                        ->throwResponse($response, 404);
                }
            }

            $therapist = new Therapist($data, $phones, $emails, $categories);

            try {
                $this->therapistsDao->createTherapist($therapist);
            } catch (IntegrityConstraintException $e) {
                return $this->dataWrapper
                    ->addEntity(new Error(
                        Error::ERR_CONFLICT, $e->getMessage(),
                        [],
                        'Impossible de créer cet ergothérpateute, le cabinet n\'existe pas'
                    ))
                    ->addMeta()
                    ->throwResponse($response, 409);
            } catch (NoEntityException $e) {
                return $this->dataWrapper
                    ->addEntity(new Error(
                        Error::ERR_INTERNAL_SERVER,
                        $e->getMessage(),
                        [],
                        'Une erreur inattendue est survenue lors de la création de la ressource'
                    ))
                    ->addMeta()
                    ->throwResponse($response, 500);
            }

            return $this->dataWrapper
                ->addEntity($therapist)
                ->addMeta()
                ->throwResponse($response, 201);
        }

        return $this->dataWrapper
            ->addEntity(new Error(
                Error::ERR_BAD_REQUEST,
                'The request could not be understood by the server due to malformed syntax',
                $this->validatorManager->getErrorsMessages(),
                'Une erreur de validation est survenu'
            ))
            ->addMeta()
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
