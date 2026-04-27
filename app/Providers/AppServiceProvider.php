<?php

namespace App\Providers;

use App\Services\Mail\MailSettingsConfigurator;
use App\Settings\MailSettings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Throwable;

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
        $this->configureMailSettings();

        RateLimiter::for('certificate-lookup', function (Request $request): Limit {
            return Limit::perMinute(5)->by($request->ip().'|'.sha1((string) $request->input('nokp')));
        });
    }

    protected function configureMailSettings(): void
    {
        try {
            if (! Schema::hasTable('settings')) {
                return;
            }

            $settings = app(MailSettings::class);
        } catch (Throwable) {
            return;
        }

        app(MailSettingsConfigurator::class)->apply($settings);
    }
}
