<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Free Plan (Default)
        SubscriptionPlan::firstOrCreate(
            ['name' => 'Free Plan'],
            [
                'storage_limit_mb' => 100,
                'max_documents_per_month' => 10,
                'max_categories' => 5,
                'price' => 0.00,
                'is_default' => true,
                'description' => 'Perfect for getting started with digital signatures',
            ]
        );

        // Create Basic Plan
        SubscriptionPlan::firstOrCreate(
            ['name' => 'Basic Plan'],
            [
                'storage_limit_mb' => 500,
                'max_documents_per_month' => 50,
                'max_categories' => 15,
                'price' => 150000,
                'is_default' => false,
                'description' => 'Ideal for small businesses and teams',
            ]
        );

        // Create Professional Plan
        SubscriptionPlan::firstOrCreate(
            ['name' => 'Professional Plan'],
            [
                'storage_limit_mb' => 2048,
                'max_documents_per_month' => 200,
                'max_categories' => 50,
                'price' => 450000,
                'is_default' => false,
                'description' => 'For power users and growing companies',
            ]
        );

        // Create Enterprise Plan
        SubscriptionPlan::firstOrCreate(
            ['name' => 'Enterprise Plan'],
            [
                'storage_limit_mb' => 10240,
                'max_documents_per_month' => 1000,
                'max_categories' => 200,
                'price' => 1500000,
                'is_default' => false,
                'description' => 'Unlimited power for large organizations',
            ]
        );

        $this->command->info('Subscription plans seeded successfully!');
    }
}
