<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Configura el locale global de PHP (compatible Windows y Linux)
        setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain', 'es_PE', 'es_ES'); 

        // Configura Carbon en español
        Carbon::setLocale('es');
        CarbonImmutable::setLocale('es');
        CarbonInterval::setLocale('es');
        CarbonPeriod::setLocale('es');
    }
}
