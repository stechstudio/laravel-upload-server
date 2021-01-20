<?php

namespace STS\UploadServer\Servers;

use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use STS\UploadServer\Servers\FilePond\DeleteFile;
use STS\UploadServer\Servers\FilePond\InitializePartial;
use STS\UploadServer\Servers\FilePond\ReceiveChunk;
use STS\UploadServer\Servers\FilePond\ReceiveSingleUpload;
use STS\UploadServer\Servers\FilePond\RetryChunk;

class FilePondServer extends AbstractServer
{
    protected $steps = [
        InitializePartial::class,
        RetryChunk::class,
        ReceiveChunk::class,
        ReceiveSingleUpload::class,
        DeleteFile::class
    ];

    public static function route($options = []): Route
    {
        $methods = ['POST', 'DELETE'];

        if (Arr::get($options, 'supportChunking', true)) {
            array_push($methods, 'HEAD', 'PATCH');
        }

        $uri = Arr::get($options, 'uri', 'filepond-server');
        $name = Arr::get($options, 'name', 'filepond-server');

        return app('router')
            ->match($methods, $uri, [FilePondServer::class, 'handle'])
            ->name($name);
    }
}
