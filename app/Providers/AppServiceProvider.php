<?php

namespace App\Providers;

use App\Services\AssemblyAI;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AssemblyAI::class, function ($app) {
            return new AssemblyAI(
                baseUri: config('services.assemblyai.base_uri'),
                key: config('services.assemblyai.key'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
