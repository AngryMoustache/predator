<?php

namespace AngryMoustache\Predator\Providers;

use AngryMoustache\Predator\Predator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class PredatorServiceProvider extends ServiceProvider
{
    public function register()
    {
        App::singleton('predator', fn () => new Predator());
    }

    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/predator.php', 'predator');

        $this->publishes([
            __DIR__ . '/../../config/predator.php' => config_path('predator.php'),
        ], 'predator:config');
    }
}
