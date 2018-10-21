<?php

namespace Bluora\LaravelModelTraits;

trait ModelEventsTrait
{
    /**
     * File based model events.
     *
     * @return void
     */
    public static function bootModelEventsTrait()
    {
        $events = [
            'retrieved', 'creating', 'created', 'updating',
            'updated', 'deleting', 'deleted', 'saving',
            'saved', 'restoring', 'restored',
        ];

        foreach ($events as $event_name) {
            $class_name = '\\App\\Events\\'.substr(strrchr(static::class, '\\'), 1);

            if (method_exists(static::class, $event_name)) {
                $event_class = null;

                if (class_exists($class1 = $class_name.'\\'.ucfirst($event_name))) {
                    $event_class = $class1;
                } elseif (class_exists($class2 = $class_name.ucfirst($event_name))) {
                    $event_class = $class2;
                }

                if (!is_null($event_class)) {
                    static::{$event_name}(function ($model) use ($event_class) {
                        event(new $event_class($model));
                    });
                }
            }
        }
    }
}
