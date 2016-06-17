<?php

namespace Bluora\LarvelModelTraits;

trait AdvancedFilterTrait
{

    /**
     * Return the delcared attributes on this model.
     * 
     * @return array
     */
    public function scopeGetAttributeFilters()
    {
        if (isset($this->attribute_filters) && is_array($this->attribute_filters)) {
            return $this->attribute_filters;
        }
        return [];
    }

    /**
     * Applies the filters to the model.
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApplyAttributeFilters($query, $search_options)
    {
        // Check the model has the `attribute_filters` variable and it's an array
        if (isset($this->attribute_filters) && is_array($this->attribute_filters)) {

            // Interate over each filter
            foreach ($this->attribute_filters as $filter_name => $filter_settings) {

                // Only contiue if these keys exist
                if (isset($filter_settings['name']) && isset($filter_settings['attribute']) && isset($filter_settings['filter'])) {

                    // Check the search request
                    if (isset($search_options[$filter_name]) && !empty($search_options[$filter_name])) {

                        // Search request exists, so let's add it to the query
                        foreach ($search_options[$filter_name] as list($value, $options)) {

                            // No options provided, use the model default, or equals.
                            if (empty($options) && isset($$this->default_filter_options)) {
                                $options = $this->default_filter_options;
                            } elseif (empty($options)) {
                                $options = '=';
                            }

                            // Select filter for this attribute
                            switch ($filter_settings['filter']) {
                                case 'string':
                                    $query = self::applyStringFilter($query, $filter_settings, $value, $options);
                                    break;
                                case 'number':
                                    $query = self::applyNumberFilter($query, $filter_settings, $value, $options);
                                    break;
                                case 'datetime':
                                    // @todo
                                    break;
                            }
                        }
                    }
                }
            }

        }
        return $query;
    }

    /**
     * Apply the string filter.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filter_settings
     * @param array $options
     * @param string $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyStringFilter($query, $filter_settings, $value, $options)
    {   
        if ($this->validateStringFilters($value, $options)) {
            if (is_array($filter_settings['attribute'])) {  
                $query->where(function($sub_query) use ($filter_settings, $value, $options) {
                    return $this->applyFilterAttributeArray($sub_query, $filter_settings['attribute'], $value, $options);
                });
            } else {
                $query->where($filter_settings['attribute'], $value, $options);
            }
        }
        return $query;
    }

    /**
     * Validate the provided string filter option.
     *
     * @param  string &$value
     * @param  string &$options
     * @return boolean
     */
    private function validateStringFilters(&$value, &$options)
    {
        switch ($options) {
            case '=':
            case '!=':
                return true;
            case '*=*':
            case '*!=*':
                $value = '%'.$value.'%';
                $options = (stripos($options, '!') !== false) ? 'NOT ' : '';
                $options .= 'LIKE';
                return true;
            case '*=':
            case '*!=':
                $value = '%'.$value;
                $options = (stripos($options, '!') !== false) ? 'NOT ' : '';
                $options .= 'LIKE';
                return true;
            case '=*':
            case '!=*':
                $value = $value.'%';
                $options = (stripos($options, '!') !== false) ? 'NOT ' : '';
                $options .= 'LIKE';
                return true;
        }
        return false;
    }

    /**
     * Apply the number filter.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filter_settings
     * @param array $options
     * @param string $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyNumberFilter($query, $filter_settings, $value, $options)
    {   
        if ($this->validateNumberFilters($options, $value)) {
            if (is_array($filter_settings['attribute'])) {  
                $query->where(function($sub_query) use ($filter_settings, $value, $options) {
                    return $this->applyFilterAttributeArray($sub_query, $filter_settings['attribute'], $value, $options);
                });
            } else {
                $query->where($filter_settings['attribute'], $value, $options);
            }
        }
        return $query;
    }

    /**
     * Validate the provided number filter option.
     *
     * @param  string &$options
     * @param  string &$value
     * @return boolean
     */
    private function validateNumberFilters(&$value, &$options)
    {
        switch ($options) {
            case '=':
            case '!=':
            case '>':
            case '>=':
            case '<=':
            case '<':
                return true;
        }
        return false;
    }

    /**
     * Apply the filter using multiple attributes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filter_settings
     * @param array $options
     * @param string $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyFilterAttributeArray($query, $attribute_list, $value, $options)
    {
        foreach ($attribute_list as $attribute_name) {
            if (stripos($options, 'NOT') === false && stripos($options, '!') === false) {
                $query->orWhere($attribute_name, $options, $value);
            } else {
                $query->where($attribute_name, $options, $value);
            }
        }
        return $query;
    }
}
