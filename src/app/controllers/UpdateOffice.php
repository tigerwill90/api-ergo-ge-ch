<?php

namespace Ergo\Controllers;

use Ergo\Business\Contact;
use Ergo\Business\Error;
use Ergo\Business\Office;
use Ergo\Domains\OfficesDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Exceptions\UniqueException;
use Ergo\Services\DataWrapper;
use Ergo\Services\Validators\ValidatorManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class UpdateOffice
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
        $id = (int) $request->getAttribute('id');

        // check for existing office
        if (!$this->officesDao->isOfficeExist($id)) {
            return $this->dataWrapper
                ->addEntity(new Error(Error::ERR_NOT_FOUND, 'No office entity found for this id : ' . $request->getAttribute('id')))
                ->throwResponse($response, 404);
        }

        $token = $request->getAttribute('token');
        $scopes = explode(' ', $token['scope']);
        // check if admin or self update, do not disclose any information about other user, return 404
        if (!in_array('admin', $scopes, true) && !in_array((int) $request->getAttribute('id'), $token['offices_id'], true)) {
            return $this->dataWrapper
                ->addEntity(new Error(Error::ERR_NOT_FOUND, 'No office entity found for this id : ' . $request->getAttribute('id')))
                ->throwResponse($response, 404);
        }

        if ($this->validatorManager->validate(['office'], $request)) {

            $params = $request->getParsedBody();
            $contacts = [];
            foreach ( $params['contacts'] as $contact) {
                $contacts[] = new Contact($contact);
            }
            $office = new Office($params, $contacts);
            $office->setId($id);

            try {
                $this->officesDao->updateOffice($office);
                return $this->dataWrapper
                    ->addEntity($office)
                    ->throwResponse($response);
            } catch (UniqueException $e) {
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
