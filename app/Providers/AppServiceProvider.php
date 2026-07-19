<?php

namespace App\Providers;

use App\Domains\Certifications\Models\Certification;
use App\Domains\Certifications\Policies\CertificationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::policy(Certification::class, CertificationPolicy::class);
    }
}
