<?php

namespace STS\UploadServer\Servers\FilePond;

use Illuminate\Http\Request;
use STS\UploadServer\Events\ChunkedUploadRetrying;
use STS\UploadServer\Events\UploadRemoved;
use STS\UploadServer\Servers\AbstractStep;
use STS\UploadServer\Storage\File;
use STS\UploadServer\Storage\PartialFile;

class DeleteFile extends AbstractStep
{
    use PayloadHelper;

    protected $event = UploadRemoved::class;

    public static function handles(Request $request): bool
    {
        return $request->method() == 'DELETE'
            && $request->has('patch')
            && config('upload-server.filepond.allow_delete');
    }

    public function handle()
    {
        $this->file = File::find($this->patch());

        unlink($this->file->getRealPath());
    }

    public function percentComplete(): int
    {
        return 0;
    }
}
