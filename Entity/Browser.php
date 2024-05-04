<?php

namespace Padam87\SimplePayBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class Browser
{
    #[ORM\Column(nullable: true)]
    protected ?string $accept = null;

    #[ORM\Column(nullable: true)]
    protected ?string $agent = null;

    #[ORM\Column(nullable: true)]
    protected ?string $ip = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    protected ?bool $java = null;

    #[ORM\Column(nullable: true)]
    protected ?string $lang = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?string $color = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?string $height = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?string $width = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?string $tz = null;

    public function toArray(): array
    {
        return [
            'accept' => $this->getAccept(),
            'agent' => $this->getAgent(),
            'ip' => $this->getIp(),
            'java' => $this->isJava(),
            'lang' => $this->getLang(),
            'color' => $this->getColor(),
            'height' => $this->getHeight(),
            'width' => $this->getWidth(),
            'tz' => $this->getTz(),
        ];
    }

    public function getAccept(): ?string
    {
        return $this->accept;
    }

    public function setAccept(?string $accept): self
    {
        $this->accept = $accept;

        return $this;
    }

    public function getAgent(): ?string
    {
        return $this->agent;
    }

    public function setAgent(?string $agent): self
    {
        $this->agent = $agent;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function isJava(): bool
    {
        return $this->java;
    }

    public function setJava(bool $java): self
    {
        $this->java = $java;

        return $this;
    }

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function setLang(?string $lang): self
    {
        $this->lang = $lang;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getHeight(): ?string
    {
        return $this->height;
    }

    public function setHeight(?string $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getWidth(): ?string
    {
        return $this->width;
    }

    public function setWidth(?string $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getTz(): ?string
    {
        return $this->tz;
    }

    public function setTz(?string $tz): self
    {
        $this->tz = $tz;

        return $this;
    }
}
