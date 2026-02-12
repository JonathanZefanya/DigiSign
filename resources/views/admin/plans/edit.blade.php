@extends('layouts.app')

@section('title', 'Edit Subscription Plan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 ds-animate">
    <div>
        <h1 class="fw-bold mb-1" style="font-size: 1.75rem;">
            <i class="bi bi-pencil text-primary me-2"></i>Edit Plan: {{ $plan->name }}
        </h1>
        <p class="text-muted mb-0">Update subscription plan details</p>
    </div>
    <a href="{{ route('admin.plans.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Plans
    </a>
</div>

<div class="ds-card ds-animate ds-animate-delay-1">
    <div class="card-body">
        <form action="{{ route('admin.plans.update', $plan) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold">Plan Name *</label>
                <input type="text" name="name" value="{{ old('name', $plan->name) }}" required
                       class="form-control @error('name') is-invalid @enderror">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Storage Limit (MB) *</label>
                    <input type="number" name="storage_limit_mb" value="{{ old('storage_limit_mb', $plan->storage_limit_mb) }}" required min="-1"
                           class="form-control @error('storage_limit_mb') is-invalid @enderror">
                    <div class="form-text">Enter -1 for unlimited</div>
                    @error('storage_limit_mb')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Max Documents/Month *</label>
                    <input type="number" name="max_documents_per_month" value="{{ old('max_documents_per_month', $plan->max_documents_per_month) }}" required min="-1"
                           class="form-control @error('max_documents_per_month') is-invalid @enderror">
                    <div class="form-text">Enter -1 for unlimited</div>
                    @error('max_documents_per_month')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Max Categories *</label>
                    <input type="number" name="max_categories" value="{{ old('max_categories', $plan->max_categories) }}" required min="-1"
                           class="form-control @error('max_categories') is-invalid @enderror">
                    <div class="form-text">Enter -1 for unlimited</div>
                    @error('max_categories')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Price ({{ \App\Helpers\AppSettings::currencySymbol() }}) *</label>
                    <div class="input-group">
                        <span class="input-group-text">{{ \App\Helpers\AppSettings::currencySymbol() }}</span>
                        <input type="number" name="price" value="{{ old('price', $plan->price) }}" required min="0" step="0.01"
                               class="form-control @error('price') is-invalid @enderror">
                    </div>
                    <div class="form-text">Enter price in {{ \App\Helpers\AppSettings::currencySymbol() }}. For free plans, enter 0.</div>
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" rows="3" class="form-control">{{ old('description', $plan->description) }}</textarea>
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input type="checkbox" name="is_default" value="1" {{ old('is_default', $plan->is_default) ? 'checked' : '' }}
                           class="form-check-input" id="isDefault">
                    <label class="form-check-label" for="isDefault">
                        Set as Default Plan (for new users)
                    </label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="bi bi-check-circle"></i> Update Plan
                </button>
                <a href="{{ route('admin.plans.index') }}" class="btn btn-outline-secondary flex-fill">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
