<?php

namespace Bluora\LarvelModelTraits;

trait OrderByTrait
{
    /**
     * Provide a standard method for ordering a model.
     *
     * @param Builder instance $query
     * @param string  $field
     * @param string  $direction
     * @return mixed
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

        if (!empty($field)) {
            return $query->orderBy($field, $direction);
        }

        return $query;
    }

}
