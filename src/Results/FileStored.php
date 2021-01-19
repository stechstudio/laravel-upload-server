<?php

namespace STS\UploadServer\Results;

use Pion\Laravel\ChunkUpload\Save\AbstractSave;
use STS\UploadServer\Events\UploadStored;
use STS\UploadServer\Upload;

class FileStored extends AbstractResult
{
    /** @var int */
    protected $progress = 100;

    public function __construct(Upload $file, $fileId, AbstractSave $result, $meta = [])
    {
        $this->file = $file;

        parent::__construct($fileId, $result, $meta);
    }

    public function announce()
    {
        event(new UploadStored(
            $this->file,
            $this->handler(),
            $this->meta
        ));
    }

    public function isFinished(): bool
    {
        return true;
    }
}
