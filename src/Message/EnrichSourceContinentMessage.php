<?php

namespace App\Message;

class EnrichSourceContinentMessage
{
    private int $uploadedFileId;
    private int $start;
    private int $end;
    private int $offset;

    public function __construct(int $uploadedFileId, int $start, int $end, int $offset = 10)
    {
        $this->uploadedFileId = $uploadedFileId;
        $this->start = $start;
        $this->end = $end;
        $this->offset = $offset;
    }

    public function getUploadedFileId(): int
    {
        return $this->uploadedFileId;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getEnd(): int
    {
        return $this->end;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }
}