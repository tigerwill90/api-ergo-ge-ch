<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 09.12.2018
 * Time: 12:42
 */

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Services\DataWrapper;
use Ergo\Services\FileUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Http\Stream;

final class DownloadDocuments
{
    /** @var LoggerInterface  */
    private $logger;

    /** @var FileUtility */
    private $utils;

    /** @var DataWrapper */
    private $wrapper;

    private const PATH = __DIR__ . '/../../pdf/';

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
        if (null !== $file = $this->utils->searchFile($filename, ['pdf'], self::PATH)) {
            $fh = fopen(self::PATH . $file['filename'] . '.' . $file['extension'], 'rb');
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

        return $this->wrapper
            ->addEntity(new Error(Error::ERR_NOT_FOUND, 'No pdf entity found for this name : ' . $filename))
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