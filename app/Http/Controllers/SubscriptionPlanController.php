<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionPlanController extends Controller
{
    /**
     * Display a listing of subscription plans
     */
    public function index()
    {
        $plans = SubscriptionPlan::withCount('users')->orderBy('price')->get();
        return view('admin.plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new plan
     */
    public function create()
    {
        return view('admin.plans.create');
    }

    /**
     * Store a newly created plan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:subscription_plans',
            'storage_limit_mb' => 'required|integer|min:-1',
            'max_documents_per_month' => 'required|integer|min:-1',
            'max_categories' => 'required|integer|min:-1',
            'price' => 'required|numeric|min:0',
            'is_default' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        // If setting as default, unset other defaults
        if ($request->boolean('is_default')) {
            SubscriptionPlan::where('is_default', true)->update(['is_default' => false]);
        }

        $plan = SubscriptionPlan::create($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Subscription plan created successfully.');
    }

    /**
     * Show the form for editing a plan
     */
    public function edit(SubscriptionPlan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    /**
     * Update the specified plan
     */
    public function update(Request $request, SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:subscription_plans,name,' . $plan->id,
            'storage_limit_mb' => 'required|integer|min:-1',
            'max_documents_per_month' => 'required|integer|min:-1',
            'max_categories' => 'required|integer|min:-1',
            'price' => 'required|numeric|min:0',
            'is_default' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        // If setting as default, unset other defaults
        if ($request->boolean('is_default')) {
            SubscriptionPlan::where('id', '!=', $plan->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $plan->update($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Subscription plan updated successfully.');
    }

    /**
     * Remove the specified plan (with protection for default plan)
     */
    public function destroy(SubscriptionPlan $plan)
    {
        // Protect default plan from deletion
        if ($plan->is_default) {
            return back()->with('error', 'Cannot delete the default system plan.');
        }

        // Check if any users are on this plan
        if ($plan->users()->count() > 0) {
            return back()->with('error', 'Cannot delete plan with active subscriptions. Please reassign users first.');
        }

        $plan->delete();

        return redirect()->route('admin.plans.index')
            ->with('success', 'Subscription plan deleted successfully.');
    }

    /**
     * Show users on this plan
     */
    public function showUsers(SubscriptionPlan $plan)
    {
        $users = $plan->users()->paginate(20);
        return view('admin.plans.users', compact('plan', 'users'));
    }
}
