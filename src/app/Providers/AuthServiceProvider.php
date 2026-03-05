<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Evidence;
use App\Models\WorkReport;
use App\Policies\ClientPolicy;
use App\Policies\EvidencePolicy;
use App\Policies\WorkReportPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * Service Provider para registrar policies de autorización.
 *
 * Centraliza todos los permisos en Policies para mantener controllers limpios.
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        WorkReport::class => WorkReportPolicy::class,
        Evidence::class => EvidencePolicy::class,
        Client::class => ClientPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
