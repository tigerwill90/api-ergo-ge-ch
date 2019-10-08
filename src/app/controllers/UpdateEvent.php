<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Domains\EventsDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Exceptions\UniqueException;
use Ergo\Services\DataWrapper;
use Ergo\Services\Validators\ValidatorManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class UpdateEvent
{
    /** @var ValidatorManagerInterface  */
    private $validatorManager;

    /** @var EventsDao */
    private $eventsDao;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(ValidatorManagerInterface $validatorManager, EventsDao $eventsDao, DataWrapper $dataWrapper, LoggerInterface $logger = null)
    {
        $this->validatorManager = $validatorManager;
        $this->eventsDao = $eventsDao;
        $this->dataWrapper = $dataWrapper;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $token = $request->getAttribute('token');
        $scope = explode(' ', $token['scope']);

        if (!in_array('admin', $scope, true)) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_FORBIDDEN, 'Insufficient privileges to update an events',
                    [],
                    'Action impossible, vous n\'avez pas les privilèges requis'
                ))
                ->addMeta()
                ->throwResponse($response, 403);
        }

        try {
            $event = $this->eventsDao->getEvent($request->getAttribute('id'));
        } catch (NoEntityException $e) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_NOT_FOUND, $e->getMessage(),
                    [],
                    'Impossible de mettre à jour cet évènement. La ressource n\'existe pas'
                ))
                ->addMeta()
                ->throwResponse($response, 404);
        }

        if ($this->validatorManager->validate(['event'], $request)) {
            $params = $request->getParsedBody();
            $event->setTitle($params['title']);
            $event->setSubtitle($params['subtitle']);
            if ($params['date'] !== null) {
                try {
                    $date = new \DateTime($params['date']);
                    $event->setDate($date->format('Y-m-d'));
                } catch (\Exception $e) {
                    throw new \RuntimeException($e->getMessage());
                }
            }
            $event->setDescription($params['description']);
            $event->setUrl($params['url']);
            $event->setImgAlt($params['img_alt']);
            $event->setImgName($params['img_name']);

            try {
                $this->eventsDao->updateEvent($event);
            } catch (UniqueException $e) {
                return $this->dataWrapper
                    ->addEntity(new Error(
                        Error::ERR_INTERNAL_SERVER, $e->getMessage(),
                        [],
                        'Une erreur inattendue est survenue lors de la création de la ressource'
                    ))
                    ->addMeta()
                    ->throwResponse($response, 500);
            }

            return $this->dataWrapper
                ->addEntity($event)
                ->addMeta()
                ->throwResponse($response, 200);
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
