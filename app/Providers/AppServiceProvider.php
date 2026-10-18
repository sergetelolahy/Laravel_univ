<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Identity\Repositories\UtilisateurRepository;
use App\Domain\Identity\Services\PasswordHasher;
use App\Domain\Identity\Services\TokenService;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentUtilisateurRepository;
use App\Infrastructure\Security\LaravelPasswordHasher;
use App\Infrastructure\Security\SanctumTokenService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UtilisateurRepository::class, EloquentUtilisateurRepository::class);
        $this->app->bind(PasswordHasher::class, LaravelPasswordHasher::class);
        $this->app->bind(TokenService::class, SanctumTokenService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
