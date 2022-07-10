<?php

namespace Karpack\Hexagon\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Karpack\Contracts\Hexagon\Services\ResolvesMultipleServices;
use Karpack\Contracts\Hexagon\Services\ServiceResolver;

class ModelServiceResolver implements ServiceResolver
{
    /**
     * Maps a model class to their corresponding service. 
     * 
     * @var array
     */
    protected $services = [];

    /**
     * Registers a service to the given model.
     * 
     * @param \Illuminate\Database\Eloquent\Model|string $model
     * @param string $service
     */
    public function register($model, string $service)
    {
        $model = $this->getClassNameOfModel($model);

        $this->services[$model] = $service;
    }

    /**
     * Returns the class name of the model if it's not already one
     * 
     * @param \Illuminate\Database\Eloquent\Model|string $model
     * @return string
     */
    protected function getClassNameOfModel($model)
    {
        if (!is_string($model)) {
            $model = get_class($model);
        }
        return $model;
    }

    /**
     * Returns the service that is tied to the given model.
     * 
     * @param \Illuminate\Database\Eloquent\Model|string $model
     * @return \Karpack\Hexagon\Services\ModelWrapperService
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function resolve($model)
    {
        $className = $this->getClassNameOfModel($model);

        if (Arr::exists($this->services, $className)) {
            $service = container()->make($this->services[$className]);

            // A single model can resolve multiple service depending on some attributes
            // or conditions of the model. Such services should be mapped to a class that
            // implements `ResolvesMultipleService` contract. We will resolve the service
            // for the $model and return it.
            if ($service instanceof ResolvesMultipleServices) {
                return $service->setModel($model)->resolve();
            }
            return $service;
        }
        throw new BindingResolutionException('No service registered for the given model');
    }
}