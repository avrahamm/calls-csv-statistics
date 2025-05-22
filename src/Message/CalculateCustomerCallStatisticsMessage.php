<?php

namespace App\Message;

class CalculateCustomerCallStatisticsMessage
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