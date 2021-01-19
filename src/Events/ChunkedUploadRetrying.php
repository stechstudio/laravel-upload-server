<?php

namespace STS\UploadServer\Events;

use Illuminate\Http\UploadedFile;

class ChunkedUploadRetrying
{
    /** @var UploadedFile */
    public $file;

    /** @var string */
    public $id;

    /** @var array */
    public $meta;

    public function __construct(UploadedFile $file, $meta = [])
    {
        $this->file = $file;
        $this->id = $file->id();
        $this->meta = $meta;
    }
}
