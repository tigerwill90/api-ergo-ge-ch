<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Domains\EventsDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\DataWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Log\LoggerInterface;

final class UploadImageEvent
{

    /** @var EventsDao */
    private $eventsDao;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var LoggerInterface  */
    private $logger;

    private const PATH = __DIR__ . '/../../assets/images/';

    public function __construct(EventsDao $eventsDao, DataWrapper $dataWrapper, LoggerInterface $logger = null)
    {
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
                    Error::ERR_FORBIDDEN, 'Insufficient privileges to create a new events',
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
                    'Impossible de téléverser une image pour un évènement qui n\'existe pas'
                ))
                ->addMeta()
                ->throwResponse($response, 404);
        }

        // validation
        $uploadedFiles = $request->getUploadedFiles();
        /** @var UploadedFileInterface $uploadedFile */
        $uploadedFile = $uploadedFiles['image'];
        $errorsContext = [];
        if ($uploadedFile !== null) {
            if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
                return $this->dataWrapper
                    ->addEntity(new Error(
                        Error::ERR_BAD_REQUEST, 'An error occurred during image upload process',
                        [],
                        'Une erreur inattendue est survenu pendant le téléversement de l\'image'
                    ))
                    ->addMeta()
                    ->throwResponse($response, 400);
            }

            $expectExt = pathinfo($event->getImgName(), PATHINFO_EXTENSION);
            $gotExt = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
            if ($expectExt === $gotExt) {
                $uploadedFile->moveTo(self::PATH . $event->getImgId());
                return $this->dataWrapper
                    ->addMeta()
                    ->throwResponse($response, 201);
            }
            $errorsContext[] = 'Invalid image type (expected: ' . $expectExt . ', got: ' . $gotExt . ')';
        } else {
            $errorsContext[] = 'No "image" field found in data form';
        }

        return $this->dataWrapper
            ->addEntity(new Error(
                Error::ERR_BAD_REQUEST,
                'The request could not be understood by the server due to malformed syntax',
                $errorsContext,
                'Une erreur de validation est survenu concernant l\'image à téléverser'
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
