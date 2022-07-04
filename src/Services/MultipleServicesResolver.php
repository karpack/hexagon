<?php

namespace Karpack\Hexagon\Services;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Karpack\Contracts\Hexagon\Services\ResolvesMultipleServices;

abstract class MultipleServicesResolver implements ResolvesMultipleServices
{
      /**
     * The model for which service has to be resolved
     * 
     * @var \Illuminate\Database\Eloquent\Model|string
     */
    protected $model;

    /**
     * The actual service resolver that returns the service for the model.
     * 
     * @return mixed
     */
    protected abstract function resolveService();

    /**
     * Resolve the service corresponding to the given model.
     * 
     * @return mixed
     */
    public function resolve()
    {
        if (!isset($this->model)) {
            throw new Exception('Unable to resolve service - model or model classname is not set.');
        }

        // If the model property is a string classname of a model, or if the resolved 
        // service is `NULL` we'll attempt to return the default service.
        if (is_string($this->model) || is_null($service = $this->resolveService())) {
            return $this->defaultService();
        }

        // If we're here, then the model is not a string and the function `resolveService` 
        // did resolve a service (and not NULL). We'll jut return what was resolved.
        return $service;
    }

    /**
     * Sets the model for which service has to be resolved.
     * 
     * @param \Illuminate\Database\Eloquent\Model|string $model
     * @return static
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * The service that should be returned if nothing was resolved. If no default service 
     * should to be resolved, throw an exception, so that the flow breaks in the calling
     * function.
     * 
     * @return mixed
     * @throws \Throwable
     */
    public function defaultService()
    {
        $model = isset($this->model) ? $this->model : '';        
        $model = is_string($this->model) ? $this->model : get_class($this->model);

        throw new BindingResolutionException('No service registered for the model ' . $model);
    }
}