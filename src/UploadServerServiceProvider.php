<?php

namespace STS\UploadServer;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use STS\UploadServer\Console\CleanupCommand;
use STS\UploadServer\Events\AbstractEvent;
use STS\UploadServer\Events\ChunkedUploadRetrying;
use STS\UploadServer\Events\ChunkedUploadStarted;
use STS\UploadServer\Events\ChunkReceived;
use STS\UploadServer\Events\UploadComplete;
use STS\UploadServer\Events\UploadRemoved;

class UploadServerServiceProvider extends ServiceProvider
{
    protected $events = [
        ChunkedUploadStarted::class,
        ChunkReceived::class,
        ChunkedUploadRetrying::class,
        UploadComplete::class,
        UploadRemoved::class
    ];

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/upload-server.php' => config_path('upload-server.php'),
            ], 'config');
        }

        if ($this->app['config']->get('upload-server.log')) {
            $this->logEvents($this->app['config']->get('upload-server.log_level'));
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/upload-server.php', 'upload-server');

        $this->commands([
            CleanupCommand::class
        ]);
    }

    protected function logEvents($level)
    {
        Event::listen($this->events, function (AbstractEvent $event) use ($level) {
            $message = ucfirst(str_replace('-', ' ',
                Str::kebab((new \ReflectionClass($event))->getShortName())
            ));

            $this->app['log']->log($level, $message, array_merge($event->meta, [
                'id'      => $event->file->id(),
                'percent' => $event->step->percentComplete(),
                'path'    => $event->file->getRealPath()
            ]));
        });
    }
}
