@if(auth()->check() && auth()->user()->subscriptionPlan)
<div class="ds-card mb-4 ds-animate">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-bold">
            <i class="bi bi-box-seam me-2"></i>Your Current Plan
        </span>
        <span class="badge bg-primary">
            {{ auth()->user()->subscriptionPlan->name }}
        </span>
    </div>
    <div class="card-body">
        {{-- Storage Usage --}}
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-semibold small">Storage Usage</span>
                <span class="text-muted small">
                    {{ round(auth()->user()->storage_used_kb / 1024, 2) }} MB / 
                    {{ auth()->user()->subscriptionPlan->storage_limit_mb == -1 ? 'Unlimited' : auth()->user()->subscriptionPlan->storage_limit_mb . ' MB' }}
                </span>
            </div>
            @if(auth()->user()->subscriptionPlan->storage_limit_mb == -1)
            <div class="text-center py-1">
                <span class="badge bg-success"><i class="bi bi-infinity me-1"></i> Unlimited</span>
            </div>
            @else
            <div class="progress" style="height: 10px;">
                <div class="progress-bar bg-primary" role="progressbar" 
                     style="width: {{ min(100, auth()->user()->getStorageUsagePercentage()) }}%"
                     aria-valuenow="{{ auth()->user()->getStorageUsagePercentage() }}" 
                     aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
            @if(auth()->user()->getStorageUsagePercentage() > 80)
                <small class="text-warning mt-1 d-block">
                    <i class="bi bi-exclamation-triangle"></i> You're running low on storage space
                </small>
            @endif
            @endif
        </div>

        {{-- Document Usage --}}
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-semibold small">Documents This Month</span>
                <span class="text-muted small">
                    {{ auth()->user()->documents_count_current_month }} / 
                    {{ auth()->user()->subscriptionPlan->max_documents_per_month == -1 ? 'Unlimited' : auth()->user()->subscriptionPlan->max_documents_per_month }}
                </span>
            </div>
            @if(auth()->user()->subscriptionPlan->max_documents_per_month == -1)
            <div class="text-center py-1">
                <span class="badge bg-success"><i class="bi bi-infinity me-1"></i> Unlimited</span>
            </div>
            @else
            <div class="progress" style="height: 10px;">
                <div class="progress-bar bg-success" role="progressbar" 
                     style="width: {{ min(100, auth()->user()->getDocumentUsagePercentage()) }}%"
                     aria-valuenow="{{ auth()->user()->getDocumentUsagePercentage() }}" 
                     aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
            @if(auth()->user()->getDocumentUsagePercentage() > 80)
                <small class="text-warning mt-1 d-block">
                    <i class="bi bi-exclamation-triangle"></i> You're approaching your monthly document limit
                </small>
            @endif
            @endif
        </div>

        {{-- Categories --}}
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <span class="fw-semibold small">Categories</span>
                <span class="text-muted small">
                    {{ auth()->user()->categories()->count() }} / 
                    {{ auth()->user()->subscriptionPlan->max_categories == -1 ? 'Unlimited' : auth()->user()->subscriptionPlan->max_categories }}
                </span>
            </div>
        </div>

        @if(auth()->user()->subscriptionPlan->price > 0)
        <div class="mt-3 pt-3 border-top">
            <div class="d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Plan Price</span>
                <span class="h5 fw-bold text-primary mb-0">
                    ${{ number_format(auth()->user()->subscriptionPlan->price, 2) }}<small>/month</small>
                </span>
            </div>
        </div>
        @endif

        @if(auth()->user()->hasExceededStorageQuota() || auth()->user()->hasExceededDocumentQuota())
        <div class="mt-3 pt-3 border-top">
            <div class="alert alert-danger mb-0">
                <h6 class="fw-bold mb-2">
                    <i class="bi bi-exclamation-triangle-fill"></i> Quota Exceeded
                </h6>
                <p class="small mb-3">
                    You've reached your plan limits. Please upgrade to continue using the service.
                </p>
                <a href="{{ route('plan.index') }}" class="btn btn-danger btn-sm">
                    <i class="bi bi-arrow-up-circle"></i> View Plan Details
                </a>
            </div>
        </div>
        @endif

        <div class="mt-3 pt-3 border-top text-center">
            <a href="{{ route('plan.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-box-seam me-1"></i> View Full Plan Details
            </a>
        </div>
    </div>
</div>
@endif
