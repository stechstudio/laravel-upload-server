<?php

namespace STS\UploadServer\Serializers;

abstract class AbstractSerializer
{


    abstract public function serialize($fileId, $path): string;

    abstract public function unserialize($serialized): array;
}
