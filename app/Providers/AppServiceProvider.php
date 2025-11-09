<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use App\Observers\LibroDiarioObserver;
use App\Models\LibroDiario;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        
    }

     public function boot(): void
    {
        setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain', 'es_PE', 'es_ES'); 
        Carbon::setLocale('es');
        CarbonImmutable::setLocale('es');
        CarbonInterval::setLocale('es');
        CarbonPeriod::setLocale('es');

        LibroDiario::observe(LibroDiarioObserver::class);
    }
}
