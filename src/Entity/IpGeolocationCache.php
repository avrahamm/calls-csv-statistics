<?php

namespace App\Entity;

use App\Repository\IpGeolocationCacheRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IpGeolocationCacheRepository::class)]
#[ORM\Table(name: 'ip_geolocation_cache')]
class IpGeolocationCache
{
    #[ORM\Id]
    #[ORM\Column(length: 45)]
    private ?string $ip_address = null;

    #[ORM\Column(length: 2)]
    private ?string $continent_code = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $last_checked = null;

    public function getIpAddress(): ?string
    {
        return $this->ip_address;
    }

    public function setIpAddress(string $ip_address): self
    {
        $this->ip_address = $ip_address;

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

    public function getLastChecked(): ?\DateTimeInterface
    {
        return $this->last_checked;
    }

    public function setLastChecked(\DateTimeInterface $last_checked): self
    {
        $this->last_checked = $last_checked;

        return $this;
    }
}