<?php

namespace Ergo\Controllers;

use Ergo\Business\Contact;
use Ergo\Business\Error;
use Ergo\Business\Office;
use Ergo\Domains\OfficesDao;
use Ergo\Exceptions\UniqueException;
use Ergo\Services\DataWrapper;
use Ergo\Services\Validators\ValidatorManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class CreateOffice
{
    /** @var ValidatorManagerInterface  */
    private $validatorManager;

    /** @var OfficesDao  */
    private $officesDao;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(ValidatorManagerInterface $validatorManager, OfficesDao $officesDao, DataWrapper $dataWrapper, LoggerInterface $logger = null)
    {
        $this->validatorManager = $validatorManager;
        $this->officesDao = $officesDao;
        $this->dataWrapper = $dataWrapper;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $scopes = explode(' ', $request->getAttribute('token')['scope']);
        if (!in_array('admin', $scopes, true)) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_FORBIDDEN, 'Insufficient privileges to create a new office',
                    [],
                    'Action impossible, vous n\'avez pas les privilèges requis'
                ))
                ->throwResponse($response, 403);
        }

        if ($this->validatorManager->validate(['office'], $request)) {
            $params = $request->getParsedBody();
            $contacts = [];
            foreach ( $params['contacts'] as $contact) {
                $contacts[] = new Contact($contact);
            }
            $office = new Office($params, $contacts);

            try {
                $this->officesDao->createOffice($office);
            } catch (UniqueException $e) {
                return $this->dataWrapper
                    ->addEntity(new Error(
                        Error::ERR_CONFLICT,
                        $e->getMessage(),
                        [],
                        'Impossible de créer ce cabinet, l\'adresse email ou le nom existe déjà'
                    ))
                    ->throwResponse($response, 409);
            }

            return $this->dataWrapper
                ->addEntity($office)
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
