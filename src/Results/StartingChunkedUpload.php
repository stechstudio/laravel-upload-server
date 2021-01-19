<?php

namespace STS\UploadServer\Results;

use STS\UploadServer\UploadProgress;
use STS\UploadServer\Events\ChunkedUploadStarted;
use STS\UploadServer\Save\InitializedChunkFile;

class StartingChunkedUpload extends AbstractResult
{
    public function announce()
    {
        event(new ChunkedUploadStarted(
            $this->fileId,
            $this->expectedSize(),
            $this->meta
        ));
    }

    public function expectedSize()
    {
        return $this->handler()->getTotalSize();
    }

    public function progress(): UploadProgress
    {
        return UploadProgress::initialize(
            $this->fileId,
            $this->expectedSize(),
            $this->file()->getRealPath()
        );
    }
}
