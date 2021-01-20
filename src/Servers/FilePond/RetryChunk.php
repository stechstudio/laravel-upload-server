<?php

namespace STS\UploadServer\Servers\FilePond;

use Illuminate\Http\Request;
use STS\UploadServer\Events\ChunkedUploadRetrying;
use STS\UploadServer\Servers\AbstractStep;
use STS\UploadServer\Storage\PartialFile;

class RetryChunk extends AbstractStep
{
    public static function handles(Request $request): bool
    {
        return $request->method() == 'HEAD'
            && $request->has('patch');
    }

    public function handle()
    {
        $this->file = PartialFile::exists($this->request->input('patch'))
            ? PartialFile::find($this->request->input('patch'))
            : PartialFile::initialize($this->request->input('patch'));
    }

    public function announce()
    {
        event(new ChunkedUploadRetrying($this->file, $this->meta));
    }

    public function response()
    {
        return response('', 200, ['Upload-Offset' => $this->file->getSize()]);
    }
}
