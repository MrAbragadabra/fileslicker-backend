<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Policies\AdminPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Политики, привязанные к моделям.
     *
     * @var array
     */
    protected $policies = [
        User::class => AdminPolicy::class,
    ];

    /**
     * Регистрация сервисов авторизации.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('is-admin', [AdminPolicy::class, 'isAdmin']);
    }
}
