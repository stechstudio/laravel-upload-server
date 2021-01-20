<?php

namespace STS\UploadServer;

use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Manager;
use STS\UploadServer\Servers\AbstractServer;
use STS\UploadServer\Servers\FilePondServer;
use STS\UploadServer\Storage\File;

class UploadServer extends Manager
{
    public function server($name = null): AbstractServer
    {
        return $this->driver($name);
    }

    public function getDefaultDriver(): string
    {
        return $this->config->get('upload-server.default');
    }

    public function createFilepondDriver(): FilePondServer
    {
        return app(FilePondServer::class);
    }

    public function retrieve($files)
    {
        $files = array_map(fn($fileId) => File::find($fileId), Arr::wrap($files));

        return count($files) == 1 ? $files[0] : $files;
    }

    /**
     * If you generate the route through this manager class, it probably means you don't know
     * which driver is being used and want to setup a generic endpoint for the default driver.
     * So we're doing to ensure the uri is generic and not driver-specific.
     */
    public function route($options = []): Route
    {
        $options['uri'] = 'upload-server';
        $options['name'] = 'upload-server';

        return $this->driver()->route($options);
    }
}
