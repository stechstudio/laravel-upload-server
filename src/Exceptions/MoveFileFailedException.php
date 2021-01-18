<?php

namespace STS\UploadServer\Exceptions;

use STS\UploadServer\Results\FileUploaded;

class MoveFileFailedException extends \RuntimeException
{
    public $logLevel = "error";

    /** @var FileUploaded */
    public $upload;

    /** @var string */
    public $disk;

    /** @var string */
    public $path;

    public function __construct(FileUploaded $upload, $disk, $path)
    {
        $this->upload = $upload;
        $this->disk = $disk;
        $this->path = $path;

        parent::__construct("Failed to move uploaded file");
    }
}
