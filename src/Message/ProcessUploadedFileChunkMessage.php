<?php

namespace App\Message;

class ProcessUploadedFileChunkMessage
{
    private string $chunkFilename;
    private string $batchId;
    private int $uploadedFileId;

    public function __construct(string $chunkFilename, string $batchId, int $uploadedFileId)
    {
        $this->chunkFilename = $chunkFilename;
        $this->batchId = $batchId;
        $this->uploadedFileId = $uploadedFileId;
    }

    public function getChunkFilename(): string
    {
        return $this->chunkFilename;
    }

    public function getBatchId(): string
    {
        return $this->batchId;
    }

    public function getUploadedFileId(): int
    {
        return $this->uploadedFileId;
    }
}