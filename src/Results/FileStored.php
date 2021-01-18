<?php

namespace STS\UploadServer\Results;

use Pion\Laravel\ChunkUpload\Save\AbstractSave;
use STS\UploadServer\Events\UploadStored;
use STS\UploadServer\Upload;

class FileStored extends AbstractResult
{
    /** @var int */
    protected $progress = 100;

    /** @var Upload */
    protected $finalFile;

    public function __construct(Upload $finalFile, $fileId, AbstractSave $result, $meta = [])
    {
        $this->finalFile = $finalFile;

        parent::__construct($fileId, $result, $meta);
    }

    public function announce()
    {
        event(new UploadStored(
            $this->finalFile,
            $this->result->handler(),
            $this->meta
        ));
    }

    public function response()
    {
        return $this->serializedResponse(
            $this->fileId,
            $this->file()->getRealPath()
        );
    }

    public function isFinished(): bool
    {
        return true;
    }

    public function file()
    {
        return $this->finalFile;
    }
}
