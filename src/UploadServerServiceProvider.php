<?php

namespace STS\UploadServer;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use STS\UploadServer\Serializers\AbstractSerializer;
use STS\UploadServer\Serializers\CryptSerializer;

class UploadServerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'upload-server');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'upload-server');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

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

        $this->app->singleton(AbstractSerializer::class, function ($app) {
            return resolve(CryptSerializer::class);
        });
    }
}
