<?php

namespace STS\UploadServer\Results;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\UploadedFile;
use Pion\Laravel\ChunkUpload\Save\AbstractSave;
use STS\UploadServer\Results\Concerns\TracksProgressInSession;
use STS\UploadServer\Serializers\AbstractSerializer;
use STS\UploadServer\UploadProgress;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractResult implements Responsable
{
    use TracksProgressInSession;

    /** @var string */
    protected $fileId;

    /** @var UploadedFile */
    protected $file;

    /** @var array */
    protected $meta;

    /** @var AbstractSave|null */
    protected $result;

    /** @var int */
    protected $percentComplete = 0;

    public function __construct($fileId, AbstractSave $result, $meta = [])
    {
        $this->fileId = $fileId;
        $this->meta = $meta;
        $this->result = $result;

        //$this->track();
        $this->announce();
    }

    public function percentComplete(): int
    {
        return $this->percentComplete;
    }

    public function setFile(UploadedFile $file)
    {
        $file->setId($this->fileId);
        $this->file = $file;

        return $this;
    }

    public function file()
    {
        if(!$this->file) {
            $this->setFile($this->result->getFile());
        }
        return $this->file;
    }

    public function handler()
    {
        return $this->result->handler();
    }

    public function id()
    {
        return $this->fileId;
    }

    public function isFinished(): bool
    {
        return $this->result && $this->result->isFinished();
    }

    public function whenFinished(\Closure $callable): AbstractResult
    {
        if ($this->isFinished()) {
            $callable($this->file(), $this);
        }

        return $this;
    }

    public function toResponse($request)
    {
        return $this->response();
    }

    public function handleMove()
    {
        return $this;
    }

    protected function textResponse($text): Response
    {
        return response($text)->header('Content-Type', 'text/plain');
    }

    public function response()
    {
        return $this->textResponse($this->fileId);
    }

    protected function track()
    {
        $this->saveProgress($this->progress());
    }

    abstract public function progress(): UploadProgress;
    abstract public function announce();
}
