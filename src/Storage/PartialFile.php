<?php

namespace STS\UploadServer\Storage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class PartialFile extends File
{
    public function appendFile(UploadedFile $file): PartialFile
    {
        $destination = fopen($this->getRealPath(), 'ab');
        $source = fopen($file->getRealPath(), 'rb');

        while ($buff = fread($source, 4096)) {
            fwrite($destination, $buff);
        }

        fclose($source);
        fclose($destination);

        clearstatcache(true, $this->getRealPath());

        return $this;
    }

    public function appendContent($content): PartialFile
    {
        file_put_contents($this->getRealPath(), $content, FILE_APPEND);
        clearstatcache(true, $this->getRealPath());

        return $this;
    }

    public function save($name): File
    {
        $destination = parent::basePath() . "/" . $this->id() . "/" . $name;

        $this->disk()->move($this->relativePath, $destination);

        return new File($this->id(), $destination);
    }

    public static function exists($id): bool
    {
        return static::disk()->exists(static::relativePathFor($id));
    }

    public static function find($id): PartialFile
    {
        return new static($id, static::relativePathFor($id));
    }

    public static function fromPath($path): PartialFile
    {
        return new static(pathinfo($path, PATHINFO_FILENAME), $path);
    }

    public static function initialize($id = null): PartialFile
    {
        $id = $id ?: static::newId();
        $path = static::relativePathFor($id);

        if (static::disk()->exists($path)) {
            static::disk()->delete($path);
        }

        static::disk()->put($path, '');

        return new static($id, static::relativePathFor($id));
    }

    public static function fileName($fileId): string
    {
        return $fileId . ".part";
    }

    public static function basePath(): string
    {
        return config('upload-server.partials_path');
    }

    public static function relativePathFor($fileId): string
    {
        return static::basePath() . "/" . static::fileName($fileId);
    }

    public static function fullPathFor($fileId): string
    {
        return static::relativeToFullPath(static::relativePathFor($fileId));
    }
}
