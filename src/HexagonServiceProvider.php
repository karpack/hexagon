<?php

namespace Karpack\Hexagon;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Karpack\Contracts\Hexagon\Services\ServiceResolver;
use Karpack\Hexagon\Services\ModelServiceResolver;

class HexagonServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the model service resolver.
     * 
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ServiceResolver::class, function ($app) {
            return new ModelServiceResolver();
        });
    }

    /**
     * Bootstrap this services.
     *
     * @return void
     */
    public function boot()
    {
        // Register all the models and their corresponding service resolver
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [ServiceResolver::class];
    }
}