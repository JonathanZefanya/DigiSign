<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class UserPlanController extends Controller
{
    /**
     * Display users with their plans
     */
    public function index()
    {
        $users = User::with('subscriptionPlan')->paginate(20);
        $plans = SubscriptionPlan::all();
        return view('admin.users.plans', compact('users', 'plans'));
    }

    /**
     * Update user's subscription plan
     */
    public function updatePlan(Request $request, User $user)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $user->update(['current_plan_id' => $request->plan_id]);

        return back()->with('success', 'User plan updated successfully.');
    }
}
