<?php

namespace App\Validation;

class Validator
{
    /**
     * New custom rules.
     * Convention: 'new_custom_rule' must have 'validateNewCustomRule' method name in that class.
     *
     * @var array
     */
    protected $rules = [
        'has_lowercase',
        'has_uppercase',
        'has_numeric',
        //'new_custom_rule'
    ];

    public function extend($validator)
    {
        foreach ($this->rules as $newRule) {
            $this->extendRule($validator, $newRule);
        }
    }

    protected function extendRule($validator, string $rule)
    {
        $method = $this->normalizeMethod($rule);

        if (! method_exists($this, $method)) {
            throw new \BadMethodCallException("Method [{$method}] does not exist.");
        }

        $validator->extend($rule, function ($attribute, $value, $parameters, $validator) use ($method) {
            return $this->$method($value, $attribute, $parameters, $validator);
        });
    }

    /**
     * Converts snake_case to StudlyCaps and adds "validate" prefix as first part.
     * Example: 'is_bool' will be 'validateIsBool'.
     *
     * @param string $rule
     * @return string
     */
    protected function normalizeMethod(string $rule)
    {
        return 'validate' . str_replace('_', '', ucwords($rule, '_'));
    }

    public function validateHasLowercase($value)
    {
        return preg_match("/[a-z]/", $value);
    }

    public function validateHasUppercase($value)
    {
        return preg_match("/[A-Z]/", $value);
    }

    public function validateHasNumeric($value)
    {
        return preg_match("/[0-9]/", $value);
    }

    // public function validateNewCustomRule($value, ...$parameters) {}
}