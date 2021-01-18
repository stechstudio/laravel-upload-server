<?php

namespace STS\UploadServer\Tests;

use Pion\Laravel\ChunkUpload\Providers\ChunkUploadServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{

    protected function getPackageProviders($app)
    {
        return [
            ChunkUploadServiceProvider::class,
            TestServiceProvider::class
        ];
    }

    public function patch($uri, array $data = [], array $headers = [], $content = null)
    {
        $server = $this->transformHeadersToServerVars($headers);
        $cookies = $this->prepareCookiesForRequest();

        return $this->call('PATCH', $uri, $data, $cookies, [], $server, $content);
    }
}
