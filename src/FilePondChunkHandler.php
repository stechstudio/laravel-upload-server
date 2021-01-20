<?php

namespace STS\UploadServer;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Pion\Laravel\ChunkUpload\Save\AbstractSave;
use Pion\Laravel\ChunkUpload\Save\ChunkSave;
use Pion\Laravel\ChunkUpload\Storage\ChunkStorage;
use Pion\Laravel\ChunkUpload\Handler\AbstractHandler;
use STS\UploadServer\Exceptions\InvalidChunkException;
use STS\UploadServer\Save\ChunkRetry;
use STS\UploadServer\Save\InitializedChunkFile;
use STS\UploadServer\Storage\File;

class FilePondChunkHandler extends AbstractHandler
{
    const CHUNK_ID_INDEX = 'patch';
    const HEADER_UPLOAD_LENGTH = 'Upload-Length';
    const HEADER_UPLOAD_OFFSET = 'Upload-Offset';
    const HEADER_UPLOAD_NAME = 'Upload-Name';

    /**
     * Checks if the current abstract handler can be used via HandlerFactory.
     *
     * @param Request $request
     *
     * @return bool
     */
    public static function canBeUsedForRequest(Request $request)
    {
        return $request->hasHeader(self::HEADER_UPLOAD_OFFSET);
    }

    /**
     * @return int
     */
    public function getTotalSize()
    {
        return $this->request->header(self::HEADER_UPLOAD_LENGTH);
    }

    /**
     * @return int
     */
    public function getChunkOffset()
    {
        return $this->request->header(self::HEADER_UPLOAD_OFFSET);
    }

    /**
     * @return int
     */
    public function getChunkSize()
    {
        return $this->file->getSize();
    }

    /**
     * @return int
     */
    public function getNextChunkOffset()
    {
        return $this->getChunkOffset() + $this->getChunkSize();
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->request->header(self::HEADER_UPLOAD_NAME);
    }

    /**
     * @return string
     */
    public function getFileId()
    {
        return $this->request->input(self::CHUNK_ID_INDEX);
    }

    /**
     * A chunk is only valid if the incoming offset matches the current size on disk.
     *
     * @param File $existing
     *
     * @return $this
     * @throws InvalidChunkException
     */
    public function validateChunk(UploadedFile $existing)
    {
        if($existing->getSize() != $this->getChunkOffset()) {
            throw new InvalidChunkException();
        }

        return $this;
    }

    public function initializeChunkFile()
    {
        return new InitializedChunkFile($this->file, $this, $this->config);
    }

    /**
     * Creates save instance and starts saving the uploaded file.
     *
     * @param ChunkStorage $chunkStorage the chunk storage
     *
     * @return AbstractSave
     */
    public function startSaving($chunkStorage)
    {
        return new ChunkSave($this->file, $this, $chunkStorage, $this->config);
    }

    public function retryChunk()
    {
        return new ChunkRetry($this->file, $this, $this->config);
    }

    /**
     * Returns the chunk file name for a storing the tmp file.
     *
     * @return string
     */
    public function getChunkFileName()
    {
        return $this->getFileId() . ".part";
    }

    /**
     * Checks if the request has first chunk.
     *
     * @return bool
     */
    public function isFirstChunk()
    {
        return $this->getChunkOffset() == 0;
    }

    /**
     * Checks if the current request has the last chunk.
     *
     * @return bool
     */
    public function isLastChunk()
    {
        return $this->getChunkOffset() + $this->getChunkSize() == $this->getTotalSize();
    }

    /**
     * Checks if the current request is chunked upload.
     *
     * @return bool
     */
    public function isChunkedUpload()
    {
        return true;
    }

    /**
     * Returns the percentage of the upload file.
     *
     * @return int
     */
    public function getPercentageDone()
    {
        return ($this->getChunkOffset() + $this->getChunkSize()) / $this->getTotalSize() * 100;
    }
}
