<?php

namespace Karpack\Hexagon\Services;

use RuntimeException;
use Karpack\Contracts\Hexagon\Services\WrapperResolver;
use Karpack\Contracts\Support\Bootable;

abstract class ModelWrapperService extends ModelService implements Bootable, WrapperResolver
{
    /**
     * Anonymous function that returns a new model wrapper object.
     * 
     * @var \callable
     */
    protected $wrapperResolver = null;

    /**
     * The parameter `$wrapperResolver` should be an  anonymous function that returns 
     * the wrapper object.
     * 
     * @param \callable|null $wrapperResolver
     */
    public function __construct(?callable $wrapperResolver = null)
    {
        $this->wrapperResolver = $wrapperResolver;
    }

    /**
     * Boots the model service
     * 
     * @return void
     * @throws \RuntimeException
     */
    public function boot()
    {
        if (!$this->wrapperResolver) {
            throw new RuntimeException('No wrapper resolver registered for the service ' . static::class);
        }
    }

    /**
     * Sets the model wrapper resolver on the service
     * 
     * @param \callable $wrapperResolver
     * @return $this
     */
    public function setWrapperResolver(callable $wrapperResolver)
    {
        $this->wrapperResolver = $wrapperResolver;

        return $this;
    }

    /**
     * Resolves a new wrapper for the given model.
     * 
     * @param \Illuminate\Database\Eloquent\Model
     */
    public function resolveWrapper($model)
    {
        return call_user_func($this->wrapperResolver, $model);
    }
}