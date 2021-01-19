<?php

namespace STS\UploadServer\Results;

use Illuminate\Http\UploadedFile;
use STS\UploadServer\UploadProgress;
use STS\UploadServer\UploadServerFacade;
use STS\UploadServer\Events\UploadComplete;
use STS\UploadServer\Exceptions\MoveFileFailedException;

class FileUploaded extends AbstractResult
{
    protected $percentComplete = 100;

    public function announce()
    {
        event(new UploadComplete(
            $this->file(),
            $this->handler(),
            $this->meta
        ));
    }

    public function handleMove()
    {
        $directory = UploadServerFacade::path($this->fileId);

        if (!UploadServerFacade::disk()->putFileAs($directory, $this->file(), $this->name())) {
            throw new MoveFileFailedException(
                $this, UploadServerFacade::diskName(), $directory . "/" . $this->name()
            );
        }

        return new FileStored(
            UploadedFile::create($this->fileId, UploadServerFacade::fullPath($this->fileId)),
            $this->fileId,
            $this->result,
            $this->meta
        );
    }

    public function name()
    {
        return $this->handler()->isChunkedUpload()
            ? $this->handler()->getFileName()
            : $this->file()->getClientOriginalName();
    }

    public function progress(): UploadProgress
    {
        return UploadProgress::restore([
            'id'           => $this->fileId,
            'percent'      => 100,
            'currentSize'  => $this->file()->getSize(),
            'expectedSize' => $this->file()->getSize(),
            'path'         => $this->file()->getRealPath()
        ]);
    }
}
