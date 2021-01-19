<?php

namespace STS\UploadServer\Results;

use Illuminate\Http\UploadedFile;
use Pion\Laravel\ChunkUpload\Save\ChunkSave;
use STS\UploadServer\Events\ChunkReceived;
use STS\UploadServer\UploadProgress;
use Symfony\Component\HttpFoundation\Response;

class ReceivedChunk extends AbstractResult
{
    public function __construct($fileId, ChunkSave $result, $meta = [])
    {
        $this->setFile(UploadedFile::create($fileId, $result->getChunkFullFilePath()));

        parent::__construct($fileId, $result, $meta = []);
    }

    public function response(): Response
    {
        return response()->json([
            'id'       => $this->fileId,
            'progress' => $this->handler()->getPercentageDone()
        ]);
    }

    public function percentComplete(): int
    {
        return $this->handler()->getPercentageDone();
    }

    public function announce()
    {
        event(new ChunkReceived(
            $this->file(),
            $this->handler(),
            $this->meta
        ));
    }

    public function progress(): UploadProgress
    {
        return $this->getProgress($this->fileId)->update([
            'percent' => $this->percentComplete(),
            'currentSize' => $this->file()->getSize()
        ]);
    }
}
