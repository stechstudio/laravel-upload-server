<?php

namespace STS\UploadServer\Servers\FilePond;

use Illuminate\Http\Request;
use STS\UploadServer\Events\ChunkedUploadRetrying;
use STS\UploadServer\Servers\AbstractStep;
use STS\UploadServer\Storage\PartialFile;

class RetryChunk extends AbstractStep
{
    use PayloadHelper;

    public static function handles(Request $request): bool
    {
        return $request->method() == 'HEAD'
            && $request->has('patch');
    }

    public function handle()
    {
        $this->file = PartialFile::exists($this->patch())
            ? PartialFile::find($this->patch())
            : PartialFile::initialize($this->patch());
    }

    public function announce()
    {
        event(new ChunkedUploadRetrying($this->file, $this->meta));
    }

    public function percentComplete(): int
    {
        return 0;
    }

    public function response()
    {
        return response('', 200, ['Upload-Offset' => $this->file->getSize()]);
    }
}
