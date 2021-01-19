<?php

namespace STS\UploadServer\Results;

use Illuminate\Http\UploadedFile;
use STS\UploadServer\Events\ChunkedUploadRetrying;

class RetryChunkedUpload extends AbstractResult
{
    /** @var int */
    protected $nextOffset = 0;

    public function __construct(UploadedFile $file, $nextOffset = 0, $meta = [])
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
