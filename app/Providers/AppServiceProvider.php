<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
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
    public function boot()
    {
        Relation::morphMap([
            'Single' => \App\Models\Single::class,
            'Consecutive' => \App\Models\Consecutive::class,
            'Multiple' => \App\Models\Multiple::class,
        ]);
    }

}
