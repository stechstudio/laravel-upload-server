<?php

namespace STS\UploadServer\Results\Concerns;

use Illuminate\Support\Facades\Session;
use Pion\Laravel\ChunkUpload\Save\AbstractSave;
use STS\UploadServer\UploadProgress;

/**
 * @mixin AbstractSave
 */
trait TracksProgressInSession
{
    protected function saveProgress(UploadProgress $progress)
    {
        Session::put($this->sessionKey($progress->id), $progress->toArray());
    }

    protected function getProgress($fileId): UploadProgress
    {
        return UploadProgress::restore(
            (array)Session::get($this->sessionKey($fileId))
        );
    }

    protected function clearProgress($fileId)
    {
        Session::forget($this->sessionKey($fileId));
    }

    protected function sessionKey($fileId)
    {
        return 'upload-server.' . $fileId;
    }
}
