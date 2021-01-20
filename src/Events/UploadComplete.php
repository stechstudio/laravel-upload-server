<?php

namespace STS\UploadServer\Events;

use STS\UploadServer\Storage\File;

class UploadComplete
{
    /** @var File */
    public $file;

    /** @var array */
    public $meta;

    public function __construct(File $file, $meta = [])
    {
        $this->file = $file;
        $this->meta = $meta;
    }
}
