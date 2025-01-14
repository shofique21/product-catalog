<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Validator::extend('password', function ($attribute, $value, $parameters, $validator) {
            // Custom logic to validate password
            return preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $value);
        });
    
        Validator::replacer('password', function ($message, $attribute, $rule, $parameters) {
            return 'The :attribute must contain at least one uppercase letter, one lowercase letter, one number, and one special character.';
        });
    }
}