<?php

namespace App\Providers;

use GuzzleHttp\Psr7\Query;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB; // Tambahkan ini


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
         if (env('APP_DEBUG')) { // Hanya aktifkan di lingkungan debugging
        DB::listen(function ($query) {
            \Log::info(
                $query->sql,
                $query->bindings,
                $query->time
            );
        });
        }
    }
}
