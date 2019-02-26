<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 25.02.2019
 * Time: 11:48
 */

namespace Ergo\Controllers;

use Ergo\Services\DataWrapper;
use Ergo\Services\FileUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class ListDocuments
{

    /** @var LoggerInterface  */
    private $logger;

    /** @var DataWrapper  */
    private $wrapper;

    /** @var FileUtility */
    private $fileUtility;

    private const SCAN_PATH = __DIR__ . '/../../pdf/';

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
        $pdfList = $this->fileUtility->scanDirectory(['pdf'], self::SCAN_PATH);
        return $this->wrapper
            ->addArray($pdfList)
            ->addMeta()
            ->throwResponse($response);
    }

    /**
     * @param string $message
     * @param array $context
     */
    private function log(string $message, array $context = []) : void
    {
        $this->logger->debug($message, $context);
    }
}