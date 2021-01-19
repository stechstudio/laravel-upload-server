<?php

namespace STS\UploadServer;

class UploadedFileMixin
{
    public function setId()
    {
        return function ($id) {
            $this->id = $id;

            return $this;
        };
    }

    public function id()
    {
        return function () {
            return $this->id;
        };
    }

    public static function create()
    {
        return static function ($id, $path) {
            return (new static($path, basename($path), null, 0, true))->setId($id);
        };
    }

    public static function createPart()
    {
        return static function ($id) {

            return (new static($path, basename($path), null, 0, true))->setId($id);
        };
    }

    public static function find()
    {
        return static function ($id) {
            return static::create($id, UploadServerFacade::fullPath($id));
        };
    }

    public static function findPart()
    {
        return static function ($id) {
            return static::create($id, UploadServerFacade::fullChunkPath($id));
        };
    }
}
