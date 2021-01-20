<?php

namespace STS\UploadServer\Servers;

use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use STS\UploadServer\Steps\InitializePartial;
use STS\UploadServer\Steps\ReceiveChunk;
use STS\UploadServer\Steps\ReceiveSingleUpload;
use STS\UploadServer\Steps\RetryChunk;

class FilePondServer extends AbstractServer
{
    protected $steps = [
        InitializePartial::class,
        RetryChunk::class,
        ReceiveChunk::class,
        ReceiveSingleUpload::class
    ];

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
