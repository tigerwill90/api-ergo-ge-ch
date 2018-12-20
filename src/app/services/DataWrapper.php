<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 19.12.2018
 * Time: 13:49
 */

namespace Ergo\Services;


use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class DataWrapper
{
    /** @var LoggerInterface  */
    private $logger;

    /** @var array */
    private $wrapper;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * @param array $data
     * @return DataWrapper
     */
    public function addData(array $data) : self
    {
        $this->wrapper['data'] = $data;
        return $this;
    }

    /**
     * @return DataWrapper
     */
    public function addMeta() : self
    {
        $this->wrapper['meta'] = [
            'api_versions' => '0.0.1'
        ];
        return $this;
    }

    /**
     * @param ResponseInterface $response
     * @param int $status
     * @return ResponseInterface
     */
    public function throwResponse(ResponseInterface $response, int $status = 200) : ResponseInterface
    {
        $body = $response->getBody();
        $body->write(json_encode($this->wrapper));
        return $response
            ->withBody($body)
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
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