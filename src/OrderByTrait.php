<?php

namespace Bluora\LaravelModelTraits;

use DB;

trait OrderByTrait
{
    /**
     * Provide a standard method for ordering a model.
     *
     * @param Builder $query
     * @param string  $field
     * @param string  $direction
     *
     * @return Builder
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

        if (preg_match('/^([`a-zA-Z$_]*)\.([`a-zA-Z$_ ]*)$/', $field)) {
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
