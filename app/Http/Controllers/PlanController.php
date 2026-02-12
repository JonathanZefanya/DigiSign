<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Show user's current plan information
     */
    public function index()
    {
        $user = auth()->user();
        $currentPlan = $user->subscriptionPlan;
        
        // If no plan assigned, assign default plan
        if (!$currentPlan) {
            $defaultPlan = \App\Models\SubscriptionPlan::where('is_default', true)->first();
            if ($defaultPlan) {
                $user->update(['current_plan_id' => $defaultPlan->id]);
                $currentPlan = $defaultPlan;
            } else {
                // If no default plan exists, show error
                return redirect()->route('documents.index')
                    ->with('error', 'No subscription plans available. Please contact administrator.');
            }
        }
        
        // Calculate usage percentages
        $storageUsagePercentage = $user->getStorageUsagePercentage();
        $documentUsagePercentage = $user->getDocumentUsagePercentage();
        $categoryUsage = $user->categories()->count();
        
        // Get storage in MB
        $storageUsedMb = round($user->storage_used_kb / 1024, 2);
        
        return view('plan.index', compact(
            'user',
            'currentPlan',
            'storageUsagePercentage',
            'documentUsagePercentage',
            'categoryUsage',
            'storageUsedMb'
        ));
    }
}
