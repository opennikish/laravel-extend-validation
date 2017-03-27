<?php

namespace App\Validation;

use Illuminate\Validation\ValidationServiceProvider as BaseValidationServiceProvider;

class ValidationServiceProvider extends BaseValidationServiceProvider
{
    /**
     * Calls validation extension service to extend all new custom validation rules.
     * See that class for more details
     *
     */
    public function boot()
    {
        $validator = $this->app['validator'];

        (new Validator())->extend($validator);
    }
}
