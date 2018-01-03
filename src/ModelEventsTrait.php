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

        $events = [
            'retrieved', 'creating', 'created', 'updating',
            'updated', 'deleting', 'deleted', 'saving',
            'saved', 'restoring', 'restored',
        ];

        foreach ($events as $event_name) {
            if (method_exists(__CLASS__, $event_name)) {
                static::{$event_name}(function ($model) {
                    $class_name = '\\App\\Events\\'.substr(strrchr(get_class($model), '\\'), 1);
                    if (class_exists($class = $class_name.'\\Created')) {
                        event(new $class($model));
                    } elseif (class_exists($class = $class_name.'Created')) {
                        event(new $class($model));
                    }
                });
            }
        }
    }
}
