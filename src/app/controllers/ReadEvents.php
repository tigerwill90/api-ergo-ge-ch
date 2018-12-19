<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 18.12.2018
 * Time: 20:15
 */

namespace Ergo\Controllers;

use Ergo\Exceptions\InvalidDateTimeRange;
use Ergo\Services\CalendarClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class ReadEvents
{
    /** @var LoggerInterface  */
    private $logger;

    /** @var CalendarClient  */
    private $calendarClient;

    public function __construct(CalendarClient $calendarClient, LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->calendarClient = $calendarClient;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {

        $body = $response->getBody();
        $start = !empty($request->getQueryParams()['start']) ? $request->getQueryParams()['start'] : null;
        $end = !empty($request->getQueryParams()['end']) ? $request->getQueryParams()['end'] : null;

        try {
            $timeMin = new \DateTime($start);
            $timeMax = new \DateTime($end);

            if ($start === null && $end === null) {
                $timeMin->modify('first day of this month midnight');
                $timeMax->modify('last day of this month 23:59:59');
            }

            $this->validateDate($start, $end, $timeMin, $timeMax);

        } catch (InvalidDateTimeRange $e) {
            $body->write(json_encode(['error' => 'bad request', 'error_description' => $e->getMessage()]));
            return $response
                ->withBody($body)
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        } catch (\Exception $e) {
            $body->write(json_encode(['error' => 'bad request', 'error_description' => 'invalid date format']));
            return $response
                ->withBody($body)
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        $service =  $this->calendarClient->getCalendarService();
        $calendarId = getenv('CALENDAR_ID');
        $optParams = array(
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => $timeMin->format(\DateTime::RFC3339),
            'timeMax' => $timeMax->format(\DateTime::RFC3339)
        );

        try {
            $results = $service->events->listEvents($calendarId, $optParams);
        } catch (\Exception $e) {
            $body->write(json_encode(['error' => 'unauthorized', 'error_description' => 'an error occured with google calendar api']));
            return $response
                ->withBody($body)
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }

        $items = $results->getItems();
        $events = [];
        foreach ($items as $item) {
            $events[] = [
                'id' => $item->getId(),
                'kind' => $item->getKind(),
                'summary' => $item->getSummary(),
                'description' => $item->getDescription(),
                'location' => $item->getLocation(),
                'organizer' => [
                    'name' => $item->getOrganizer()->getDisplayName(),
                    'email' => $item->getOrganizer()->getEmail()
                ],
                'start' => [
                    'date' => $item->getStart()->getDate(),
                    'time' => $item->getStart()->getDateTime(),
                    'timezone' => $item->getStart()->getTimeZone()
                ],
                'end' => [
                    'date' => $item->getEnd()->getDate(),
                    'time' => $item->getEnd()->getDateTime(),
                    'timezone' => $item->getEnd()->getTimeZone()
                ]
            ];
        }
        $body->write(json_encode($events));
        return $response->withBody($body)->withHeader('Content-Type', 'application/json');
    }

    /**
     * @param string|null $start
     * @param string|null $end
     * @param \DateTime $timeMin
     * @param \DateTime $timeMax
     * @throws InvalidDateTimeRange
     */
    private function validateDate(?string $start, ?string $end, \DateTime $timeMin, \DateTime $timeMax) : void
    {
        if ($start === null && $end !== null) {
            throw new InvalidDateTimeRange('start query parameter is missing or empty');
        }

        if ($start !== null && $end === null) {
            throw new InvalidDateTimeRange('end query parameter is missing or empty');
        }

        if ($timeMax->format(\DateTime::RFC3339) <= $timeMin->format(\DateTime::RFC3339)) {
            throw new InvalidDateTimeRange('end time is equal or lesser than start time');
        }
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
