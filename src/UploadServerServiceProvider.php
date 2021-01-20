<?php

namespace STS\UploadServer;

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
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/upload-server.php', 'upload-server');
    }
}
