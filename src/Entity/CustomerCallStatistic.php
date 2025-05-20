<?php

namespace App\Entity;

use App\Repository\CustomerCallStatisticRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomerCallStatisticRepository::class)]
#[ORM\Table(name: 'customer_call_statistics')]
class CustomerCallStatistic
{
    #[ORM\Id]
    #[ORM\Column]
    private ?int $customer_id = null;

    #[ORM\Column]
    private ?int $num_calls_within_same_continent = null;

    #[ORM\Column]
    private ?int $total_duration_within_same_cont = null;

    #[ORM\Column]
    private ?int $total_num_calls = null;

    #[ORM\Column]
    private ?int $total_calls_duration = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $last_updated = null;

    public function getCustomerId(): ?int
    {
        return $this->customer_id;
    }

    public function setCustomerId(int $customer_id): self
    {
        $this->customer_id = $customer_id;

        return $this;
    }

    public function getNumCallsWithinSameContinent(): ?int
    {
        return $this->num_calls_within_same_continent;
    }

    public function setNumCallsWithinSameContinent(int $num_calls_within_same_continent): self
    {
        $this->num_calls_within_same_continent = $num_calls_within_same_continent;

        return $this;
    }

    public function getTotalDurationWithinSameCont(): ?int
    {
        return $this->total_duration_within_same_cont;
    }

    public function setTotalDurationWithinSameCont(int $total_duration_within_same_cont): self
    {
        $this->total_duration_within_same_cont = $total_duration_within_same_cont;

        return $this;
    }

    public function getTotalNumCalls(): ?int
    {
        return $this->total_num_calls;
    }

    public function setTotalNumCalls(int $total_num_calls): self
    {
        $this->total_num_calls = $total_num_calls;

        return $this;
    }

    public function getTotalCallsDuration(): ?int
    {
        return $this->total_calls_duration;
    }

    public function setTotalCallsDuration(int $total_calls_duration): self
    {
        $this->total_calls_duration = $total_calls_duration;

        return $this;
    }

    public function getLastUpdated(): ?\DateTimeInterface
    {
        return $this->last_updated;
    }

    public function setLastUpdated(\DateTimeInterface $last_updated): self
    {
        $this->last_updated = $last_updated;

        return $this;
    }
}