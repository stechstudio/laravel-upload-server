<?php

namespace STS\UploadServer\Servers\FilePond;

use Illuminate\Http\Request;
use STS\UploadServer\Events\ChunkReceived;
use STS\UploadServer\Events\UploadComplete;
use STS\UploadServer\Servers\AbstractStep;
use STS\UploadServer\Storage\PartialFile;

class ReceiveChunk extends AbstractStep
{
    public static function handles(Request $request): bool
    {
        return $request->method() == "PATCH"
            && $request->hasHeader('Upload-Offset')
            && $request->has('patch')
            && PartialFile::exists($request->input('patch'))
            && PartialFile::find($request->input('patch'))->getSize() == $request->header('Upload-Offset');
    }

    public function handle()
    {
        $this->file = PartialFile::find($this->request->input('patch'))
            ->appendContent($this->request->getContent());
    }

    public function announce()
    {
        event(new ChunkReceived($this->file, $this->meta));
    }

    public function finalize()
    {
        $this->file = $this->file->save($this->clientName());

        event(new UploadComplete($this->file, $this->meta));
    }

    public function response()
    {
        return response()->json([
            'id'       => $this->file->id(),
            'progress' => $this->percentComplete()
        ]);
    }

    public function percentComplete()
    {
        return ($this->chunkOffset() + $this->chunkSize()) / $this->expectedSize() * 100;
    }

    public function isFinished(): bool
    {
        return $this->chunkOffset() + $this->chunkSize() == $this->expectedSize();
    }

    public function chunkOffset()
    {
        return $this->request->header('Upload-Offset');
    }

    public function chunkSize()
    {
        return strlen($this->request->getContent());
    }

    public function expectedSize()
    {
        return $this->request->header('Upload-Length');
    }

    public function clientName()
    {
        return $this->request->header('Upload-Name');
    }
}
