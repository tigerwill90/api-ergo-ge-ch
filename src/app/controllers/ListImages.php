<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 26.02.2019
 * Time: 19:34
 */

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Services\DataWrapper;
use Ergo\Services\FileUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class ListImages
{
    /** @var LoggerInterface  */
    private $logger;

    /** @var DataWrapper  */
    private $wrapper;

    /** @var FileUtility */
    private $fileUtility;

    private const SCAN_PATH = __DIR__ . '/../../assets/images/';

    public function __construct(DataWrapper $wrapper, FileUtility $fileUtility, LoggerInterface $logger = null)
    {
        $this->wrapper = $wrapper;
        $this->logger = $logger;
        $this->fileUtility = $fileUtility;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $imageList = $this->fileUtility->scanDirectory(['png', 'jpg'], self::SCAN_PATH);

        if (empty($imageList)) {
            return $this->wrapper
                ->addEntity(new Error(
                    Error::ERR_NOT_FOUND, 'No image entity found',
                    [],
                    'Aucune image trouvée'
                ))
                ->throwResponse($response, 404);
        }

        return $this->wrapper
            ->addArray($imageList)
            ->addMeta()
            ->throwResponse($response);
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
