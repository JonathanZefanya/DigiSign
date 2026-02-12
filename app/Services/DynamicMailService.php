<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;

class DynamicMailService
{
    /**
     * Configure mail settings from database
     */
    public static function configureMail(): void
    {
        $host = self::getSetting('smtp_host');
        $port = self::getSetting('smtp_port');
        $username = self::getSetting('smtp_username');
        $password = self::getSetting('smtp_password', true);
        $encryption = self::getSetting('smtp_encryption');
        $fromAddress = self::getSetting('smtp_from_address');
        $fromName = self::getSetting('smtp_from_name');

        // Only override if database settings exist
        if ($host && $port && $username) {
            Config::set('mail.mailers.smtp.host', $host);
            Config::set('mail.mailers.smtp.port', $port);
            Config::set('mail.mailers.smtp.username', $username);
            Config::set('mail.mailers.smtp.password', $password);
            Config::set('mail.mailers.smtp.encryption', $encryption === 'none' ? null : $encryption);
            
            if ($fromAddress) {
                Config::set('mail.from.address', $fromAddress);
            }
            
            if ($fromName) {
                Config::set('mail.from.name', $fromName);
            }
        }
    }

    /**
     * Get setting value from database
     */
    private static function getSetting(string $key, bool $encrypted = false): ?string
    {
        $setting = Setting::where('key', $key)->first();
        
        if (!$setting) {
            return null;
        }

        if ($encrypted && $setting->value) {
            try {
                return Crypt::decryptString($setting->value);
            } catch (\Exception $e) {
                return null;
            }
        }

        return $setting->value;
    }

    /**
     * Check if SMTP is configured
     */
    public static function isConfigured(): bool
    {
        return self::getSetting('smtp_host') && 
               self::getSetting('smtp_port') && 
               self::getSetting('smtp_username');
    }
}
