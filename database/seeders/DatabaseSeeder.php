<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed subscription plans first (needed for user creation)
        $this->call(SubscriptionPlanSeeder::class);

        // Create Admin User
        User::updateOrCreate(
            ['email' => 'admin@digisign.local'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // Create Demo User
        User::updateOrCreate(
            ['email' => 'user@digisign.local'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
                'role' => 'user',
                'is_active' => true,
            ]
        );

        // Default Settings
        $defaults = [
            'app_name' => 'DigiSign',
            'app_timezone' => 'UTC',
            'registration_enabled' => '1',
        ];

        foreach ($defaults as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // Default Categories (for documents)
        $categories = [
            ['name' => 'Contract', 'color' => '#0d9488', 'description' => 'Contract documents'],
            ['name' => 'Invoice', 'color' => '#7c3aed', 'description' => 'Invoice and billing documents'],
            ['name' => 'Letter', 'color' => '#d97706', 'description' => 'Official letters and correspondence'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
