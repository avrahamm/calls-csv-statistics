<?php

namespace App\Entity;

use App\Repository\UploadedFileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UploadedFileRepository::class)]
#[ORM\Table(name: 'uploaded_files')]
#[ORM\Index(name: 'idx_uploaded_files_status', columns: ['status'])]
class UploadedFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $file_name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $uploaded_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $processed_at = null;

    #[ORM\Column(length: 20, options: ["default" => "pending"])]
    private ?string $status = 'pending';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $error_message = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $phones_enriched = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $ips_enriched = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileName(): ?string
    {
        return $this->file_name;
    }

    public function setFileName(string $file_name): self
    {
        $this->file_name = $file_name;

        return $this;
    }

    public function getUploadedAt(): ?\DateTimeInterface
    {
        return $this->uploaded_at;
    }

    public function setUploadedAt(\DateTimeInterface $uploaded_at): self
    {
        $this->uploaded_at = $uploaded_at;

        return $this;
    }

    public function getProcessedAt(): ?\DateTimeInterface
    {
        return $this->processed_at;
    }

    public function setProcessedAt(?\DateTimeInterface $processed_at): self
    {
        $this->processed_at = $processed_at;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

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

    public function getPhonesEnriched(): ?\DateTimeInterface
    {
        return $this->phones_enriched;
    }

    public function setPhonesEnriched(?\DateTimeInterface $phones_enriched): self
    {
        $this->phones_enriched = $phones_enriched;

        return $this;
    }

    public function getIpsEnriched(): ?\DateTimeInterface
    {
        return $this->ips_enriched;
    }

    public function setIpsEnriched(?\DateTimeInterface $ips_enriched): self
    {
        $this->ips_enriched = $ips_enriched;

        return $this;
    }
}
