<?php

namespace STS\UploadServer\Steps;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use STS\UploadServer\Events\UploadComplete;
use STS\UploadServer\Storage\File;

class ReceiveSingleUpload extends AbstractStep
{
    public static function handles(Request $request): bool
    {
        return count($request->allFiles()) > 0;
    }

    public function handle()
    {
        $this->file = File::storeUploadedFile($this->findFile());

        $this->finished = true;
    }

    public function announce()
    {
        event(new UploadComplete($this->file, $this->meta));
    }

    protected function findFile(): UploadedFile
    {
        $file = current($this->request->allFiles());

        if (is_array($file)) {
            $file = current($file);
        }

        return $file;
    }
}
