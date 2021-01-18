<?php

namespace STS\UploadServer\Events;

use Illuminate\Http\UploadedFile;
use Pion\Laravel\ChunkUpload\Handler\AbstractHandler;

class UploadComplete
{
    /** @var UploadedFile */
    public $file;

    /** @var AbstractHandler */
    public $handler;

    /** @var array */
    public $meta;

    public function __construct(UploadedFile $file, AbstractHandler $handler, $meta = [])
    {
        $this->file = $file;
        $this->handler = $handler;
        $this->meta = $meta;
    }
}
