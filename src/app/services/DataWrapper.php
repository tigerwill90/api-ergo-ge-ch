<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 19.12.2018
 * Time: 13:49
 */

namespace Ergo\Services;

use Ergo\Business\EntityInterface;
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
     * @param EntityInterface $entity
     * @return DataWrapper
     */
    public function addEntity(EntityInterface $entity) : self
    {
        $this->wrapper['data'] = $entity->getEntity();
        return $this;
    }

    /**
     * @param EntityInterface[] $collection
     * @return DataWrapper
     */
    public function addCollection(array $collection) : self
    {
        foreach ($collection as $entity) {
            $this->wrapper['data'][] = $entity->getEntity();
        }
        return $this;
    }

    /**
     * @param array $data
     * @return DataWrapper
     */
    public function addArray(array $data): self
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
            'api_versions' => getenv('API_VERSION')
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
        $body->write(json_encode($this->wrapper, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
        return $response
            ->withBody($body)
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
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