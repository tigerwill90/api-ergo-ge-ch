<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 25.02.2019
 * Time: 13:59
 */

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\domains\CategoriesDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Services\DataWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class ReadCategory
{
    /** @var LoggerInterface */
    private $logger;

    /** @var CategoriesDao */
    private $categoriesDao;

    /** @var DataWrapper */
    private $wrapper;

    public function __construct(CategoriesDao $categoriesDao, DataWrapper $wrapper, LoggerInterface $logger = null)
    {
        $this->categoriesDao = $categoriesDao;
        $this->wrapper = $wrapper;
        $this->logger = $logger;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws \Exception
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        try {
            $category = $this->categoriesDao->getCategory($request->getAttribute('id'));
        } catch (NoEntityException $e) {
            return $this->wrapper
                ->addEntity(new Error(Error::ERR_NOT_FOUND, $e->getMessage()))
                ->addMeta()
                ->throwResponse($response, 404);
        } catch (\Exception $e) {
            throw $e;
        }
        return $this->wrapper
                ->addEntity($category)
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
