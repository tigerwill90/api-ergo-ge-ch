<?php

namespace Ergo\Business;

class User implements EntityInterface
{
    /** @var int */
    private $id;

    /** @var string */
    private $email;

    /** @var string */
    private $hashedPassword;

    /** @var string */
    private $roles;

    /** @var []string */
    private $officesName;

    /** @var []int */
    private $officesId;

    public function __construct(array $user, array $officesId = [], array $officesName = [])
    {
        if (!empty($user['id'])) $this->id = (int) $user['id'];
        $this->email = $user['email'];
        $this->hashedPassword = $user['hashedPassword'];
        $this->roles = $user['roles'];
        $this->officesId = $officesId;
        $this->officesName = $officesName;
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
     * @return User
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getHashedPassword(): string
    {
        return $this->hashedPassword;
    }

    /**
     * @param string $hashedPassword
     * @return User
     */
    public function setHashedPassword(string $hashedPassword): self
    {
        $this->hashedPassword = $hashedPassword;
        return $this;
    }

    /**
     * @return string
     */
    public function getRoles(): string
    {
        return $this->roles;
    }

    /**
     * @param string $roles
     * @return User
     */
    public function setRoles(string $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOfficesName()
    {
        return $this->officesName;
    }

    /**
     * @param array $officesName
     * @return User
     */
    public function setOfficesName(array $officesName): self
    {
        $this->officesName = $officesName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOfficesId()
    {
        return $this->officesId;
    }

    /**
     * @param array $officesId
     * @return User
     */
    public function setOfficesId(array $officesId): self
    {
        $this->officesId = $officesId;
        return $this;
    }

    /**
     * @return array
     */
    public function getEntity(): array
    {
        return [
            'email' => $this->email,
            'roles' => explode(' ', $this->roles),
            'offices_name' => $this->officesName,
            'offices_id' => $this->officesId
        ];
    }
}
