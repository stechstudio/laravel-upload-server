<?php

namespace STS\UploadServer\Events;

class ChunkedUploadStarted
{
    /** @var string */
    public $id;

    /** @var array */
    public $meta;

    public function __construct($id, $meta = [])
    {
        $this->id = $id;
        $this->meta = $meta;
    }
}
