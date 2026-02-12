<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class SmtpSettingsController extends Controller
{
    /**
     * Show SMTP settings form
     */
    public function index()
    {
        $settings = [
            'smtp_host' => $this->getSetting('smtp_host'),
            'smtp_port' => $this->getSetting('smtp_port'),
            'smtp_username' => $this->getSetting('smtp_username'),
            'smtp_password' => $this->getSetting('smtp_password', true),
            'smtp_encryption' => $this->getSetting('smtp_encryption'),
            'smtp_from_address' => $this->getSetting('smtp_from_address'),
            'smtp_from_name' => $this->getSetting('smtp_from_name'),
        ];

        return view('admin.smtp.index', compact('settings'));
    }

    /**
     * Update SMTP settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'smtp_host' => 'required|string',
            'smtp_port' => 'required|integer',
            'smtp_username' => 'required|string',
            'smtp_password' => 'nullable|string',
            'smtp_encryption' => 'required|in:tls,ssl,none',
            'smtp_from_address' => 'required|email',
            'smtp_from_name' => 'required|string',
        ]);

        foreach ($validated as $key => $value) {
            if ($key === 'smtp_password' && empty($value)) {
                continue; // Don't update password if empty
            }

            $this->updateSetting($key, $value, $key === 'smtp_password');
        }

        // Clear config cache
        \Artisan::call('config:clear');

        return back()->with('success', 'SMTP settings updated successfully.');
    }

    /**
     * Test SMTP connection
     */
    public function test(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            \Mail::raw('This is a test email from your Digital Signature application.', function ($message) use ($request) {
                $message->to($request->test_email)
                    ->subject('SMTP Test Email');
            });

            return back()->with('success', 'Test email sent successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    /**
     * Get setting value
     */
    private function getSetting(string $key, bool $encrypted = false)
    {
        $setting = Setting::where('key', $key)->first();
        
        if (!$setting) {
            return '';
        }

        if ($encrypted) {
            try {
                return Crypt::decryptString($setting->value);
            } catch (\Exception $e) {
                return '';
            }
        }

        return $setting->value;
    }

    /**
     * Update or create setting
     */
    private function updateSetting(string $key, string $value, bool $encrypt = false)
    {
        if ($encrypt) {
            $value = Crypt::encryptString($value);
        }

        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
