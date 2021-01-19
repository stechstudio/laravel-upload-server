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

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('filesystems.disks.local.root', env('RUNNER_TEMP', realpath(__DIR__.'/storage')));
    }

    public function patch($uri, array $data = [], array $headers = [], $content = null)
    {
        $server = $this->transformHeadersToServerVars($headers);
        $cookies = $this->prepareCookiesForRequest();

        return $this->call('PATCH', $uri, $data, $cookies, [], $server, $content);
    }
}
