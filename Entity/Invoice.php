<?php

namespace Padam87\SimplePayBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class Invoice extends CustomerInfo
{
}
