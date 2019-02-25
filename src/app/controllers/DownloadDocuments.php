<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 09.12.2018
 * Time: 12:42
 */

namespace Ergo\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Http\Stream;

final class DownloadDocuments
{
    /** @var LoggerInterface  */
    private $logger;

    private const SCAN_PATH = __DIR__ . '/../../pdf/*.pdf';
    private const PATH = '/../../pdf/';

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $filename = $request->getAttribute('name');
        if ($this->pdfExist($filename)) {
            $fh = fopen(__DIR__. self::PATH . $filename . '.pdf', 'rb');
            $stream = new Stream($fh);
            $disposition = $request->getQueryParams()['disposition'];
            $newResponse = $response
                ->withBody($stream)
                ->withHeader('Content-Type', 'application/pdf')
                ->withHeader('Content-Description', 'File Transfer')
                ->withHeader('Content-Transfer-Encoding', 'binary');

            if (!empty($disposition) && $disposition === 'download') {
                return $newResponse
                    ->withHeader('Content-Type', 'application/download')
                    ->withHeader('Content-Type', 'application/force-download')
                    ->withHeader('Content-Disposition', 'attachment; filename=' . $filename . '.pdf');
            }

            return $newResponse
                ->withHeader('Content-Disposition', 'inline; filename=' . $filename . '.pdf');
        }

        $body = $response->getBody();
        $body->write(json_encode(['error' => 'resource not found', 'error_description' => 'the requested file doesn\'t exist']));
        return $response
            ->withBody($body)
            ->withStatus(404)
            ->withHeader('Content-Type', 'application/json');
    }

    /**
     * Secure way to check if a pdf filename exist in pdf directory
     * @param string $filename
     * @return bool
     */
    private function pdfExist(string $filename) : bool {
        foreach (glob(self::SCAN_PATH) as $file) {
            if ($filename . '.pdf' === end(explode('/', rtrim($file, '/')))) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $message
     * @param array|null $context
     */
    private function log(string $message, array $context = []) : void
    {
        $this->logger->debug($message, $context);
    }
}