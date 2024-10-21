<?php

namespace Linksderisar\FilamentStatePattern;

use Illuminate\Support\ServiceProvider;

class FilamentStatePatternServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        // Register views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'ldi-fsp');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}