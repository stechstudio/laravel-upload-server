<?php

namespace STS\UploadServer\Results;

use STS\UploadServer\Events\ChunkedUploadRetrying;
use STS\UploadServer\UploadProgress;

class RetryChunkedUpload extends AbstractResult
{
    public function announce()
    {
        event(new ChunkedUploadRetrying(
            $this->file(),
            $this->meta
        ));
    }

    public function response()
    {
        return $this->textResponse('')
            ->header('Upload-Offset', $this->file()->getSize());
    }

    public function progress(): UploadProgress
    {
        return $this->getProgress($this->fileId);
    }
}
