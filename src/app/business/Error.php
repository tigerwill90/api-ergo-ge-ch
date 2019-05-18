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

    /** @var array  */
    private $context;

    /** const */
    public const ERR_NOT_FOUND = 'Not Found';
    public const ERR_BAD_REQUEST = 'Bad Request';
    public const ERR_UNAUTHORIZED = 'Unauthorized';
    public const ERR_CONFLICT = 'Conflict';

    public function __construct(string $name, string $description, array $context = [])
    {
        $this->name = $name;
        $this->description = $description;
        $this->context = $context;
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
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param array $context
     * @return Error
     */
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return array
     */
    public function getEntity(): array
    {
        $error = [
            'error' => ucfirst($this->name),
            'error_description' => ucfirst($this->description)
        ];

        if (!empty($this->context)) {
            $error['error_context'] = $this->context;
        }

        return $error;
    }
}
