<?php

namespace STS\UploadServer\Storage;

use Illuminate\Http\UploadedFile;

class PartialFile extends File
{
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

    public function save($name)
    {
        $destination = $this->basePath($this->id()) . "/" . $name;

        $this->disk()->move($this->path, $destination);

        return new File($this->id(), $destination);
    }

    public static function find($id): PartialFile
    {
        return new static($id, static::relativePathFor($id));
    }

    public static function initialize(): PartialFile
    {
        $path = static::relativePathFor($id = static::newId());

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

    public static function relativePathFor($fileId): string
    {
        return static::basePath() . "/partials/" . static::fileName($fileId);
    }

    public static function fullPathFor($fileId): string
    {
        return static::relativeToFullPath(static::relativePathFor($fileId));
    }
}