@extends('layouts.app')

@section('title', 'Subscription Plans')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 ds-animate">
    <div>
        <h1 class="fw-bold mb-1" style="font-size: 1.75rem;">
            <i class="bi bi-bookmark-star text-primary me-2"></i>Subscription Plans
        </h1>
        <p class="text-muted mb-0">Manage subscription plans and pricing</p>
    </div>
    <a href="{{ route('admin.plans.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> New Plan
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show ds-animate" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show ds-animate" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    @foreach($plans as $plan)
    <div class="col-md-6 col-lg-4 ds-animate" style="animation-delay: {{ $loop->index * 0.1 }}s">
        <div class="ds-card h-100 {{ $plan->is_default ? 'border-primary border-2' : '' }}">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h3 class="h4 fw-bold mb-1">{{ $plan->name }}</h3>
                        @if($plan->is_default)
                            <span class="badge bg-primary-subtle text-primary">
                                <i class="bi bi-star-fill me-1"></i>Default Plan
                            </span>
                        @endif
                    </div>
                    <div class="text-end">
                        @if(\App\Helpers\AppSettings::isPricingEnabled())
                        <div class="h2 fw-bold text-primary mb-0">
                            {{ \App\Helpers\AppSettings::formatPrice($plan->price) }}
                        </div>
                        <small class="text-muted">per month</small>
                        @else
                        <div class="text-muted small">
                            <i class="bi bi-eye-slash"></i>
                            <br>Pricing Hidden
                        </div>
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-hdd text-primary me-2"></i>
                        <span class="text-muted">Storage:</span>
                        <strong class="ms-auto">{{ $plan->storage_limit_mb == -1 ? 'Unlimited' : $plan->storage_limit_mb . ' MB' }}</strong>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-file-earmark-text text-success me-2"></i>
                        <span class="text-muted">Documents:</span>
                        <strong class="ms-auto">{{ $plan->max_documents_per_month == -1 ? 'Unlimited' : $plan->max_documents_per_month . '/month' }}</strong>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-folder text-warning me-2"></i>
                        <span class="text-muted">Categories:</span>
                        <strong class="ms-auto">{{ $plan->max_categories == -1 ? 'Unlimited' : $plan->max_categories }}</strong>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-people text-info me-2"></i>
                        <span class="text-muted">Users:</span>
                        <strong class="ms-auto">{{ $plan->users_count }}</strong>
                    </div>
                </div>

                @if($plan->description)
                <p class="text-muted small mb-3">{{ $plan->description }}</p>
                @endif

                <div class="mt-auto">
                    <div class="btn-group w-100" role="group">
                        <a href="{{ route('admin.plans.edit', $plan) }}" 
                           class="btn btn-outline-secondary">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        
                        @if(!$plan->is_default)
                        <button type="button" 
                                class="btn btn-outline-danger"
                                onclick="if(confirm('Delete this plan? Users will need to be reassigned.')) document.getElementById('delete-plan-{{ $plan->id }}').submit();">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                        <form id="delete-plan-{{ $plan->id }}" 
                              action="{{ route('admin.plans.destroy', $plan) }}" 
                              method="POST" class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>
                        @else
                        <button type="button" class="btn btn-outline-secondary" disabled>
                            <i class="bi bi-shield-lock"></i> Protected
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

@if($plans->isEmpty())
<div class="ds-card text-center py-5 ds-animate">
    <i class="bi bi-inbox display-1 text-muted"></i>
    <h4 class="mt-3">No Plans Found</h4>
    <p class="text-muted">Create your first subscription plan to get started.</p>
    <a href="{{ route('admin.plans.create') }}" class="btn btn-primary mt-2">
        <i class="bi bi-plus-circle"></i> Create Plan
    </a>
</div>
@endif
@endsection
