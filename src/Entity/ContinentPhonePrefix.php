<?php

namespace App\Entity;

use App\Repository\ContinentPhonePrefixRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContinentPhonePrefixRepository::class)]
#[ORM\Table(name: 'continent_phone_prefix')]
class ContinentPhonePrefix
{
    #[ORM\Id]
    #[ORM\Column(length: 16)]
    private ?string $phone_prefix = null;

    #[ORM\Column(length: 2)]
    private ?string $continent_code = null;

    public function getPhonePrefix(): ?string
    {
        return $this->phone_prefix;
    }

    public function setPhonePrefix(string $phone_prefix): self
    {
        $this->phone_prefix = $phone_prefix;

        return $this;
    }

    public function getContinentCode(): ?string
    {
        return $this->continent_code;
    }

    public function setContinentCode(string $continent_code): self
    {
        $this->continent_code = $continent_code;

        return $this;
    }
}