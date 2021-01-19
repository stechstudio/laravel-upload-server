<?php

namespace STS\UploadServer;

class UploadProgress
{
    public $id;
    public $percent = 0;
    public $currentSize = 0;
    public $expectedSize = 0;
    public $nextOffset = 0;
    public $path;

    public function toArray()
    {
        return [
            'id'           => $this->id,
            'percent'      => $this->percent,
            'currentSize'  => $this->currentSize,
            'expectedSize' => $this->expectedSize,
            'path'         => $this->path
        ];
    }

    public static function initialize($id, $expectedSize, $path)
    {
        $progress = new static;

        $progress->id = $id;
        $progress->expectedSize = $expectedSize;
        $progress->path = $path;

        return $progress;
    }

    public static function restore($payload)
    {
        $progress = new static;

        $progress->id = $payload['id'];
        $progress->percent = $payload['percent'];
        $progress->currentSize = $payload['currentSize'];
        $progress->expectedSize = $payload['expectedSize'];
        $progress->path = $payload['path'];

        return $progress;
    }

    public function update($payload)
    {
        foreach($payload AS $key => $value) {
            if(property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        return $this;
    }
}
