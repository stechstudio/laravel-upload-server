<?php

namespace STS\UploadServer;

use Illuminate\Http\UploadedFile;
use STS\UploadServer\Serializers\AbstractSerializer;

class Upload extends UploadedFile
{
    /** @var string */
    protected $fileId;

    public function __construct($fileId, $path, $name)
    {
        $this->fileId = $fileId;

        parent::__construct($path, $name, null, 0, true);
    }

    public function id(): string
    {
        return $this->fileId;
    }

    public static function restore($serialized): Upload
    {
        $payload = self::serializer()->unserialize($serialized);

        return new static($payload['id'], $payload['path'], basename($payload['path']));
    }

    public static function serializer()
    {
        return resolve(AbstractSerializer::class);
    }
}
