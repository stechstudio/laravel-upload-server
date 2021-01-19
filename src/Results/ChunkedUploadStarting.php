<?php

namespace STS\UploadServer\Results;

use STS\UploadServer\Events\ChunkedUploadStarted;

class ChunkedUploadStarting extends AbstractResult
{
    protected $size = 0;

    protected $fullPath;

    public function __construct($fileId, $fullPath, $size, $meta = [])
    {
        $this->fileId = $fileId;
        $this->fullPath = $fullPath;
        $this->meta = $meta;
        $this->size = $size;

        $this->announce();
    }

    public function announce()
    {
        event(new ChunkedUploadStarted(
            $this->fileId,
            $this->size,
            $this->meta
        ));
    }
}
