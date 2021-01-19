<?php

namespace STS\UploadServer;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\ServiceProvider;

class UploadServerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/upload-server.php' => config_path('upload-server.php'),
            ], 'config');

            $this->passThroughConfig();

            UploadedFile::mixin(new UploadedFileMixin());
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/upload-server.php', 'upload-server');
    }

    /**
     * Pass through some of our configs onto the chunk-upload package
     */
    protected function passThroughConfig()
    {
        $this->app['config']->set([
            'chunk-upload.storage.disk' => $this->app['config']->get('upload-server.temporary_files_disk'),
            'chunk-upload.storage.chunks' => $this->app['config']->get('upload-server.temporary_files_path') . "/chunks"
        ]);
    }
}
