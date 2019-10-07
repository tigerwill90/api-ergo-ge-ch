<?php

namespace Ergo\Domains;

use Ergo\Business\Event;
use Ergo\Exceptions\NoEntityException;
use Psr\Log\LoggerInterface;
use PDO;

class EventsDao
{
    /* @var PDO */
    private $pdo;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(PDO $pdo, LoggerInterface $logger = null)
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
    }

    /**
     * @param Event $event
     * @throws NoEntityException
     */
    public function createEvent(Event $event) : void
    {
        $sql =  'INSERT INTO events (events_title, events_subtitle, events_date, events_description, events_url) values (:title, :subtitle, :date, :description, :url)';

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $title = $event->getTitle();
            $stmt->bindParam(':title', $title);
            $subtitle = $event->getSubtitle();
            $stmt->bindParam(':subtitle', $subtitle);
            $date = $event->getDate();
            $stmt->bindParam(':date', $date);
            $description = $event->getDescription();
            $stmt->bindParam(':description', $description);
            $url = $event->getUrl();
            $stmt->bindParam(':url', $url);

            $stmt->execute();
            $event->setId((int) $this->pdo->lastInsertId());

            $this->setEventDateTime($event);

            $this->pdo->commit();
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * @param Event $event
     * @throws NoEntityException
     */
    private function setEventDateTime(Event $event) : void
    {
        $sql = 'SELECT events_created AS created, events_updated AS updated FROM events WHERE events_id = ' . $event->getId();

        try {
            $stmt = $this->pdo->query($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($data)) {
                throw new NoEntityException('No entit found for this event id : ' . $event->getId());
            }
            $event->setCreated($data[0]['created']);
            $event->setUpdated($data[0]['updated']);
        } catch (\PDOException $e) {
            throw $e;
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
