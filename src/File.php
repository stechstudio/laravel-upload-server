<?php

namespace STS\UploadServer;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class File extends UploadedFile
{
    protected $id;

    protected $path;

    public function __construct($id, $path, $name = null)
    {
        $this->id = $id;
        $this->path = $path;

        $fullPath = file_exists($path) ? $path : $this->relativeToFullPath($path);

        parent::__construct(
            $fullPath,
            $name ?: basename($path),
            null, 0, true
        );
    }

    public function id()
    {
        return $this->id;
    }

    public function appendFile(UploadedFile $file)
    {
        $destination = fopen($this->getRealPath(), 'ab');
        $source = fopen($file->getRealPath(), 'rb');

        while ($buff = fread($source, 4096)) {
            fwrite($destination, $buff);
        }

        fclose($source);
        fclose($destination);

        return $this;
    }

    public function appendContent($content)
    {
        file_put_contents($this->getRealPath(), $content, FILE_APPEND);

        return $this;
    }

    public static function initializePartial($id): File
    {
        $path = static::partialPath($id);

        if (static::disk()->exists($path)) {
            static::disk()->delete($path);
        }

        static::disk()->put($path, '');

        return new static($id, static::partialPath($id));
    }

    public static function findPartial($id)
    {
        return new static($id, static::partialPath($id));
    }

    public static function fromPath($path)
    {
        return new static(0, $path);
    }

    public static function diskName(): string
    {
        return config('upload-server.temporary_files_disk');
    }

    public static function disk(): Filesystem
    {
        return Storage::disk(static::diskName());
    }

    public static function relativePath($fileId = null): string
    {
        return rtrim(config('upload-server.temporary_files_path') . "/" . $fileId, '/');
    }

    public static function relativeToFullPath($relativePath): string
    {
        return static::disk()->getAdapter()->applyPathPrefix($relativePath);
    }

    public static function partialFileName($fileId): string
    {
        return $fileId . ".part";
    }

    public static function partialPath($fileId): string
    {
        return static::relativePath() . "/partials/" . static::partialFileName($fileId);
    }

    public static function fullPartialPath($fileId): string
    {
        return static::relativeToFullPath(static::partialPath($fileId));
    }

    public static function fullPath($fileId): string
    {
        return static::relativeToFullPath(
            Arr::first(static::disk()->files(static::relativePath($fileId)))
        );
    }
}
