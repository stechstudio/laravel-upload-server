<?php

namespace STS\UploadServer\Storage;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    public static function find($id): File
    {
        return new static($id,
            Arr::first(static::disk()->files(static::basePath($id)))
        );
    }

    public static function fromPath($path)
    {
        return new static(0, $path);
    }

    public static function storeUploadedFile(UploadedFile $file)
    {
        $file->storeAs(
            static::basePath($id = static::newId()),
            $file->getClientOriginalName(),
            static::diskName()
        );

        return new static($id, static::basePath($id) . "/" . $file->getClientOriginalName());
    }

    public static function diskName(): string
    {
        return config('upload-server.temporary_files_disk');
    }

    public static function disk(): Filesystem
    {
        return Storage::disk(static::diskName());
    }

    public static function basePath($fileId = null): string
    {
        return rtrim(config('upload-server.temporary_files_path') . "/" . $fileId, '/');
    }

    public static function relativeToFullPath($relativePath): string
    {
        return static::disk()->getAdapter()->applyPathPrefix($relativePath);
    }

    public static function fullPathFor($fileId): string
    {
        return static::relativeToFullPath(
            Arr::first(static::disk()->files(static::basePath($fileId)))
        );
    }

    public static function newId(): string
    {
        return Str::uuid();
    }
}
