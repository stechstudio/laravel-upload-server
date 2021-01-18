<?php

namespace STS\UploadServer\Events;

use STS\UploadServer\Upload;

class ChunkedUploadRetrying
{
    /** @var Upload  */
    public $file;

    /** @var string */
    public $id;

    /** @var array */
    public $meta;

    public function __construct(Upload $file, $meta = [])
    {
        $this->file = $file;
        $this->id = $file->id();
        $this->meta = $meta;
    }
}
