<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 25.02.2019
 * Time: 13:38
 */

namespace Ergo\Business;

class Category implements EntityInterface
{
    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $description;

    public function __construct(array $category)
    {
        if (!empty($category['id'])) $this->id = (int) $category['id'];
        $this->name = $category['name'];
        if (!empty($category['description'])) $this->description = $category['description'];
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
     * @return Category
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
     * @return Category
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Category
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return array
     */
    public function getEntity(): array
    {
        return [
            'id' => $this->id,
            'name' => ucfirst($this->name),
            'description' => $this->description
        ];
    }
}
