<?php

namespace STS\UploadServer\Serializers;

use Illuminate\Encryption\Encrypter;

class CryptSerializer extends AbstractSerializer
{
    /** @var Encrypter */
    private $crypt;

    public function __construct(Encrypter $crypt)
    {
        $this->crypt = $crypt;
    }

    public function serialize($fileId, $path): string
    {
        return $this->crypt->encryptString(json_encode([
            'id' => $fileId,
            'path' => $path
        ]));
    }

    public function unserialize($serialized): array
    {
        return json_decode($this->crypt->decryptString($serialized), true);
    }
}
