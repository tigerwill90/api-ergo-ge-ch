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

    /** @var string */
    private $firstname;

    /** @var string */
    private $lastname;

    /** @var bool */
    private $active;

    /** @var string[] */
    private $officesName;

    /** @var int[] */
    private $officesId;

    public function __construct(array $user, array $officesId = [], array $officesName = [])
    {
        if (!empty($user['id'])) $this->id = (int) $user['id'];
        $this->email = $user['email'];
        $this->hashedPassword = $user['hashedPassword'];
        $this->roles = $user['roles'];
        $this->firstname = $user['firstname'];
        $this->lastname = $user['lastname'];
        $this->active = (bool) $user['active'];
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
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     * @return User
     */
    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     * @return User
     */
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return User
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getOfficesName() : array
    {
        return $this->officesName;
    }

    /**
     * @param string[] $officesName
     * @return User
     */
    public function setOfficesName(array $officesName): self
    {
        $this->officesName = $officesName;
        return $this;
    }

    /**
     * @return int[]
     */
    public function getOfficesId() : array
    {
        return $this->officesId;
    }

    /**
     * @param string[] $officesId
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
        $user = [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => ucfirst($this->firstname),
            'last_name' => ucfirst($this->lastname),
            'active' => $this->active,
            'roles' => explode(' ', $this->roles)
        ];

        if (!empty($this->officesId)) {
            $user['offices_id'] = $this->officesId;
        }

        if (!empty($this->officesName)) {
            $user['offices_name'] = $this->officesName;
        }

        return $user;
    }
}
