<?php

namespace App\Providers;

use App\Services\NutritionApiService;
use Illuminate\Support\ServiceProvider;

/**
 * AppServiceProvider
 * 
 * The main service provider for the Recipe Nutrition Management application.
 * This provider is responsible for registering application services and
 * performing any necessary application bootstrapping.
 * 
 * Service providers are the central place where all Laravel applications
 * are bootstrapped. They tell Laravel how to bind various components
 * into the service container.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     * 
     * This method is used to bind things into the service container.
     * It runs before the boot method and should only be used for
     * registering services, not for using them.
     *
     * @return void
     */
    public function register()
    {
        // Register the NutritionApiService as a singleton
        // Singleton means the same instance will be used throughout the application lifecycle
        // This is important for the API service to maintain state and avoid unnecessary instantiation
        $this->app->singleton(NutritionApiService::class, function ($app) {
            // Create and return a new instance of the NutritionApiService
            return new NutritionApiService();
        });
    }

    /**
     * Bootstrap any application services.
     * 
     * This method is called after all service providers have been registered.
     * Use this method to register event listeners, middleware, or other
     * application bootstrapping logic.
     *
     * @return void
     */
    public function boot()
    {
        // No bootstrapping logic needed for this simple application
        // This method is left empty but available for future enhancements
    }
}
