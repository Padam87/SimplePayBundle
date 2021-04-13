<?php

namespace Padam87\SimplePayBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable()
 */
class Recurring
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $times = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?\DateTime $until = null;

    /**
     * @ORM\Column(type="float")
     */
    protected float $maxAmount = 0.0;

    public function toArray(): array
    {
        return [
            'times' => $this->getTimes(),
            'until' => $this->getUntil() ? $this->getUntil()->format(DATE_ATOM) : null,
            'maxAmount' => intval($this->getMaxAmount()) == $this->getMaxAmount() ? intval($this->getMaxAmount()) : $this->getMaxAmount(),
        ];
    }

    public function getTimes(): ?int
    {
        return $this->times;
    }

    public function setTimes(?int $times): self
    {
        $this->times = $times;

        return $this;
    }

    public function getUntil(): ?\DateTime
    {
        return $this->until;
    }

    public function setUntil(?\DateTime $until): self
    {
        $this->until = $until;

        return $this;
    }

    public function getMaxAmount(): float
    {
        return $this->maxAmount;
    }

    public function setMaxAmount(float $maxAmount): self
    {
        $this->maxAmount = $maxAmount;

        return $this;
    }
}
