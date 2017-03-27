## Convenient way to extend Laravel's validation

Sometimes you may need extend base validation rules which provides by Laravel.

Probably, you do it like this in your Controller, Model or Request class:

```
Validator::extend('your_new_rule', function ($attribute, $value, $parameters, $validator) {
    //..
});
```

If you build large app and it grows up, the best place when you can put validation extension it's **Service Provider**.

You can use for that goal your default `AppServiceProvider` but you must keep in mind, that **code with validation extensions should not be called on the every user's request** to your app. Because in most cases you don't need it in every request.

So let's create the **Service Provider** which initializes only when it's needed.
Laravel mark these Service Providers with `$defer = true` property  and `providers()` method with.

From Laravel docs:
```
To defer the loading of a provider, set the defer property to true and define a provides method.
The provides method should return the service container bindings registered by the provider
```

If you'll explore the Laravel's `ValidationServiceProvider` code you'll discover it's deferred too.
And this is not loading on every user's request. 

### Solution

So, our validation extensions **must not calls on every user's request**. It's

To act this goal and keep code clear and simple you can do the follow:
1. Extend the Laravel's `Illuminate\Validation\ValidationServiceProvider`. 
2. Put your validation extensions code to the `boot` method. This method calls method when all **ServiceProviders** initialized.

To not clog our the Provider with validation extensions logic better put it to separate class.

Finally, your code will reusable, your app's performance not will reduced and you will have the good place for extending/changing custom validation rules it in the future.
 
### Code

File: `app/Validation/ValidationServiceProvider.php`

```php
<?php namespace App\Validation;
    
use Illuminate\Validation\ValidationServiceProvider as BaseValidationServiceProvider;
    
class ValidationServiceProvider extends BaseValidationServiceProvider
{
    public function boot()
    {
        $validator = $this->app['validator'];
    
        (new Validator())->extend($validator);
    }
}
```

File: `app/Validation/Validator.php`

```php
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
        'new_custom_rule',
        //..
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

     public function validateNewCustomRule($value) 
     {
         //..   
     }
     
     //..
}
```

You can copy this code or clone the repository. This repository contains this code in the 'Validation' directory with couple simple validation extensions.

Thanks for reading!



