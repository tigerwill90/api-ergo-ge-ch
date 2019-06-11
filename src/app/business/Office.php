<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 02.12.2018
 * Time: 01:31
 */

namespace Ergo\Business;

class Office implements EntityInterface
{
    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $email;

    /** @var Contact[] */
    private $contacts;

    public function __construct(array $office, array $contacts = [])
    {
        if (!empty($office['id'])) $this->id = (int) $office['id'];
        $this->name = $office['name'];
        $this->email = $office['email'];
        $this->contacts = $contacts;
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
     * @return Office
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
     * @return Office
     */
    public function setName(string $name): self
    {
        $this->name = $name;
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
     * @return Office
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return Contact[]
     */
    public function getContacts(): array
    {
        return $this->contacts;
    }

    /**
     * @param Contact[] $contacts
     * @return Office
     */
    public function setContacts(array $contacts): self
    {
        $this->contacts = $contacts;
        return $this;
    }


    /**
     * @return array
     */
    public function getCollection() : array
    {
        return $this->getEntity();
    }

    /**
     * @return array
     */
    public function getEntity(): array
    {
        $entity = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => strtolower($this->email),
            'contacts' => []
        ];

        foreach ($this->contacts as $contact) {
            $entity['contacts'][] = $contact->getEntity();
        }

        return $entity;
    }
}
