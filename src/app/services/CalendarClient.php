<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 18.12.2018
 * Time: 20:14
 */

namespace Ergo\Services;

use Psr\Log\LoggerInterface;

class CalendarClient
{
    /** @var LoggerInterface  */
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * @return \Google_Service_Calendar
     */
    public function getCalendarService() : \Google_Service_Calendar
    {
        $client = new \Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->setApplicationName('ASE - Section Genevoise');
        $client->setScopes([\Google_Service_Calendar::CALENDAR_READONLY]);
        return new \Google_Service_Calendar($client);
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
