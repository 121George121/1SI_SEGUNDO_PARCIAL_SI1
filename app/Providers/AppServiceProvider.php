<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Events\ConnectionEstablished;

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
        Event::listen(ConnectionEstablished::class, function (ConnectionEstablished $event) {
            if (!app()->runningInConsole()) {
                try {
                    $event->connection->statement("SET myapp.request_method = '" . request()->method() . "'");
                } catch (\Throwable $e) {
                }
            }
        });
    }
}
