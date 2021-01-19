<?php

namespace STS\UploadServer\Servers;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use STS\UploadServer\Results\AbstractResult;

abstract class AbstractServer
{
    /** @var Request */
    protected $request;

    /** @var array */
    protected $meta = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Controller method used for the generated route. Why not a dedicated controller class?
     * Well, we expect your controller to be _very_ lightweight! It should do very little
     * other than handing off to this server class anyway.
     *
     * @return Responsable
     */
    public function handle(): Responsable
    {
        return $this->receive()->handleMove();
    }

    /**
     * Kicks off the process of receiving a file upload request
     *
     * @param null  $key
     * @param array $meta
     *
     * @return AbstractResult
     */
    abstract public function receive($key = null, $meta = []): AbstractResult;

    /**
     * This should generated the route for the above handle method. Optional
     * array of options allows the users to specify which functionality should be
     * enabled or disabled.
     *
     * @param array $options
     *
     * @return Route
     */
    abstract public static function route($options = []): Route;

    public function setMeta($meta = [])
    {
        $this->meta = array_merge($this->meta, $meta);

        return $this;
    }

    protected function newFileId(): string
    {
        return Str::uuid();
    }

    public function diskName()
    {
        return config('upload-server.temporary_files_disk');
    }

    public function disk()
    {
        return Storage::disk($this->diskName());
    }

    public function path($fileId = null)
    {
        return rtrim(config('upload-server.temporary_files_path') . "/" . $fileId, '/');
    }

    public function chunkPath($fileId)
    {
        return $this->path() . "/chunks/" . $fileId . ".part";
    }

    public function fullChunkPath($fileId)
    {
        return $this->disk()->getAdapter()->applyPathPrefix($this->chunkPath($fileId));
    }

    public function fullPath($fileId)
    {
        return $this->disk()->getAdapter()->applyPathPrefix(
            Arr::first($this->disk()->files($this->path($fileId)))
        );
    }

    public function initializeChunkFile($fileId)
    {
        if ($this->disk()->exists($this->chunkPath($fileId))) {
            $this->disk()->delete($this->chunkPath($fileId));
        }

        $this->disk()->put($this->chunkPath($fileId), '');

        return $this->chunkPath($fileId);
    }
}
