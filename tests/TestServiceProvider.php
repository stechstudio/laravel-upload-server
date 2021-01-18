<?php

namespace STS\UploadServer\Tests;

use STS\UploadServer\Servers\FilePondServer;
use STS\UploadServer\UploadServerServiceProvider;

class TestServiceProvider extends UploadServerServiceProvider
{
    public function register()
    {
        parent::register();

        FilePondServer::route();
    }
}
