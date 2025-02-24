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

    /** @var string[] */
    private $dates;

    /** @var string */
    private $description;

    /** @var Url[] */
    private $urls;

    /** @var string */
    private $imgAlt;

    /** @var string */
    private $imgId;

    /** @var string */
    private $imgName;

    /** @var string */
    private $created;

    /** @var string */
    private $updated;

    public function __construct(array $event)
    {
        if (!empty($event['id'])) {
            $this->id = $event['id'];
        }
        $this->title = $event['title'];
        $this->subtitle = $event['subtitle'];
        $this->dates = $event['dates'];
        $this->description = $event['description'];
        $this->imgAlt = $event['imgAlt'];
        $this->imgName = $event['imgName'];
        $this->imgId = $event['imgId'];
        if (!empty($event['created'])) {
            $this->created = $event['created'];
        }
        if (!empty($event['updated'])) {
            $this->updated = $event['updated'];
        }
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
    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    /**
     * @param string $subtitle
     * @return Event
     */
    public function setSubtitle(?string $subtitle): self
    {
        $this->subtitle = $subtitle;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getDates(): array
    {
        return $this->dates;
    }

    /**
     * @param string[] $dates
     * @return Event
     */
    public function setDates(array $dates): self
    {
        $this->dates = $dates;
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
     * @return Url[]
     */
    public function getUrls(): array
    {
        return $this->urls;
    }

    /**
     * @param Url[] $urls
     * @return Event
     */
    public function setUrls(array $urls): self
    {
        $this->urls = $urls;
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

    /**
     * @return string
     */
    public function getImgAlt(): string
    {
        return $this->imgAlt;
    }

    /**
     * @param string $imgAlt
     * @return Event
     */
    public function setImgAlt(string $imgAlt): self
    {
        $this->imgAlt = $imgAlt;
        return $this;
    }

    /**
     * @return string
     */
    public function getImgId(): string
    {
        return $this->imgId;
    }

    /**
     * @param string $imgId
     * @return Event
     */
    public function setImgId(string $imgId): self
    {
        $this->imgId = $imgId;
        return $this;
    }

    /**
     * @return string
     */
    public function getImgName(): string
    {
        return $this->imgName;
    }

    /**
     * @param string $imgName
     * @return Event
     */
    public function setImgName(string $imgName): self
    {
        $this->imgName = $imgName;
        return $this;
    }


    public function getEntity(): array
    {

        $entity = [
            'id' => $this->id,
            'title' => ucfirst($this->title),
            'subtitle' => $this->subtitle !== null ? ucfirst($this->subtitle) : null,
            'urls' => [],
            'dates' => [],
            'description' => $this->description,
            'img_name' => $this->imgName,
            'img_alt' => $this->imgAlt,
            'created' => $this->created,
            'updated' => $this->updated
        ];

        foreach ($this->dates as $stringDate) {
            try {
                $date = $date = new \DateTime($stringDate);
                $entity['dates'][] = $date->format(\DateTime::ATOM);
            } catch (\Exception $e) {
                throw new \RuntimeException($e->getMessage());
            }
        }

        foreach ($this->urls as $url) {
            $entity['urls'][] = $url->getEntity();
        }

        return $entity;
    }

    public function getCollection(): array
    {
        return $this->getEntity();
    }
}
