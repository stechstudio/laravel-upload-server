<?php

namespace STS\UploadServer\Servers\FilePond;

use STS\UploadServer\Servers\AbstractStep;

/** @mixin AbstractStep */
trait PayloadHelper
{
    public function expectedSize(): int
    {
        return $this->request->header('Upload-Length');
    }

    public function patch(): string
    {
        return $this->request->input('patch');
    }

    public function offset(): int
    {
        return $this->request->header('Upload-Offset');
    }

    public function clientName(): string
    {
        return $this->request->header('Upload-Name');
    }

    public function percentComplete(): int
    {
        return ($this->file()->getSize() / $this->expectedSize()) * 100;
    }
}
