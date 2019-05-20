<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Business\Therapist;
use Ergo\Domains\TherapistsDao;
use Ergo\Exceptions\UniqueException;
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
        // validate parameter first
        if ($this->validatorManager->validate(['therapist'], $request)) {
            $token = $request->getAttribute('token');
            $scopes = explode(' ', $token['scope']);
            $params = $request->getParsedBody();
            // check if admin or self create, if all some office id are not in user office id, reject request
            $diff = array_diff($params['offices_id'], $token['offices_id']);
            if (!empty($diff) && !in_array('admin', $scopes, true)) {
                return $this->dataWrapper
                    ->addEntity(new Error(Error::ERR_FORBIDDEN, 'Insufficient privileges to create a new therapist for office id : ' . implode(', ', $diff)))
                    ->throwResponse($response, 403);
            }

            $data['title'] = $params['title'];
            $data['firstname'] = $params['first_name'];
            $data['lastname'] = $params['last_name'];
            $data['home'] = $params['home'];
            $officesId = array_unique((array) $params['offices_id'], SORT_REGULAR);
            $phones = array_unique((array) $params['phones'], SORT_REGULAR);
            $emails = array_unique((array) $params['emails'], SORT_REGULAR);
            $categories = array_unique((array) $params['categories'], SORT_REGULAR);

            $therapist = new Therapist($data, $phones, $emails, $categories, $officesId);

            try {
                $this->therapistsDao->createTherapist($therapist);
            } catch (UniqueException $e) {
                return $this->dataWrapper
                    ->addEntity(new Error(Error::ERR_CONFLICT, $e->getMessage()))
                    ->throwResponse($response, 409);
            }

            return $this->dataWrapper
                ->addEntity($therapist)
                ->throwResponse($response, 201);
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
