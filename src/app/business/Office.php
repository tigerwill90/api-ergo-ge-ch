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
    private $address;

    /** @var string */
    private $npa;

    /** @var  string */
    private $city;

    /** @var string */
    private $cp;

    /** @var string */
    private $name;

    /** @var string */
    private $phone;

    /** @var string */
    private $fax;

    /** @var string */
    private $email;

    /** @var string */
    private $district;

    public function __construct(array $office)
    {
        if (!empty($office['id'])) $this->id = (int)$office['id'];
        if (!empty($office['cp'])) $this->cp = $office['cp'];
        if (!empty($office['phone'])) $this->phone = $office['phone'];
        if (!empty($office['fax'])) $this->fax = $office['fax'];
        if (!empty($office['district'])) $this->district = $office['district'];
        $this->address = $office['address'];
        $this->npa = $office['npa'];
        $this->city = $office['city'];
        $this->name = $office['name'];
        $this->email = $office['email'];
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
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @param string $address
     * @return Office
     */
    public function setAddress(string $address): self
    {
        $this->address = $address;
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
     * @return Office
     */
    public function setNpa(string $npa): self
    {
        $this->npa = $npa;
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
     * @return Office
     */
    public function setCity(string $city): self
    {
        $this->city = $city;
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
     * @return Office
     */
    public function setCp(string $cp): self
    {
        $this->cp = $cp;
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
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return Office
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
     * @return Office
     */
    public function setFax(string $fax): self
    {
        $this->fax = $fax;
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
     * @return string
     */
    public function getDistrict(): string
    {
        return $this->district;
    }

    /**
     * @param string $district
     * @return Office
     */
    public function setDistrict(string $district): self
    {
        $this->district = $district;
        return $this;
    }

    /**
     * @return array
     */
    public function getEntity(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'npa' => $this->npa,
            'city' => $this->city,
            'cp' => $this->cp,
            'phone' => $this->phone,
            'fax' => $this->fax,
            'email' => $this->email,
            'district' => $this->district
        ];
    }
}
