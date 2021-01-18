<?php

namespace STS\UploadServer;

use Illuminate\Support\Manager;
use STS\UploadServer\Servers\FilePondServer;

class UploadServer extends Manager
{
    public function server($name = null)
    {
        return $this->driver($name);
    }

    public function getDefaultDriver()
    {
        return $this->config->get('upload-server.default');
    }

    public function createFilepondDriver()
    {
        return resolve(FilePondServer::class);
    }
}
