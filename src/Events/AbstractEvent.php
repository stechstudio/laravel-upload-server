<?php

namespace STS\UploadServer\Events;

use Illuminate\Support\Traits\ForwardsCalls;
use STS\UploadServer\Servers\AbstractStep;
use STS\UploadServer\Storage\File;

abstract class AbstractEvent
{
    use ForwardsCalls;

    /** @var File */
    public $file;

    /** @var AbstractStep */
    public AbstractStep $step;

    /** @var array */
    public $meta;

    public function __construct(File $file, AbstractStep $step, $meta = [])
    {
        $this->file = $file;
        $this->step = $step;
        $this->meta = $meta;
    }

    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->step, $method, $parameters);
    }
}
