<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 29.03.2019
 * Time: 14:25
 */

namespace Ergo\Business;

class Contact implements EntityInterface
{
    /** @var int */
    private $id;

    /** @var string */
    private $street;

    /** @var string */
    private $city;

    /** @var string */
    private $npa;

    /** @var string */
    private $cp;

    /** @var string */
    private $phone;

    /** @var string */
    private $fax;

    public function __construct(array $contact)
    {
        if (!empty($contact['id'])) $this->id = (int)$contact['id'];
        $this->street = $contact['street'];
        $this->city = $contact['city'];
        $this->npa = $contact['npa'];
        if (!empty($contact['cp'])) $this->cp = $contact['cp'];
        if (!empty($contact['phone'])) $this->phone = $contact['phone'];
        if (!empty($contact['fax'])) $this->fax = $contact['fax'];
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
     * @return Contact
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @param string $street
     * @return Contact
     */
    public function setStreet(string $street): self
    {
        $this->street = $street;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return Contact
     */
    public function setCity(string $city): self
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string
     */
    public function getNpa(): string
    {
        return $this->npa;
    }

    /**
     * @param string $npa
     * @return Contact
     */
    public function setNpa(string $npa): self
    {
        $this->npa = $npa;
        return $this;
    }

    /**
     * @return string
     */
    public function getCp(): string
    {
        return $this->cp;
    }

    /**
     * @param string $cp
     * @return Contact
     */
    public function setCp(string $cp): self
    {
        $this->cp = $cp;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return Contact
     */
    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getFax(): string
    {
        return $this->fax;
    }

    /**
     * @param string $fax
     * @return Contact
     */
    public function setFax(string $fax): self
    {
        $this->fax = $fax;
        return $this;
    }

    /**
     * @return array
     */
    public function getEntity(): array
    {
        return [
            'street' => $this->street,
            'city' => $this->city,
            'npa' => $this->npa,
            'cp' => $this->cp,
            'phone' => $this->phone,
            'fax' => $this->fax
        ];
    }
}
