<?php

namespace Bluora\LaravelModelTraits;

use Validator;

trait ModelValidationTrait
{
    public static $RULE_SOURCE_NEW = 0;
    public static $RULE_SOURCE_UPDATE = 1;
    public static $RULE_SOURCE_FORMAT = 2;

    /**
     * Create model and allocation values.
     *
     * @param array $request_values
     *
     * @return mixed
     */
    public static function createModel($request_values)
    {
        $model = (new static());

        return $model->validateInput($request_values, false);
    }

    /**
     * Validate input.
     *
     * @param array $request_values
     *
     * @return mixed
     */
    public function validateInput($request_values, $existing_model = true)
    {
        $rules = $this->buildValidationArray($existing_model, $request_values);
        $validator = Validator::make($request_values, $rules);
        if ($validator->fails()) {
            return [true, $validator];
        }

        foreach ($rules as $attribute_name => $validation) {
            if (isset($request_values[$attribute_name])) {
                $this->{$attribute_name} = $request_values[$attribute_name];
            }
        }

        return $this;
    }

    /**
     * Process a new model.
     *
     * @param array $update_data
     * @param Model $model
     * @param array $options
     *
     * @return mixed
     */
    public static function processNew($update_data, $options = [])
    {
        $result = [
            'is_error' => true,
            'feedback' => 'Validation of inputs failed.',
            'toastr'   => 'error',
            'timeout'  => 5000,
            'fields'   => [],
        ];

        if (isset($options['validation'])) {
            if (!$options['validation']($update_data)) {
                return $result;
            }
        }

        $model = static::createModel($update_data);

        if (is_array($model)) {
            return $result;
        }
        $model_class = get_class($model);

        // Validation failed.
        if (is_array($model) && $model[0] == true) {
            $result['fields'] = array_keys($model[1]->errors()->messages());
            $result['feedback'] = implode('; ', $model[1]->errors()->all());
        } else {
            if (isset($options['on_saving']) && $options['on_saving'] instanceof \Closure) {
                $options['on_saving']($model, $update_data);
            }
            $model->save();

            if (($model_id = $model->id) > 0) {
                $model = $model_class::where('id', '=', $model_id)->first();

                if (isset($options['on_created']) && $options['on_created'] instanceof \Closure) {
                    $options['on_created']($model, $update_data);
                }

                $result = [
                    'is_error' => false,
                    'feedback' => 'Created sucessfully.',
                    'toastr'   => 'success',
                    'timeout'  => 2000,
                    'fields'   => [],
                    'model'    => $model,
                    'uuid'     => $model->uuid,
                ];

                if (!empty($options['success_route'])) {
                    header('X-FORCE_FRONTEND_REDIRECT: 1');

                    return route(array_get($options, 'success_route', 'home'), [$model->getTable() => $model->uuid]);
                }
            } else {
                $result['feedback'] = 'Failed to create record.';
            }
        }

        return $result;
    }

    /**
     * Process a save.
     *
     * @param array $update_data
     * @param array $options
     *
     * @return mixed
     */
    public function processSave($update_data, $options = [])
    {
        $result = [
            'is_error' => true,
            'feedback' => 'Validation of inputs failed.',
            'toastr'   => 'error',
            'timeout'  => 5000,
            'fields'   => [],
            'changes'  => [],
        ];

        if (isset($options['validation'])) {
            if (!$options['validation']($update_data)) {
                return $result;
            }
        }

        $model = $this->validateInput($update_data);
        if (is_array($model) && $model[0] == true) {
            $result['fields'] = array_keys($model[1]->errors()->messages());
            $result['feedback'] = implode('; ', $model[1]->errors()->all());
        } else {
            $result = [
                'is_error' => false,
                'feedback' => 'Changes sucessfully made.',
                'toastr'   => 'success',
                'timeout'  => 2000,
                'changes'  => [],
            ];

            if (isset($options['on_saving']) && $options['on_saving'] instanceof \Closure) {
                $options['on_saving']($this, $update_data);
            }

            $dirty_check = (method_exists($this, 'preDirtyCheck')) ? 'preDirtyCheck' : 'getDirty';

            if ($changes = $this->$dirty_check()) {
                $this->save();
                $result['changes'] = $changes;

                if (isset($options['on_saved']) && $options['on_saved'] instanceof \Closure) {
                    $options['on_saved']($model, $update_data);
                }
            } else {
                $result['toastr'] = 'info';
                $result['feedback'] = 'No changes were made.';
            }
        }

        // If normal web request and a route is provided on success
        if (!request()->ajax() && !empty($options['success_route'])) {
            session()->flash('toastr', $result['toastr']);
            session()->flash('timeout', $result['timeout']);
            session()->flash('is_error', $result['is_error']);
            session()->flash('feedback', $result['feedback']);
            session()->flash('fields', $result['fields']);

            if (!isset($options['success_paramaters'])) {
                $options['success_paramaters'] = [];
            }

            return redirect()->route(array_get($options, 'success_route', 'home'), array_get($options, 'success_paramaters', []));
        }

        return $result;
    }

    /**
     * Build the validation rules for this model.
     *
     * @param bool  $existing_model
     * @param array $request_values
     *
     * @return array
     */
    private function buildValidationArray($existing_model, &$request_values)
    {
        $rules = [];
        if (isset($this->attribute_rules)) {
            foreach ($this->attribute_rules as $attribute_name => $available_rules) {
                $rules[$attribute_name] = [];
                $source = ($existing_model) ? static::$RULE_SOURCE_UPDATE : static::$RULE_SOURCE_NEW;
                $source_style = $existing_model;

                if (isset($request_values[$attribute_name]) && empty($request_values[$attribute_name])) {
                    $source = static::$RULE_SOURCE_NEW;
                    $source_style = false;
                }

                // Allocate rule for new or existing model
                if (isset($available_rules[$source]) && !empty($available_rules[$source])) {
                    $rules[$attribute_name][] = $available_rules[$source];
                }

                if ($source_style && $source == static::$RULE_SOURCE_UPDATE) {
                    $rules[$attribute_name][] = 'sometimes';
                }

                // Add the value type
                (isset($available_rules[static::$RULE_SOURCE_FORMAT]) && !empty($available_rules[static::$RULE_SOURCE_FORMAT])) ? $rules[$attribute_name][] = $available_rules[static::$RULE_SOURCE_FORMAT] : false;

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
                elseif (!empty($available_rules[$source]) && !empty($available_rules[static::$RULE_SOURCE_FORMAT])) {
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
     * @param mixed  $value
     * @param string $rules
     *
     * @return mixed
     */
    private function applyValidationCasting($value, $rules)
    {
        if (stripos($rules, 'json') !== false) {
            $value = json_encode($value);
        }

        if (stripos($rules, 'boolean') !== false) {
            $value = (int) $value;
        }

        if (stripos($rules, 'string') !== false) {
            $value = (string) $value;
        }

        return $value;
    }
}
