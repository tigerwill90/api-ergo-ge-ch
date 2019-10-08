<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 26.02.2019
 * Time: 17:47
 */

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Services\DataWrapper;
use Ergo\Services\FileUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Http\Stream;

final class DownloadImage
{
    /** @var LoggerInterface  */
    private $logger;

    /** @var FileUtility */
    private $utils;

    /** @var DataWrapper  */
    private $wrapper;

    private const PATH = __DIR__ . '/../../assets/images/';

    public function __construct(FileUtility $utils , DataWrapper $wrapper, LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->utils = $utils;
        $this->wrapper = $wrapper;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $filename = $request->getAttribute('name');
        if (null !== $file = $this->utils->searchFile($filename, ['png', 'jpg', 'jpeg', 'svg'], self::PATH)) {
            $fh = fopen(self::PATH . $file['filename'] . '.' . $file['extension'], 'rb');
            $stream = new Stream($fh);
            if ($file['extension'] === 'svg') {
                $mimeTypeExtension = 'svg+xml';
            } else if ($file['extension'] === 'jpg') {
                $mimeTypeExtension = 'jpeg';
            } else {
                $mimeTypeExtension = $file['extension'];
            }
            return $response
                ->withBody($stream)
                ->withHeader('Content-Type', 'image/' . $mimeTypeExtension)
                ->withHeader('Content-Disposition', 'inline; filename=' . $file['filename'] . '.' . $file['extension']);
        }

        return $this->wrapper
            ->addEntity(new Error(
                Error::ERR_NOT_FOUND, 'No image entity found for this name : ' . $filename,
                [],
                'Impossible d\'afficher cette image. La ressource n\'existe pas'
            ))
            ->addMeta()
            ->throwResponse($response, 404);
    }

    /**
     * @param string $message
     * @param array|null $context
     */
    private function log(string $message, array $context = []) : void
    {
        if ($this->logger !== null) {
            $this->logger->debug($message, $context);
        }
    }
}
