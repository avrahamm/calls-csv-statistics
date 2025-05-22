<?php

namespace App\Message;

class FinalizeCallsImportMessage
{
    private string $batchId;
    private int $uploadedFileId;
    private array $chunkFilenames;

    public function __construct(string $batchId, int $uploadedFileId, array $chunkFilenames)
    {
        $this->batchId = $batchId;
        $this->uploadedFileId = $uploadedFileId;
        $this->chunkFilenames = $chunkFilenames;
    }

    public function getBatchId(): string
    {
        return $this->batchId;
    }

    public function getUploadedFileId(): int
    {
        return $this->uploadedFileId;
    }

    public function getChunkFilenames(): array
    {
        return $this->chunkFilenames;
    }
}