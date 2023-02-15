<?php

namespace Padam87\SimplePayBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

class CustomerInfo
{
    #[ORM\Column(nullable: true)]
    protected ?string $name = null;

    #[ORM\Column(nullable: true)]
    protected ?string $company = null;

    #[ORM\Column(nullable: true)]
    protected ?string $country = null;

    #[ORM\Column(nullable: true)]
    protected ?string $state = null;

    #[ORM\Column(nullable: true)]
    protected ?string $city = null;

    #[ORM\Column(nullable: true)]
    protected ?string $zip = null;

    #[ORM\Column(nullable: true)]
    protected ?string $address = null;

    #[ORM\Column(nullable: true)]
    protected ?string $address2 = null;

    #[ORM\Column(nullable: true)]
    protected ?string $phone = null;

    public function toArray(): ?array
    {
        $array = [
            'name' => $this->getName(),
            'company' => $this->getCompany(),
            'country' => $this->getCountry(),
            'state' => $this->getState(),
            'city' => $this->getCity(),
            'zip' => $this->getZip(),
            'address' => $this->getAddress(),
            'address2' => $this->getAddress2(),
            'phone' => $this->getPhone(),
        ];

        return array_filter($array, function ($value) {
            return $value !== null;
        });
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getZip(): ?string
    {
        return $this->zip;
    }

    public function setZip(?string $zip): self
    {
        $this->zip = $zip;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function setAddress2(?string $address2): self
    {
        $this->address2 = $address2;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }
}
