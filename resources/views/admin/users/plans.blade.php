@extends('layouts.app')

@section('title', 'User Plans Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">User Plans Management</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left">User</th>
                    <th class="px-6 py-3 text-left">Email</th>
                    <th class="px-6 py-3 text-left">Current Plan</th>
                    <th class="px-6 py-3 text-left">Storage Used</th>
                    <th class="px-6 py-3 text-left">Documents (Month)</th>
                    <th class="px-6 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-6 py-4">{{ $user->name }}</td>
                    <td class="px-6 py-4">{{ $user->email }}</td>
                    <td class="px-6 py-4">
                        @if($user->subscriptionPlan)
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">
                                {{ $user->subscriptionPlan->name }}
                            </span>
                        @else
                            <span class="text-gray-400">No Plan</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        {{ round($user->storage_used_kb / 1024, 2) }} MB
                        @if($user->subscriptionPlan)
                            / {{ $user->subscriptionPlan->storage_limit_mb == -1 ? 'Unlimited' : $user->subscriptionPlan->storage_limit_mb . ' MB' }}
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        {{ $user->documents_count_current_month }}
                        @if($user->subscriptionPlan)
                            / {{ $user->subscriptionPlan->max_documents_per_month == -1 ? 'Unlimited' : $user->subscriptionPlan->max_documents_per_month }}
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <button onclick="openChangePlanModal({{ $user->id }}, '{{ $user->name }}', {{ $user->current_plan_id ?? 'null' }})"
                                class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700 text-sm">
                            Change Plan
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $users->links() }}
    </div>
</div>

<!-- Change Plan Modal -->
<div id="changePlanModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h2 class="text-xl font-bold mb-4">Change Plan for <span id="userName"></span></h2>
        <form id="changePlanForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Select Plan</label>
                <select name="plan_id" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}">
                            {{ $plan->name }} - ${{ $plan->price }} ({{ $plan->storage_limit_mb == -1 ? 'Unlimited' : $plan->storage_limit_mb . 'MB' }}, {{ $plan->max_documents_per_month == -1 ? 'Unlimited' : $plan->max_documents_per_month . ' docs/mo' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex space-x-4">
                <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Update Plan
                </button>
                <button type="button" onclick="closeChangePlanModal()" 
                        class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openChangePlanModal(userId, userName, currentPlanId) {
    document.getElementById('userName').textContent = userName;
    document.getElementById('changePlanForm').action = `/admin/users/${userId}/plan`;
    if (currentPlanId) {
        document.querySelector(`select[name="plan_id"] option[value="${currentPlanId}"]`).selected = true;
    }
    document.getElementById('changePlanModal').classList.remove('hidden');
}

function closeChangePlanModal() {
    document.getElementById('changePlanModal').classList.add('hidden');
}
</script>
@endsection
