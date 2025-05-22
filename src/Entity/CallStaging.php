<?php

namespace App\Entity;

use App\Repository\CallStagingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CallStagingRepository::class)]
#[ORM\Table(name: 'calls_staging')]
#[ORM\Index(name: 'idx_batch_id', columns: ['batch_id'])]
#[ORM\Index(name: 'idx_customer_id', columns: ['customer_id'])]
#[ORM\Index(name: 'idx_source_ip', columns: ['source_ip'])]
#[ORM\Index(name: 'idx_call_date', columns: ['call_date'])]
class CallStaging
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $customer_id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $call_date = null;

    #[ORM\Column]
    private ?int $duration = null;

    #[ORM\Column(length: 32)]
    private ?string $dialed_number = null;

    #[ORM\Column(length: 45)]
    private ?string $source_ip = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $source_continent = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $dest_continent = null;

    #[ORM\Column(nullable: true)]
    private ?bool $within_same_cont = null;

    #[ORM\Column(nullable: true)]
    private ?int $uploaded_file_id = null;

    #[ORM\Column(length: 36)]
    private ?string $batch_id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $chunk_filename = null;

    #[ORM\Column(nullable: true)]
    private ?int $row_number_in_chunk = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_valid = true;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $error_message = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomerId(): ?int
    {
        return $this->customer_id;
    }

    public function setCustomerId(int $customer_id): self
    {
        $this->customer_id = $customer_id;

        return $this;
    }

    public function getCallDate(): ?\DateTimeInterface
    {
        return $this->call_date;
    }

    public function setCallDate(\DateTimeInterface $call_date): self
    {
        $this->call_date = $call_date;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDialedNumber(): ?string
    {
        return $this->dialed_number;
    }

    public function setDialedNumber(string $dialed_number): self
    {
        $this->dialed_number = $dialed_number;

        return $this;
    }

    public function getSourceIp(): ?string
    {
        return $this->source_ip;
    }

    public function setSourceIp(string $source_ip): self
    {
        $this->source_ip = $source_ip;

        return $this;
    }

    public function getSourceContinent(): ?string
    {
        return $this->source_continent;
    }

    public function setSourceContinent(?string $source_continent): self
    {
        $this->source_continent = $source_continent;

        return $this;
    }

    public function getDestContinent(): ?string
    {
        return $this->dest_continent;
    }

    public function setDestContinent(?string $dest_continent): self
    {
        $this->dest_continent = $dest_continent;

        return $this;
    }

    public function isWithinSameCont(): ?bool
    {
        return $this->within_same_cont;
    }

    public function setWithinSameCont(?bool $within_same_cont): self
    {
        $this->within_same_cont = $within_same_cont;

        return $this;
    }

    public function getUploadedFileId(): ?int
    {
        return $this->uploaded_file_id;
    }

    public function setUploadedFileId(?int $uploaded_file_id): self
    {
        $this->uploaded_file_id = $uploaded_file_id;

        return $this;
    }

    public function getBatchId(): ?string
    {
        return $this->batch_id;
    }

    public function setBatchId(string $batch_id): self
    {
        $this->batch_id = $batch_id;

        return $this;
    }

    public function getChunkFilename(): ?string
    {
        return $this->chunk_filename;
    }

    public function setChunkFilename(?string $chunk_filename): self
    {
        $this->chunk_filename = $chunk_filename;

        return $this;
    }

    public function getRowNumberInChunk(): ?int
    {
        return $this->row_number_in_chunk;
    }

    public function setRowNumberInChunk(?int $row_number_in_chunk): self
    {
        $this->row_number_in_chunk = $row_number_in_chunk;

        return $this;
    }

    public function isValid(): ?bool
    {
        return $this->is_valid;
    }

    public function setIsValid(?bool $is_valid): self
    {
        $this->is_valid = $is_valid;

        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->error_message;
    }

    public function setErrorMessage(?string $error_message): self
    {
        $this->error_message = $error_message;

        return $this;
    }
}