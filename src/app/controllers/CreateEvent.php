<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Business\Event;
use Ergo\Domains\EventsDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\Auth;
use Ergo\Services\DataWrapper;
use Ergo\Services\Validators\ValidatorManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class CreateEvent
{
    /** @var ValidatorManagerInterface  */
    private $validatorManager;

    /** @var EventsDao */
    private $eventsDao;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var Auth */
    private $auth;

    /** @var LoggerInterface  */
    private $logger;

    private const PATH = __DIR__ . '/../../assets/images/';
    private const MAX_ATTEMPT = 5;
    private const FILE_ID_LENGTH = 100;

    public function __construct(ValidatorManagerInterface $validatorManager, EventsDao $eventsDao, DataWrapper $dataWrapper, Auth $auth, LoggerInterface $logger = null)
    {
        $this->validatorManager = $validatorManager;
        $this->eventsDao = $eventsDao;
        $this->dataWrapper = $dataWrapper;
        $this->auth = $auth;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $token = $request->getAttribute('token');
        $scope = explode(' ', $token['scope']);

        if (!in_array('admin', $scope, true)) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_FORBIDDEN, 'Insufficient privileges to create a new events',
                    [],
                    'Action impossible, vous n\'avez pas les privilèges requis'
                ))
                ->addMeta()
                ->throwResponse($response, 403);
        }

        if ($this->validatorManager->validate(['create_event'], $request)) {
            $params = $request->getParsedBody();
            $data['title'] = $params['title'];
            $data['subtitle'] = $params['subtitle'];
            if ($params['date'] !== null) {
                try {
                    $date = new \DateTime($params['date']);
                    $data['date'] = $date->format('Y-m-d');
                } catch (\Exception $e) {
                    throw new \RuntimeException($e->getMessage());
                }
            }
            $data['description'] = $params['description'];
            $data['url'] = $params['url'];
            $data['imgAlt'] = $params['img_alt'];
            $data['imgName'] = $params['img_name'];

            $imgId = $this->auth->generateRandomValue(self::FILE_ID_LENGTH);
            $timeout = 0;
            while (file_exists(self::PATH . $imgId) || $this->eventsDao->isImageIdExist($imgId)) {
                $imgId = $this->auth->generateRandomValue(self::FILE_ID_LENGTH);
                if ($timeout >= self::MAX_ATTEMPT) {
                    throw new \RuntimeException('Unable to generate unique file name');
                }
                $timeout++;
            }

            $data['imgId'] = $imgId;
            $event = new Event($data);

            try {
                $this->eventsDao->createEvent($event);
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
                ->addEntity($event)
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
