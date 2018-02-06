<?php

namespace Bluora\LaravelModelTraits;

use DB;

trait OrderByTrait
{
    /**
     * Provide a standard method for ordering a model.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $field
     * @param string                                $direction
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrder($query, $field = '', $direction = '')
    {
        if (isset($this->default_order_by) && empty($field)) {
            $field = $this->default_order_by;
        }

        if (isset($this->default_order_direction) && empty($direction)) {
            $direction = $this->default_order_direction;
        }

        if (empty($direction)) {
            $direction = 'asc';
        }

        if (stripos($field, '.') !== false) {
            list($relation_name, $column) = explode('.', $field);

            if (!method_exists($this, $relation_name)) {
                return;
            }

            $relationship = $relation_name;
            app('LaravelModel')->modelJoin($query, $this, $relationship, '=', 'inner');
            $table_name = array_get($relationship, $relation_name.'.table', false);
            $query->orderBy(DB::raw('`'.$table_name.'`.`'.$column.'`'), $direction);

            return;
        }

        if (!empty($field)) {
            $query->orderBy(DB::raw($field), $direction);
        }
    }
}
