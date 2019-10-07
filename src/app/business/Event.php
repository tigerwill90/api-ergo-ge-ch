<?php

namespace Ergo\Business;

class Event implements EntityInterface
{
    /** @var int */
    private $id;

    /** @var string */
    private $title;

    /** @var string */
    private $subtitle;

    /** @var string */
    private $date;

    /** @var string */
    private $description;

    /** @var string */
    private $url;

    /** @var string */
    private $created;

    /** @var string */
    private $updated;

    public function __construct(array $event)
    {
        if (!empty($event['id'])) $this->id = $event['id'];
        $this->title = $event['title'];
        if (!empty($event['subtitle'])) $this->subtitle = $event['subtitle'];
        if (!empty($event['date'])) $this->date = $event['date'];
        $this->description = $event['description'];
        if (!empty($event['url'])) $this->url = $event['url'];
        if (!empty($event['created'])) $this->created = $event['created'];
        if (!empty($event['updated'])) $this->updated = $event['updated'];
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Event
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Event
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubtitle(): string
    {
        return $this->subtitle;
    }

    /**
     * @param string $subtitle
     * @return Event
     */
    public function setSubtitle(string $subtitle): self
    {
        $this->subtitle = $subtitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param string $date
     * @return Event
     */
    public function setDate(string $date): self
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Event
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return Event
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getCreated(): string
    {
        return $this->created;
    }

    /**
     * @param string $created
     * @return Event
     */
    public function setCreated(string $created): self
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return string
     */
    public function getUpdated(): string
    {
        return $this->updated;
    }

    /**
     * @param string $updated
     * @return Event
     */
    public function setUpdated(string $updated): self
    {
        $this->updated = $updated;
        return $this;
    }


    public function getEntity(): array
    {
        return [
            'id' => $this->id,
            'title' => ucfirst($this->title),
            'subtitle' => ucfirst($this->subtitle),
            'date' => $this->date,
            'description' => $this->description,
            'url' => $this->url,
            'created' => $this->created,
            'updated' => $this->updated
        ];
    }

    public function getCollection(): array
    {
        return [
            'id' => $this->id,
            'title' => ucfirst($this->title),
            'date' => $this->date
        ];
    }
}
