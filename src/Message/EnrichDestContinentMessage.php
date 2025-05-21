<?php

namespace App\Message;

class EnrichDestContinentMessage
{
    private int $uploadedFileId;
    private array $uniquePhones;

    public function __construct(int $uploadedFileId, array $uniquePhones)
    {
        $this->uploadedFileId = $uploadedFileId;
        $this->uniquePhones = $uniquePhones;
    }

    public function getUploadedFileId(): int
    {
        return $this->uploadedFileId;
    }

    public function getUniquePhones(): array
    {
        return $this->uniquePhones;
    }
}