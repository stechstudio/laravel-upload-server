<?php

namespace STS\UploadServer\Results;

use Illuminate\Http\UploadedFile;
use Pion\Laravel\ChunkUpload\Save\AbstractSave;
use STS\UploadServer\Events\UploadStored;
use STS\UploadServer\UploadProgress;

class FileStored extends AbstractResult
{
    /** @var int */
    protected $percentComplete = 100;

    public function __construct(UploadedFile $file, $fileId, AbstractSave $result, $meta = [])
    {
        $this->setFile($file);

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

    public function progress(): UploadProgress
    {
        return $this->getProgress($this->fileId)->update([
            'percent' => 100,
            'currentSize' => $this->file()->getSize(),
            'path' => $this->file()->getRealPath()
        ]);
    }
}
