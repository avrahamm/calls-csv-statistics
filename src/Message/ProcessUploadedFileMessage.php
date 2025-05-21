<?php

namespace App\Message;

class ProcessUploadedFileMessage
{
    private int $fileId;

    public function __construct(int $fileId)
    {
        $this->fileId = $fileId;
    }

    public function getFileId(): int
    {
        return $this->fileId;
    }
}