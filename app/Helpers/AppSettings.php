<?php

namespace App\Helpers;

use App\Models\Setting;

class AppSettings
{
    /**
     * Get app name from settings or fall back to config.
     */
    public static function appName(): string
    {
        return Setting::get('app_name', config('app.name', 'DigiSign'));
    }

    /**
     * Get app logo path.
     */
    public static function logo(): ?string
    {
        $logo = Setting::get('app_logo');
        return $logo ? asset('storage/' . $logo) : null;
    }

    /**
     * Get app favicon path.
     */
    public static function favicon(): ?string
    {
        $favicon = Setting::get('app_favicon');
        return $favicon ? asset('storage/' . $favicon) : null;
    }

    /**
     * Get app timezone.
     */
    public static function timezone(): string
    {
        return Setting::get('app_timezone', config('app.timezone', 'UTC'));
    }

    /**
     * Get SSO API URL.
     */
    public static function ssoApiUrl(): ?string
    {
        return Setting::get('sso_api_url', env('SSO_API_URL'));
    }

    /**
     * Get SSO API Key.
     */
    public static function ssoApiKey(): ?string
    {
        return Setting::get('sso_api_key', env('SSO_API_KEY'));
    }

    /**
     * Check if SSO is configured.
     */
    public static function isSsoEnabled(): bool
    {
        return !empty(static::ssoApiUrl()) && !empty(static::ssoApiKey());
    }

    /**
     * Check if public registration is enabled.
     */
    public static function isRegistrationEnabled(): bool
    {
        return Setting::get('registration_enabled', '1') === '1';
    }
}
