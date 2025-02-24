<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 18.12.2018
 * Time: 20:15
 */

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Exceptions\InvalidDateTimeRange;
use Ergo\Services\CalendarClient;
use Ergo\Services\DataWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class ReadCalendarEvents
{
    /** @var LoggerInterface  */
    private $logger;

    /** @var CalendarClient  */
    private $calendarClient;

    /** @var DataWrapper  */
    private $wrapper;

    public function __construct(CalendarClient $calendarClient, DataWrapper $wrapper, LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->wrapper = $wrapper;
        $this->calendarClient = $calendarClient;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
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
            return $this->wrapper
                ->addEntity(new Error(
                    Error::ERR_BAD_REQUEST, $e->getMessage(),
                    [],
                    'La plage des dates est incorrect'
                ))
                ->addMeta()
                ->throwResponse($response, 400);
        } catch (\Exception $e) {
            return $this->wrapper
                ->addEntity(new Error(
                    Error::ERR_BAD_REQUEST, 'invalid date format',
                    [],
                    'Le format de la date est incorrect'
                ))
                ->addMeta()
                ->throwResponse($response, 400);
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
            return $this->wrapper
                ->addEntity(new Error(
                    Error::ERR_UNAUTHORIZED, 'an error occured with google calendar api',
                    [],
                    'Un problème d\'autorisation est survenu avec le calendrier google'
                ))
                ->addMeta()
                ->throwResponse($response, 401);
        }

        $items = $results->getItems();
        $events = [];
        foreach ($items as $item) {
            $events[] = [
                'id' => $item->getId(),
                'kind' => $item->getKind(),
                'title' => $item->getSummary(),
                'description' => $item->getDescription(),
                'location' => $item->getLocation(),
                'htmlLink' => $item->getHtmlLink(),
                'organizer' => [
                    'name' => $item->getOrganizer()->getDisplayName(),
                    'email' => $item->getOrganizer()->getEmail()
                ],
                'creator' => [
                    'name' => $item->getCreator()->getDisplayName(),
                    'email' => $item->getCreator()->getEmail()
                ],
                'start' => call_user_func(function () use ($item) {
                    if ($item->getStart()->getDate() === null) {
                        return $item->getStart()->getDateTime();
                    }
                    return $item->getStart()->getDate();
                }),
                'end' => call_user_func(function () use ($item) {
                    if ($item->getEnd()->getDate() === null) {
                        return $item->getEnd()->getDateTime();
                    }
                    return $item->getEnd()->getDate();
                }),
                'timezone' => $item->getStart()->getTimeZone()
            ];
        }

        return $this->wrapper
            ->addArray($events)
            ->addMeta()
            ->throwResponse($response);
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
