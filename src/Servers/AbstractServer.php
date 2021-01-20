<?php

namespace STS\UploadServer\Servers;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use STS\UploadServer\Exceptions\InvalidUploadException;

abstract class AbstractServer
{
    /** @var Request */
    protected $request;

    /** @var array */
    protected $meta = [];

    /** @var array */
    protected $steps = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle($meta = []): Responsable
    {
        $this->setMeta($meta);

        return app($this->findStep())->run($this);
    }

    public function findStep(): string
    {
        foreach ($this->steps as $step) {
            if ($step::handles($this->request)) {
                return $step;
            }
        }

        throw new InvalidUploadException;
    }

    /**
     * This should generated the route for the above handle method. Optional
     * array of options allows the users to specify which functionality should be
     * enabled or disabled.
     *
     * @param array $options
     *
     * @return Route
     */
    abstract public static function route($options = []): Route;

    public function setMeta($meta = [])
    {
        $this->meta = array_merge($this->meta, $meta);

        return $this;
    }

    public function meta(): array
    {
        return $this->meta;
    }
}
