<?php

namespace STS\UploadServer\Tests;

use Orchestra\Testbench\TestCase;
use STS\UploadServer\UploadServerServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [UploadServerServiceProvider::class];
    }

    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
