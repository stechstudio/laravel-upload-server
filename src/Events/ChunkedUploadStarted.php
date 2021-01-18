<?php

namespace STS\UploadServer\Events;

class ChunkedUploadStarted
{
    /** @var string */
    public $id;

    /** @var int */
    public $size;

    /** @var array */
    public $meta;

    public function __construct($id, $size, $meta = [])
    {
        $this->id = $id;
        $this->size = $size;
        $this->meta = $meta;
    }
}
