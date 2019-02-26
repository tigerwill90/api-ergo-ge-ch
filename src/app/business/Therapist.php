<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 25.02.2019
 * Time: 22:34
 */

namespace Ergo\Business;

class Therapist implements EntityInterface
{
    /** @var int */
    private $id;

    /** @var string */
    private $title;

    /** @var string */
    private $firstname;

    /** @var string */
    private $lastname;

    /** @var bool */
    private $home;

    /** @var int */
    private $officeId;

    /** @var array */
    private $phones;

    /** @var array */
    private $emails;

    /** @var array */
    private $categories;

    public function __construct(array $therapist, array $phones = [], array $emails = [], array $categories = [])
    {
        if (!empty($therapist['id'])) $this->id = (int)$therapist['id'];
        if (!empty($therapist['officeId'])) $this->officeId = (int)$therapist['officeId'];
        $this->title = $therapist['title'];
        $this->firstname = $therapist['firstname'];
        $this->lastname = $therapist['lastname'];
        $this->home = (bool)$therapist['home'];
        $this->phones = $phones;
        $this->emails = $emails;
        $this->categories = $categories;
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
     * @return Therapist
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
     * @return Therapist
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
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
     * @return Therapist
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
     * @return Therapist
     */
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHome(): bool
    {
        return $this->home;
    }

    /**
     * @param bool $home
     * @return Therapist
     */
    public function setHome(bool $home): self
    {
        $this->home = $home;
        return $this;
    }

    /**
     * @return int
     */
    public function getOfficeId(): int
    {
        return $this->officeId;
    }

    /**
     * @param int $officeId
     * @return Therapist
     */
    public function setOfficeId(int $officeId): self
    {
        $this->officeId = $officeId;
        return $this;
    }

    /**
     * @return array
     */
    public function getPhones(): array
    {
        return $this->phones;
    }

    /**
     * @param array $phones
     * @return Therapist
     */
    public function setPhones(array $phones): self
    {
        $this->phones = $phones;
        return $this;
    }

    /**
     * @return array
     */
    public function getEmails(): array
    {
        return $this->emails;
    }

    /**
     * @param array $emails
     * @return Therapist
     */
    public function setEmails(array $emails): self
    {
        $this->emails = $emails;
        return $this;
    }

    /**
     * @return array
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     * @return Therapist
     */
    public function setCategories(array $categories): self
    {
        $this->categories = $categories;
        return $this;
    }

    /**
     * @return array
     */
    public function getEntity(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'home' => $this->home,
            'officeId' => $this->officeId,
            'phones' => $this->phones,
            'emails' => $this->emails,
            'categories' => $this->categories
        ];
    }
}
