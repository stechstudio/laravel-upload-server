<?php

namespace STS\UploadServer;

use Illuminate\Support\Facades\Facade;

class UploadServerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return UploadServer::class;
    }
}
