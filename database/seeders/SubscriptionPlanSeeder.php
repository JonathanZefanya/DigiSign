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

        $this->command->info('Subscription plans seeded successfully!');
    }
}
