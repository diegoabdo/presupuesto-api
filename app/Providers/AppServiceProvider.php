<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
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
        // Sin esto, un solo Resource (ej. new SavingsGoalResource($x)) se
        // devuelve como {"data": {...}}, pero el cliente Flutter espera los
        // campos sueltos (igual que los controladores que no usan Resource,
        // como IncomeSourceController). Las colecciones (::collection())
        // siguen envueltas en {"data": [...]} porque DataRepository._asList
        // ya sabe leer ambas formas.
        JsonResource::withoutWrapping();
    }
}
