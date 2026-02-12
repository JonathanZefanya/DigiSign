<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Assign default plan to users without a plan
        $defaultPlan = \App\Models\SubscriptionPlan::where('is_default', true)->first();
        
        if ($defaultPlan) {
            \App\Models\User::whereNull('current_plan_id')
                ->update(['current_plan_id' => $defaultPlan->id]);
        }
    }

    public function down(): void
    {
        // No rollback needed
    }
};
