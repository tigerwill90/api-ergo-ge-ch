<?php

namespace Ergo\Business;

class Url implements EntityInterface
{

    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $url;

    public function __construct(array $url)
    {
        if (!empty($url['id'])) {
            $this->id = $url['id'];
        }
        $this->name = $url['name'];
        $this->url = $url['url'];
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
     * @return Url
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Url
     */
    public function setName(string $name): self
    {
        $this->name = $name;
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
     * @return Url
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getEntity(): array
    {
        return [
            'id' => $this->id,
            'name' => ucfirst($this->name),
            'url' => strtolower($this->url)
        ];
    }

    public function getCollection(): array
    {
        return $this->getEntity();
    }
}