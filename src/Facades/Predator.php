<?php

namespace AngryMoustache\Predator\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \GuzzleHttp\Client\Client token(bool $force = false)
 * @method static object store(object $item, ?string $type = null)
 * @method static object truncate($type)
 * @method static object filter($type, $filters = [], $weights = [], $orderBy = [], $fields = [])
 * @method static \AngryMoustache\Predator\PredatorFilter query($types) Start a new query object to filter results
 * @method static object post($uri, $options = [], $alreadyForced = false)
 */
class Predator extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'predator';
    }
}
