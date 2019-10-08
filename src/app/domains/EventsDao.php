<?php

namespace Ergo\Domains;

use Ergo\Business\Event;
use Ergo\Exceptions\NoEntityException;
use Ergo\Exceptions\UniqueException;
use Psr\Log\LoggerInterface;
use PDO;

class EventsDao
{
    /* @var PDO */
    private $pdo;

    /** @var LoggerInterface */
    private $logger;

    private const INTEGRITY_CONSTRAINT_VIOLATION = 23000;

    public function __construct(PDO $pdo, LoggerInterface $logger = null)
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
    }

    /**
     * @param int $id
     * @return Event
     * @throws NoEntityException
     */
    public function getEvent(int $id) : Event
    {
        $sql = '
                SELECT 
                        events_id AS id, events_title AS title, events_img_alt AS imgAlt, events_subtitle AS subtitle, events_date AS date, 
                        events_description AS description, events_url AS url, events_img_id AS imgId, events_img_name AS imgName,
                        events_created AS created, events_updated AS updated
                    FROM events 
                    WHERE events_id = 
               ' . $id;

        try {
            $stmt = $this->pdo->query($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($data)) {
                throw new NoEntityException('No entity found for this event id : ' . $id);
            }
            return new Event($data[0]);
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @return Event[]
     * @throws NoEntityException
     */
    public function getEvents() : array
    {
        $sql = '
                SELECT 
                        events_id AS id, events_title AS title, events_img_alt AS imgAlt, events_subtitle AS subtitle, events_date AS date, 
                        events_description AS description, events_url AS url, events_img_id AS imgId, events_img_name AS imgName,
                        events_created AS created, events_updated AS updated
                    FROM events 
               ';

        try {
            $stmt = $this->pdo->query($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($data)) {
                throw new NoEntityException('No entity found for events');
            }
            $events = [];
            foreach ($data as $event) {
                $events[] = new Event($event);
            }
            return $events;
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param Event $event
     * @throws NoEntityException
     */
    public function createEvent(Event $event) : void
    {
        $sql =  'INSERT INTO events (events_title, events_img_alt, events_subtitle, events_date, events_description, events_url, events_img_id, events_img_name) values (:title, :imgAlt, :subtitle, :date, :description, :url, :imgId, :imgName)';

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $title = $event->getTitle();
            $stmt->bindParam(':title', $title);
            $alt = $event->getImgAlt();
            $stmt->bindParam(':imgAlt', $alt);
            $subtitle = $event->getSubtitle();
            $stmt->bindParam(':subtitle', $subtitle);
            $date = $event->getDate();
            $stmt->bindParam(':date', $date);
            $description = $event->getDescription();
            $stmt->bindParam(':description', $description);
            $url = $event->getUrl();
            $stmt->bindParam(':url', $url);
            $imgId = $event->getImgId();
            $stmt->bindParam(':imgId', $imgId);
            $imgName = $event->getImgName();
            $stmt->bindParam(':imgName', $imgName);
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
     * @throws UniqueException
     */
    public function updateEvent(Event $event) : void
    {
        $sql = 'UPDATE events SET 
                  events_title = :title,
                  events_subtitle = :subtitle,
                  events_date = :date,
                  events_description = :description,
                  events_url = :url,
                  events_img_alt = :imgAlt,
                  events_img_name = :imgName,
                  events_img_id = :imgId
                WHERE events_id = :id';

        try {
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
            $imgAlt = $event->getImgAlt();
            $stmt->bindParam(':imgAlt', $imgAlt);
            $imgName = $event->getImgName();
            $stmt->bindParam(':imgName', $imgName);
            $imgId = $event->getImgId();
            $stmt->bindParam(':imgId', $imgId);
            $id = $event->getId();
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } catch (\PDOException $e) {
            if ((int) $e->getCode() === self::INTEGRITY_CONSTRAINT_VIOLATION) {
                throw new UniqueException('This event image id already exist', $e->getCode());
            }
            throw $e;
        }
    }

    /**
     * @param int $id
     * @throws NoEntityException
     */
    public function deleteEvent(int $id) : void
    {
        $sql = 'DELETE FROM events WHERE events_id = :id';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount() === 0) {
                throw new NoEntityException('No entity found for this event id : ' . $id);
            }
        } catch (\PDOException $e) {
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

    public function isImageIdExist(string $imgId) : bool
    {
        $sql = 'SELECT EXISTS(SELECT * FROM events WHERE events_img_id = :imgId)';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':imgId', $imgId);
            $stmt->execute();
            return (bool) $stmt->fetchAll(PDO::FETCH_COLUMN)[0];
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
