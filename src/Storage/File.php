<?php

namespace STS\UploadServer\Storage;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class File extends UploadedFile
{
    /** @var string */
    protected $id;

    /** @var string */
    protected $relativePath;

    public function __construct($id, $path, $name = null)
    {
        $this->id = $id;
        $prefix = $this->disk()->getAdapter()->getPathPrefix();
        $this->relativePath = ltrim(str_replace($prefix,'',$path), '\\/');

        parent::__construct(
            Str::start($path, $prefix . "/"),
            $name ?: basename($path),
            null, 0, true
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function getRelativePath()
    {
        return $this->relativePath;
    }

    public static function exists($id): bool
    {
        return count(static::disk()->files(static::relativePathFor($id))) == 1;
    }

    public static function find($id): File
    {
        return new static($id,
            Arr::first(static::disk()->files(static::relativePathFor($id)))
        );
    }

    public static function all(): Collection
    {
        return collect(static::disk()->files(static::basePath()))
            ->map(fn($path) => static::fromPath($path));
    }

    public static function fromPath($path): File
    {
        return new static(0, $path);
    }

    public static function storeUploadedFile(UploadedFile $file)
    {
        $file->storeAs(
            static::relativePathFor($id = static::newId()),
            $file->getClientOriginalName(),
            static::diskName()
        );

        return new static($id, static::relativePathFor($id) . "/" . $file->getClientOriginalName());
    }

    public function delete(): bool
    {
        return $this->disk()->delete($this->relativePath);
    }

    public static function diskName(): string
    {
        return config('upload-server.disk');
    }

    public static function disk(): Filesystem
    {
        return Storage::disk(static::diskName());
    }

    public static function basePath(): string
    {
        return config('upload-server.path');
    }

    public static function relativePathFor($fileId): string
    {
        return rtrim(static::basePath() . "/" . $fileId, '/');
    }

    public static function relativeToFullPath($relativePath): string
    {
        return static::disk()->getAdapter()->applyPathPrefix($relativePath);
    }

    public static function fullPathFor($fileId): string
    {
        return static::relativeToFullPath(
            Arr::first(static::disk()->files(static::relativePathFor($fileId)))
        );
    }

    public static function newId(): string
    {
        return Str::uuid();
    }
}
