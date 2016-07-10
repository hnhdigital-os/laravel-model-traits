<?php

namespace Bluora\LaravelModelTraits;

use Validator;

trait ModelValidationTrait
{

    /**
     * Create model and allocation values.
     *
     * @param  array  $request_values
     * @return mixed
     */
    public static function createModel($request_values)
    {
        $model = (new static);
        return $model->validateInput($request_values, false);
    }

    /**
     * Validate input.
     *
     * @param  array  $request_values 
     * @return mixed
     */
    public function validateInput($request_values, $existing_model = true)
    {
        $rules = $this->buildValidationArray($existing_model, $request_values);
        $validator = Validator::make($request_values, $rules);

        if ($validator->fails()) {
            return $validator->errors()->all();
        }

        foreach ($rules as $attribute_name => $validation) {
            if (isset($request_values[$attribute_name])) {
                $this->{$attribute_name} = $request_values[$attribute_name];
            }
        }
        return $this;
    }

    /**
     * Build the validation rules for this model.
     *
     * @param  boolean $existing_model
     * @param  array $request_values
     * @return array
     */
    private function buildValidationArray($existing_model, &$request_values)
    {
        $rules = [];
        if (isset($this->attribute_rules)) {
            foreach ($this->attribute_rules as $attribute_name => $available_rules) {
                $rules[$attribute_name] = [];
                $source = ($existing_model) ? 1 : 0;

                // Allocate rule for new or existing model
                (isset($available_rules[$source]) && !empty($available_rules[$source])) ? 
                    $rules[$attribute_name][] = $available_rules[$source] : ($existing_model) ? $rules[$attribute_name][] = 'sometimes' : false;

                // Add the value type
                (isset($available_rules[2]) && !empty($available_rules[2])) ? $rules[$attribute_name][] = $available_rules[2] : false;

                // Stringify the array of rules
                $rules[$attribute_name] = implode('|', $rules[$attribute_name]);

                // Remove where rule is FALSE
                if (isset($available_rules[$source]) && $available_rules[$source] === false) {
                    unset($request_values[$attribute_name]);
                    unset($rules[$attribute_name]);
                } 

                // Cast the provided value
                elseif (isset($request_values[$attribute_name])) {
                    $request_values[$attribute_name] = $this->applyValidationCasting($request_values[$attribute_name], $rules[$attribute_name]);
                }

                // Cast empty value
                elseif (!empty($available_rules[$source]) && !empty($available_rules[2])) {
                    $request_values[$attribute_name] = $this->applyValidationCasting('', $rules[$attribute_name]);
                }
            }
        }
        $request_values = array_only($request_values, array_keys($rules));
        return $rules;
    }

    /**
     * Apply casting if it is present in the rule.
     * 
     * @param  mixed $value
     * @param  string $rules
     * @return mixed
     */
    private function applyValidationCasting($value, $rules)
    {
        if (stripos($rules, 'json') !== false) {
            $value = json_encode($value);
        }

        if (stripos($rules, 'boolean') !== false) {
            $value = (int)$value;
        }

        if (stripos($rules, 'string') !== false) {
            $value = (string)$value;
        }

        return $value;
    }

}
