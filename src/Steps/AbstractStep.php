<?php

namespace STS\UploadServer\Steps;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use STS\UploadServer\Storage\File;
use STS\UploadServer\Servers\AbstractServer;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractStep implements Responsable
{
    /** @var Request */
    protected $request;

    /** @var AbstractServer */
    protected $server;

    /** @var File */
    protected $file;

    /** @var array */
    protected $meta;

    /** @var bool */
    protected $finished = false;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function run(AbstractServer $server)
    {
        $this->server = $server;
        $this->meta = $server->meta();

        $this->handle();
        $this->announce();

        return $this;
    }

    abstract public static function handles(Request $request): bool;

    abstract public function handle();

    abstract public function announce();

    public function toResponse($request)
    {
        return $this->response();
    }

    public function response()
    {
        return $this->textResponse($this->file->id());
    }

    protected function textResponse($text): Response
    {
        return response($text)->header('Content-Type', 'text/plain');
    }

    public function isFinished(): bool
    {
        return $this->finished;
    }

    public function whenFinished(\Closure $callable): AbstractStep
    {
        if ($this->isFinished()) {
            $callable($this->file, $this);
        }

        return $this;
    }

    public function file(): File
    {
        return $this->file;
    }

    public function meta(): array
    {
        return $this->meta;
    }
}
