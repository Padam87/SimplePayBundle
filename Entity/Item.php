<?php

namespace Padam87\SimplePayBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\MappedSuperclass]
class Item
{
    #[Assert\NotBlank]
    #[ORM\Column]
    protected ?string $ref = null;

    #[Assert\NotBlank]
    #[ORM\Column]
    protected ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $description = null;

    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual(1)]
    #[ORM\Column(type: 'float')]
    protected ?int $amount = null;

    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual(0)]
    #[ORM\Column(type: 'float')]
    protected ?float $price = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'integer')]
    protected ?int $tax = null;

    public function toArray(): array
    {
        return [
            'ref' => $this->getRef(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'amount' => $this->getAmount(),
            'price' => (int) $this->getPrice() == $this->getPrice() ? (int) $this->getPrice() : $this->getPrice(),
            'tax' => $this->getTax(),
        ];
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(?string $ref): self
    {
        $this->ref = $ref;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(?int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getTax(): ?int
    {
        return $this->tax;
    }

    public function setTax(?int $tax): self
    {
        $this->tax = $tax;

        return $this;
    }
}
