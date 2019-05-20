<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Business\Therapist;
use Ergo\Domains\TherapistsDao;
use Ergo\Exceptions\IntegrityConstraintException;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\DataWrapper;
use Ergo\Services\Validators\ValidatorManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class UpdateTherapist
{
    /** @var ValidatorManagerInterface  */
    private $validatorManager;

    /** @var TherapistsDao  */
    private $therapistsDao;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(ValidatorManagerInterface $validatorManager, TherapistsDao $therapistsDao, DataWrapper $dataWrapper, LoggerInterface $logger = null)
    {
        $this->validatorManager = $validatorManager;
        $this->therapistsDao = $therapistsDao;
        $this->dataWrapper = $dataWrapper;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $data['id'] = $request->getAttribute('id');

        // Check for existing therapist and retrieve current therapist
        try {
            $currentTherapist = $this->therapistsDao->getTherapist($data['id']);
        } catch (NoEntityException $e) {
            return $this->dataWrapper
                ->addEntity(new Error(Error::ERR_NOT_FOUND, 'No therapist entity found for this id : ' . $data['id']))
                ->throwResponse($response, 404);
        }

        if ($this->validatorManager->validate(['therapist'], $request)) {
            $token = $request->getAttribute('token');
            $scopes = explode(' ', $token['scope']);
            $params = $request->getParsedBody();
            /**
             * Admin user can modify all relation
             * - user can move owned therapist between owned office
             *   - office_parameter must in jwt token (move only in owned office)
             *   - current therapists_office_id must in jwt token (move only owned therapist)
             */
            if (
                !in_array('admin', $scopes, true) &&
                (($moveOwnedOffice = !in_array($params['office_id'], $token['offices_id'], true)) ||
                !in_array($currentTherapist->getOfficeId(), $token['offices_id'], true))
            ) {
                if ($moveOwnedOffice) {
                    return $this->dataWrapper
                        ->addEntity(new Error(Error::ERR_FORBIDDEN, 'Insufficient privileges to move therapist id ' . $data['id'] . ' to office id ' . $params['office_id']))
                        ->throwResponse($response, 403);
                }
                return $this->dataWrapper
                    ->addEntity(new Error(Error::ERR_NOT_FOUND, 'No therapist entity found for this id : ' . $data['id']))
                    ->throwResponse($response, 404);
            }

            $data['title'] = $params['title'];
            $data['firstname'] = $params['first_name'];
            $data['lastname'] = $params['last_name'];
            $data['home'] = $params['home'];
            $data['officeId'] = $params['office_id'];
            $phones = array_unique((array) $params['phones'], SORT_REGULAR);
            $emails = array_unique((array) $params['emails'], SORT_REGULAR);
            $categories = array_unique((array) $params['categories'], SORT_REGULAR);

            $therapist = new Therapist($data, $phones, $emails, $categories);

            try {
                $this->therapistsDao->updateTherapist($therapist);
                return $this->dataWrapper
                    ->addEntity($therapist)
                    ->throwResponse($response);
            } catch (IntegrityConstraintException $e) {
                return $this->dataWrapper
                    ->addEntity(new Error(Error::ERR_CONFLICT, $e->getMessage()))
                    ->throwResponse($response, 409);
            }
        }

        return $this->dataWrapper
            ->addEntity(new Error(
                Error::ERR_BAD_REQUEST,
                'The request could not be understood by the server due to malformed syntax',
                $this->validatorManager->getErrorsMessages()
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
