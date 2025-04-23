<?php

namespace Hypervel\Permission\Providers;

use Hypervel\Support\Facades\Gate;
use Hypervel\Support\ServiceProvider;

class PermissionServiceProvider extends ServiceProvider
{

    public function boot()
    {
        Gate::after(function ($user, string $ability) {
            return $user->hasPermissionTo($ability)
                ? true
                : null;
        });
    }
}