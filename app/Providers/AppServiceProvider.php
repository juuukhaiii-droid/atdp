<?php

namespace App\Providers;

use Illuminate\Foundation\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Vite $vite): void
    {
        // Hostinger's git-deploy silently drops any folder literally named
        // "build" (treats it as a generated artifact, same as it blanket-
        // blocks /storage/ URLs at the edge). Renamed so it actually deploys.
        $vite->useBuildDirectory('compiled-assets');
    }
}
