<?php

namespace STS\UploadServer;

use Illuminate\Support\Facades\Config;
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

            // Pass through some of our configs onto the chunk-upload package
            Config::set([
                'chunk-upload.storage.disk' => Config::get('upload-server.temporary_files_disk'),
                'chunk-upload.storage.chunks' => Config::get('upload-server.temporary_files_path') . "/chunks"
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/upload-server.php', 'upload-server');
    }
}
