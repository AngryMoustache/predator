<?php

namespace AngryMoustache\Predator\Facades;

use Illuminate\Support\Facades\Facade;

class Predator extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'predator';
    }
}
