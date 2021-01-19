<?php

namespace STS\UploadServer\Servers;

use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use Pion\Laravel\ChunkUpload\Config\AbstractConfig;
use Pion\Laravel\ChunkUpload\Handler\SingleUploadHandler;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Pion\Laravel\ChunkUpload\Save\ChunkSave;
use Pion\Laravel\ChunkUpload\Storage\ChunkStorage;
use STS\UploadServer\FilePondChunkHandler;
use STS\UploadServer\Exceptions\InvalidUploadException;
use STS\UploadServer\Results\AbstractResult;
use STS\UploadServer\Results\RetryChunkedUpload;
use STS\UploadServer\Results\ChunkedUploadStarting;
use STS\UploadServer\Results\ReceivedChunk;
use STS\UploadServer\Results\FileUploaded;

class FilePondServer extends AbstractServer
{
    public function receive($key = null, $meta = []): AbstractResult
    {
        $this->setMeta($meta);

        if ($this->isStartingChunkedUpload()) {
            return $this->startChunkedUpload();
        }

        if ($this->isRetryingChunkedUpload()) {
            return $this->retryChunkedUpload();
        }

        if ($this->isChunk()) {
            return $this->receiveChunk();
        }

        if ($this->isSingleUpload()) {
            return $this->receiveSingle($key ?: config('upload-server.input_name'));
        }

        throw new InvalidUploadException;
    }

    protected function startChunkedUpload(): ChunkedUploadStarting
    {
        $fileId = $this->newFileId();

        if ($this->disk()->exists($this->chunkPath($fileId))) {
            $this->disk()->delete($this->chunkPath($fileId));
        }

        $this->disk()->put($this->chunkPath($fileId), '');

        return new ChunkedUploadStarting(
            $fileId,
            $this->fullChunkPath($fileId),
            $this->request->header('Upload-Length'),
            $this->meta
        );
    }

    protected function retryChunkedUpload()
    {
        $upload = UploadedFile::findPart($this->request->input('patch'));
        $status = $this->progress($this->request->input('patch'));

        $nextExpectedOffset = Arr::get($status, 'nextOffset');
        $chunkPath = Arr::get($status, 'chunkPath');

        if (filesize($chunkPath) != $nextExpectedOffset || $chunkPath != $upload->path()) {
            $nextExpectedOffset = 0;
            $this->clearProgress($upload->id());
        }

        return new RetryChunkedUpload($upload, $nextExpectedOffset, $this->meta);
    }

    protected function receiveSingle($key): FileUploaded
    {
        $save = (new FileReceiver(
            $this->findFile($key),
            $this->request,
            SingleUploadHandler::class)
        )->receive();

        return new FileUploaded($this->newFileId(), $save, $this->meta);
    }

    public function receiveChunk(): AbstractResult
    {
        $handler = (new FilePondChunkHandler(
            $this->request,
            $this->buildFileFromChunkPayload(),
            AbstractConfig::config()
        ));

        $this->updateProgress(
            $save = $handler
                ->validateChunk(UploadedFile::create($handler->getFileId(), $this->fullChunkPath($handler->getFileId())))
                ->startSaving(ChunkStorage::storage())
        );

        // We always create this so the event is fired
        $result = new ReceivedChunk($save->handler()->getFileId(), $save, $this->meta);

        // But we will return a different result if we're finished
        return $result->isFinished()
            ? new FileUploaded($result->handler()->getFileId(), $save, $this->meta)
            : $result;
    }

    protected function updateProgress(ChunkSave $save)
    {
        if ($save->isFinished()) {
            $this->clearProgress($save->handler()->getFileId());
        }

        Session::put($this->sessionKey($save->handler()->getFileId()), [
            'progress'      => $save->handler()->getPercentageDone(),
            'currentOffset' => $save->handler()->getChunkOffset(),
            'nextOffset'    => $save->handler()->getNextChunkOffset(),
            'chunkPath'     => $save->getChunkFullFilePath()
        ]);
    }

    protected function progress($fileId)
    {
        return (array)Session::get($this->sessionKey($fileId));
    }

    protected function clearProgress($fileId)
    {
        return Session::pull($this->sessionKey($fileId));
    }

    protected function sessionKey($fileId)
    {
        return 'upload-server.' . $fileId;
    }

    public function isStartingChunkedUpload(): bool
    {
        return $this->request->method() == 'POST' && $this->request->hasHeader('Upload-Length');
    }

    public function isRetryingChunkedUpload(): bool
    {
        return $this->request->method() == 'HEAD';
    }

    public function isChunk(): bool
    {
        return $this->request->method() == "PATCH" && $this->request->hasHeader('Upload-Offset');
    }

    public function isSingleUpload(): bool
    {
        return count($this->request->allFiles()) > 0;
    }

    protected function buildFileFromChunkPayload(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), "upload_");
        file_put_contents($path, $this->request->getContent());

        // We need to pretend like we have an UploadedFile, while skipping the normal
        // validation that verifies a file was uploaded properly.
        return new UploadedFile($path,
            $this->request->header('Upload-Name'),
            null,
            \UPLOAD_ERR_OK,
            true
        );
    }

    protected function findFile($key): UploadedFile
    {
        $file = $this->request->hasFile($key)
            ? $this->request->file($key)
            : current($this->request->allFiles());

        if (is_array($file)) {
            $file = current($file);
        }

        return $file;
    }

    public static function route($options = []): Route
    {
        $methods = ['PATCH'];

        if (Arr::get($options, 'allowDelete', true)) {
            $methods[] = 'DELETE';
        }

        if (Arr::get($options, 'supportChunking', true)) {
            array_push($methods, 'POST', 'HEAD', 'PATCH');
        }

        $uri = Arr::get($options, 'uri', 'filepond-server');
        $name = Arr::get($options, 'name', 'filepond-server');

        return resolve('router')
            ->match($methods, $uri, [FilePondServer::class, 'handle'])
            ->name($name);
    }
}
