<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 25.02.2019
 * Time: 19:41
 */

namespace Ergo\Business;

class Error implements EntityInterface
{
    /** @var string */
    private $name;

    /** @var string */
    private $description;

    /** const */
    public const ERR_NOT_FOUND = 'Not Found';
    public const ERR_BAD_REQUEST = 'Bad Request';
    public const ERR_UNAUTHORIZED = 'Unauthorized';

    public function __construct(string $name, string $description)
    {
        $this->name = $name;
        $this->description = $description;
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
     * @return Error
     */
    public function setName(string $name): self
    {
        $this->name = $name;
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
     * @return Error
     */
    public function setDescription(string $description): self
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
            'error' => $this->name,
            'error_description' => ucfirst($this->description)
        ];
    }
}
