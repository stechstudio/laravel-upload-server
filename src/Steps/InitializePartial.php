<?php

namespace STS\UploadServer\Steps;

use Illuminate\Http\Request;
use STS\UploadServer\Events\ChunkedUploadStarted;
use STS\UploadServer\Storage\PartialFile;

class InitializePartial extends AbstractStep
{
    public static function handles(Request $request): bool
    {
        return $request->method() == 'POST' && $request->hasHeader('Upload-Length');
    }

    public function handle()
    {
        $this->file = PartialFile::initialize();
    }

    public function announce()
    {
        event(new ChunkedUploadStarted($this->file, $this->meta));
    }

    public function expectedSize()
    {
        return $this->request->header('Upload-Length');
    }
}
