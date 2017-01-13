<?php

namespace Bluora\LaravelModelTraits;

trait ModelEventsTrait
{
    /**
     * Model triggers.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $class = '\\App\\Events\\'.substr(strrchr(get_class($model), '\\'), 1).'Created';
            if (class_exists($class)) {
                event(new $class($model));
            }
        });

        static::updated(function ($model) {
            $class = '\\App\\Events\\'.substr(strrchr(get_class($model), '\\'), 1).'Updated';
            if (class_exists($class)) {
                event(new $class($model));
            }
        });
    }
}
