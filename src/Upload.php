<?php

namespace STS\UploadServer;

use Illuminate\Http\UploadedFile;
use STS\UploadServer\Serializers\AbstractSerializer;

class Upload extends UploadedFile
{
    /** @var string */
    protected $fileId;

    public function __construct($fileId, $path)
    {
        $this->fileId = $fileId;

        parent::__construct($path, basename($path), null, 0, true);
    }

    public function id(): string
    {
        return $this->fileId;
    }

    public static function find($fileId): Upload
    {
        return new static($fileId, UploadServerFacade::fullPath($fileId));
    }

    public static function findPart($fileId): Upload
    {
        return new static($fileId, UploadServerFacade::fullChunkPath($fileId));
    }
}
