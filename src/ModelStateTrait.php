<?php

namespace Bluora\LarvelModelTraits;

trait ModelStateTrait
{

    /**
     * Scope a query to only include active models.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query, $type = true)
    {
        return $query->archived(!$type)->deleted(false);
    }

    /**
     * Scope a query to only include archived models.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeArchived($query, $type = true)
    {
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
