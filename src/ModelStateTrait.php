<?php

namespace Bluora\LaravelModelTraits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletingScope;

trait ModelStateTrait
{

    public function getStateCreatedAtColumn()
    {
        return $this->table.'.created_at';
    }

    public function getStateUpdatedAtColumn()
    {
        return $this->table.'.updated_at';
    }

    public function getStateArchivedAtColumn()
    {
        return $this->table.'.archived_at';
    }

    public function getStateDeletedAtColumn()
    {
        return $this->table.'.deleted_at';
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
        $query = $query->withoutGlobalScope(SoftDeletingScope::class);
        switch ($mode) {
            case static::$mode_archived:
                $query = $query->archived();
                break;
            case static::$mode_deleted:
                $query = $query->deleted();
                break;
            case static::$mode_active:
            default:
                $query = $query->active();
                break;
        }
        return $query;
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
            $query = $query->whereNull(static::getStateDeletedAtColumn());
        }
        if (static::getStateArchivedAtColumn()) {
            if ($type === true) {
                $query = $query->whereNotNull(static::getStateArchivedAtColumn());
            } else {
                $query = $query->whereNull(static::getStateArchivedAtColumn());
            }
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
                $query = $query->whereNotNull(static::getStateDeletedAtColumn());
            } else {
                $query = $query->whereNull(static::getStateDeletedAtColumn());
            }
        }
        return $query;
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

}
