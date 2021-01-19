<?php

namespace STS\UploadServer\Results;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\UploadedFile;
use Pion\Laravel\ChunkUpload\Save\AbstractSave;
use STS\UploadServer\Serializers\AbstractSerializer;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractResult implements Responsable
{
    /** @var string */
    protected $fileId;

    /** @var UploadedFile */
    protected $file;

    /** @var array */
    protected $meta;

    /** @var AbstractSave|null */
    protected $result;

    /** @var int */
    protected $progress = 0;

    public function __construct($fileId, AbstractSave $result, $meta = [])
    {
        $this->fileId = $fileId;
        $this->meta = $meta;
        $this->result = $result;

        $this->announce();
    }

    public function progress(): int
    {
        return $this->progress;
    }

    public function file()
    {
        return $this->file ?: $this->result->getFile();
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

    public function whenFinished(\Closure $callable)
    {
        if ($this->isFinished()) {
            $callable($this);
        }
    }

    public function toResponse($request)
    {
        return $this->response();
    }

    public function handleMove()
    {
        return $this;
    }

    public function serialize($fileId, $path): string
    {
        return resolve(AbstractSerializer::class)->serialize($fileId, $path);
    }

    protected function textResponse($text): Response
    {
        return response($text)->header('Content-Type', 'text/plain');
    }

    public function response()
    {
        return $this->textResponse($this->fileId);
    }

    abstract public function announce();
}
