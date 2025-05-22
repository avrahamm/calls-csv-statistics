<?php

namespace App\Message;

class EnrichWithinSameContMessage
{
    private int $uploadedFileId;

    public function __construct(int $uploadedFileId)
    {
        $this->uploadedFileId = $uploadedFileId;
    }

    public function getUploadedFileId(): int
    {
        return $this->uploadedFileId;
    }
}