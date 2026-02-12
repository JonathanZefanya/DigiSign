<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Helpers\AppSettings;
use App\Services\DynamicMailService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Configure dynamic SMTP from database
        try {
            DynamicMailService::configureMail();
        } catch (\Exception $e) {
            // Database might not be available during migrations
        }

        // Share app settings with all views
        View::composer('*', function ($view) {
            try {
                $view->with('appName', AppSettings::appName());
                $view->with('appLogo', AppSettings::logo());
                $view->with('appFavicon', AppSettings::favicon());
                $view->with('registrationEnabled', AppSettings::isRegistrationEnabled());
                $view->with('appTimezone', AppSettings::timezone());
                
                // Share pending signatures count for navbar badge
                if (auth()->check()) {
                    $pendingSignaturesCount = \App\Models\Document::whereHas('recipients', function($q) {
                        $q->where('email', auth()->user()->email)
                          ->where('status', 'PENDING')
                          ->where('role', 'SIGNER');
                    })->count();
                    $view->with('pendingSignaturesCount', $pendingSignaturesCount);
                }
            } catch (\Exception $e) {
                $view->with('appName', config('app.name', 'DigiSign'));
                $view->with('appLogo', null);
                $view->with('appFavicon', null);
                $view->with('registrationEnabled', true);
                $view->with('appTimezone', 'UTC');
                $view->with('pendingSignaturesCount', 0);
            }
        });
    }
}
