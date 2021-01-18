<?php

namespace STS\UploadServer\Results;

use STS\UploadServer\Events\ChunkedUploadRetrying;
use STS\UploadServer\Upload;

class RetryChunkedUpload extends AbstractResult
{
    /** @var Upload  */
    protected $file;

    protected $nextOffset = 0;

    public function __construct(Upload $file, $nextOffset = 0, $meta = [])
    {
        $this->file = $file;
        $this->meta = $meta;
        $this->nextOffset = $nextOffset;

        $this->announce();
    }

    public function announce()
    {
        event(new ChunkedUploadRetrying(
            $this->file,
            $this->meta
        ));
    }

    public function response()
    {
        return $this->textResponse('')
            ->header('Upload-Offset', $this->nextOffset);
    }
}
