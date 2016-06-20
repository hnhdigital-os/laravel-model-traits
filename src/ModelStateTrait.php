<?php

namespace Bluora\LaravelModelTraits;

trait ModelStateTrait
{

    public static $mode_active = '0';
    public static $mode_archived = '1';
    public static $mode_deleted = '2';

    /**
     * Scope a query to only include active models.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMode($query, $mode = '0')
    {
        switch ($mode) {
            case static::$mode_archived:
                return $query->archived();
            case static::$mode_deleted:
                return $query->deleted();
            case static::$mode_active:
            default:
                return $query->active();
        }
    }

    /**
     * Scope a query to only include active models.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query, $type = true)
    {
        return $query->archived(!$type);
    }

    /**
     * Scope a query to only include archived models.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeArchived($query, $type = true)
    {
        $query->whereNull('deleted_at');
        if ($type === true) {
            return $query->whereNotNull('archived_at');
        }
        return $query->whereNull('archived_at');
    }

    /**
     * Scope a query to only include deleted models.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDeleted($query, $type = true)
    {
        if ($type === true) {
            return $query->whereNotNull('deleted_at');
        }
        return $query->whereNull('deleted_at');
    }
}
