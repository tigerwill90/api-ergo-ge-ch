<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 25.02.2019
 * Time: 11:48
 */

namespace Ergo\Controllers;

use Ergo\Services\DataWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class ListDocuments
{

    /** @var LoggerInterface  */
    private $logger;

    private $wrapper;

    private const SCAN_PATH = __DIR__ . '/../../pdf/*.pdf';

    public function __construct(DataWrapper $wrapper, LoggerInterface $logger = null)
    {
        $this->wrapper = $wrapper;
        $this->logger = $logger;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $pdfList = $this->scanPdfDirectory();
        return $this->wrapper
            ->addData($pdfList)
            ->addMeta()
            ->throwResponse($response);
    }

    /**
     * Return a list of pdf content in pdf directory
     * @return array
     */
    private function scanPdfDirectory() : array {
        $files = [];
        foreach (glob(self::SCAN_PATH) as $file) {
            $files[] = end(explode('/', rtrim($file, '/')));
        }
        return $files;
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