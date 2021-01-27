<?php

namespace STS\UploadServer\Servers\FilePond;

use Illuminate\Http\Request;
use STS\UploadServer\Events\ChunkReceived;
use STS\UploadServer\Events\UploadComplete;
use STS\UploadServer\Exceptions\InvalidChunkException;
use STS\UploadServer\Servers\AbstractStep;
use STS\UploadServer\Storage\PartialFile;

class ReceiveChunk extends AbstractStep
{
    use PayloadHelper;

    protected $event = ChunkReceived::class;

    public static function handles(Request $request): bool
    {
        return $request->method() == "PATCH"
            && $request->hasHeader('Upload-Offset')
            && $request->has('patch');
    }

    public function handle()
    {
        if(!PartialFile::exists($this->patch()) || PartialFile::find($this->patch())->getSize() != $this->offset()) {
            // We have a bad chunk request
            throw new InvalidChunkException;
        }

        $this->file = PartialFile::find($this->patch())
            ->appendContent($this->request->getContent());
    }

    public function finalize()
    {
        $this->file = $this->file->save($this->clientName());

        event(new UploadComplete($this->file, $this, $this->meta));
    }

    public function response()
    {
        return response()->json([
            'id'       => $this->file->id(),
            'progress' => $this->percentComplete()
        ]);
    }
}
