<?php

namespace Bluora\LaravelModelTraits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletingScope;

trait ModelStateTrait
{

    public function getStateCreatedAtColumn()
    {
        return 'created_at';
    }

    public function getStateUpdatedAtColumn()
    {
        return 'updated_at';
    }

    public function getStateArchivedAtColumn()
    {
        return 'archived_at';
    }

    public function getStateDeletedAtColumn()
    {
        return 'deleted_at';
    }

    public static $mode_active = '0';
    public static $mode_archived = '1';
    public static $mode_deleted = '2';

    /**
     * Scope a query to only include specific models.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMode($query, $mode = '0')
    {
        $query->withoutGlobalScope(SoftDeletingScope::class);
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
     * Activate this model.
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function activateModel()
    {
        if (static::getStateArchivedAtColumn()) {
            $this->{static::getStateArchivedAtColumn()} = null;
        }
        if (static::getStateDeletedAtColumn()) {
            $this->{static::getStateDeletedAtColumn()} = null;
        }
        if (static::getStateArchivedAtColumn() || static::getStateDeletedAtColumn()) {
            $this->save();
        }
        return $this;
    }

    /**
     * Archive this model.
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function archiveModel()
    {
        if (static::getStateArchivedAtColumn()) {
            $this->{static::getStateArchivedAtColumn()} = Carbon::now()->toDateTimeString();
            $this->save();
        }
        return $this;
    }

    /**
     * Delete this model.
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function deleteModel()
    {
        if (static::getStateDeletedAtColumn()) {
            $this->{static::getStateDeletedAtColumn()} = Carbon::now()->toDateTimeString();
            $this->save();
        }
        return $this;
    }

    /**
     * Is this model active?
     * 
     * @return string
     */
    public function getIsActiveAttribute()
    {
        return (is_null($this->{static::getStateArchivedAtColumn()})
            && is_null($this->{static::getStateDeletedAtColumn()}));
    }

    /**
     * Is this model archived?
     * 
     * @return string
     */
    public function getIsArchivedAttribute()
    {
        return (!is_null($this->{static::getStateArchivedAtColumn()})
            && is_null($this->{static::getStateDeletedAtColumn()}));
    }

    /**
     * Is this model deleted?
     * 
     * @return string
     */
    public function getIsDeletedAttribute()
    {
        return (!is_null($this->{static::getStateDeletedAtColumn()}));
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
        if (static::getStateDeletedAtColumn()) {
            $query->whereNull(static::getStateDeletedAtColumn());
        }
        if (static::getStateArchivedAtColumn()) {
            if ($type === true) {
                return $query->whereNotNull(static::getStateArchivedAtColumn());
            }
            return $query->whereNull(static::getStateArchivedAtColumn());
        }
        return $query;
    }

    /**
     * Scope a query to only include deleted models.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDeleted($query, $type = true)
    {
        if (static::getStateDeletedAtColumn()) {
            if ($type === true) {
                return $query->whereNotNull(static::getStateDeletedAtColumn());
            }
            return $query->whereNull(static::getStateDeletedAtColumn());
        }
        return $query;
    }

}
