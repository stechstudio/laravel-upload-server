<?php

namespace STS\UploadServer\Results;

use STS\UploadServer\Events\ChunkReceived;
use Symfony\Component\HttpFoundation\Response;

class ReceivedChunk extends AbstractResult
{
    public function response(): Response
    {
        return response()->json([
            'id'       => $this->fileId,
            'progress' => $this->handler()->getPercentageDone()
        ]);
    }

    public function progress(): int
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
}
