@extends('layouts.app')
@section('title', 'My Plan')

@section('styles')
<style>
    .plan-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
    }

    .plan-badge {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        padding: 8px 20px;
        border-radius: 25px;
        font-weight: 600;
        display: inline-block;
    }

    .usage-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: all 0.3s;
    }

    .usage-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .usage-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .progress-custom {
        height: 12px;
        border-radius: 10px;
        background-color: #e5e7eb;
        overflow: hidden;
    }

    .progress-bar-custom {
        height: 100%;
        border-radius: 10px;
        transition: width 0.6s ease;
    }

    .feature-item {
        padding: 15px;
        background: #f8fafc;
        border-radius: 10px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .feature-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
    }

    .warning-banner {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .danger-banner {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }
</style>
@endsection

@section('content')
<div class="container py-4">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 ds-animate">
        <div>
            <h1 class="fw-bold mb-1" style="font-size: 1.75rem;">
                <i class="bi bi-box-seam text-primary me-2"></i>My Plan & Usage
            </h1>
            <p class="text-muted mb-0">Monitor your subscription and resource usage</p>
        </div>
        <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Documents
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show ds-animate" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Warning Banners --}}
    @if($user->hasExceededStorageQuota() || $user->hasExceededDocumentQuota())
        <div class="danger-banner ds-animate">
            <h5 class="fw-bold mb-2">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>Quota Exceeded!
            </h5>
            <p class="mb-3">You've reached your plan limits. Please upgrade to continue using the service.</p>
            <a href="#" class="btn btn-light btn-lg">
                <i class="bi bi-arrow-up-circle me-2"></i>Upgrade Now
            </a>
        </div>
    @elseif($storageUsagePercentage > 80 || $documentUsagePercentage > 80)
        <div class="warning-banner ds-animate">
            <h5 class="fw-bold mb-2">
                <i class="bi bi-exclamation-circle-fill me-2"></i>Approaching Quota Limit
            </h5>
            <p class="mb-0">You're running low on resources. Consider upgrading your plan soon.</p>
        </div>
    @endif

    <div class="row g-4">
        {{-- Current Plan Card --}}
        <div class="col-lg-4 ds-animate">
            <div class="plan-card">
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <i class="bi bi-award" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="fw-bold mb-2">{{ $currentPlan->name }}</h3>
                    <div class="plan-badge">Active Plan</div>
                </div>

                @if($currentPlan->price > 0)
                    @if(\App\Helpers\AppSettings::isPricingEnabled())
                    <div class="text-center mb-4 pb-4 border-bottom" style="border-color: rgba(255,255,255,0.2) !important;">
                        <div class="stat-value">{{ \App\Helpers\AppSettings::formatPrice($currentPlan->price) }}</div>
                        <div style="opacity: 0.9;">/month</div>
                    </div>
                    @else
                    <div class="text-center mb-4 pb-4 border-bottom" style="border-color: rgba(255,255,255,0.2) !important;">
                        <div class="stat-value">ACTIVE</div>
                        <div style="opacity: 0.9;">Premium Plan</div>
                    </div>
                    @endif
                @else
                    <div class="text-center mb-4 pb-4 border-bottom" style="border-color: rgba(255,255,255,0.2) !important;">
                        <div class="stat-value">FREE</div>
                        <div style="opacity: 0.9;">No monthly cost</div>
                    </div>
                @endif

                <div class="mb-3">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>{{ $currentPlan->storage_limit_mb == -1 ? 'Unlimited' : $currentPlan->storage_limit_mb . ' MB' }}</strong> Storage
                </div>
                <div class="mb-3">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>{{ $currentPlan->max_documents_per_month == -1 ? 'Unlimited' : $currentPlan->max_documents_per_month }}</strong> Documents/Month
                </div>
                <div class="mb-4">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>{{ $currentPlan->max_categories == -1 ? 'Unlimited' : $currentPlan->max_categories }}</strong> Categories
                </div>

                @if($currentPlan->description)
                    <div class="mt-4 pt-4" style="border-top: 1px solid rgba(255,255,255,0.2);">
                        <small style="opacity: 0.9;">{{ $currentPlan->description }}</small>
                    </div>
                @endif
            </div>
        </div>

        {{-- Usage Statistics --}}
        <div class="col-lg-8 ds-animate ds-animate-delay-1">
            <div class="row g-4 mb-4">
                {{-- Storage Usage --}}
                <div class="col-md-6">
                    <div class="usage-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="usage-icon me-3" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                                <i class="bi bi-hdd"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">Storage Usage</h6>
                                <small class="text-muted">{{ $storageUsedMb }} MB / {{ $currentPlan->storage_limit_mb == -1 ? 'Unlimited' : $currentPlan->storage_limit_mb . ' MB' }}</small>
                            </div>
                        </div>
                        @if($currentPlan->storage_limit_mb == -1)
                        <div class="text-center py-2">
                            <span class="badge bg-success"><i class="bi bi-infinity me-1"></i> Unlimited</span>
                        </div>
                        @else
                        <div class="progress-custom mb-2">
                            <div class="progress-bar-custom" 
                                 style="width: {{ min(100, $storageUsagePercentage) }}%; background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%);">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold" style="color: #3b82f6;">{{ round($storageUsagePercentage, 1) }}%</span>
                            @if($storageUsagePercentage > 80)
                                <small class="text-warning fw-semibold">
                                    <i class="bi bi-exclamation-triangle"></i> Low space
                                </small>
                            @else
                                <small class="text-success">
                                    <i class="bi bi-check-circle"></i> Good
                                </small>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Document Usage --}}
                <div class="col-md-6">
                    <div class="usage-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="usage-icon me-3" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">Documents This Month</h6>
                                <small class="text-muted">{{ $user->documents_count_current_month }} / {{ $currentPlan->max_documents_per_month == -1 ? 'Unlimited' : $currentPlan->max_documents_per_month }}</small>
                            </div>
                        </div>
                        @if($currentPlan->max_documents_per_month == -1)
                        <div class="text-center py-2">
                            <span class="badge bg-success"><i class="bi bi-infinity me-1"></i> Unlimited</span>
                        </div>
                        @else
                        <div class="progress-custom mb-2">
                            <div class="progress-bar-custom" 
                                 style="width: {{ min(100, $documentUsagePercentage) }}%; background: linear-gradient(90deg, #10b981 0%, #059669 100%);">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold" style="color: #10b981;">{{ round($documentUsagePercentage, 1) }}%</span>
                            @if($documentUsagePercentage > 80)
                                <small class="text-warning fw-semibold">
                                    <i class="bi bi-exclamation-triangle"></i> Near limit
                                </small>
                            @else
                                <small class="text-success">
                                    <i class="bi bi-check-circle"></i> Good
                                </small>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Category Usage --}}
                <div class="col-md-6">
                    <div class="usage-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="usage-icon me-3" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                                <i class="bi bi-bookmarks"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">Categories</h6>
                                <small class="text-muted">{{ $categoryUsage }} / {{ $currentPlan->max_categories == -1 ? 'Unlimited' : $currentPlan->max_categories }} used</small>
                            </div>
                        </div>
                        @if($currentPlan->max_categories == -1)
                        <div class="text-center py-2">
                            <span class="badge bg-success"><i class="bi bi-infinity me-1"></i> Unlimited</span>
                        </div>
                        @else
                        @php
                            $categoryPercentage = $currentPlan->max_categories > 0 ? ($categoryUsage / $currentPlan->max_categories * 100) : 0;
                        @endphp
                        <div class="progress-custom mb-2">
                            <div class="progress-bar-custom" 
                                 style="width: {{ min(100, $categoryPercentage) }}%; background: linear-gradient(90deg, #8b5cf6 0%, #7c3aed 100%);">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold" style="color: #8b5cf6;">{{ round($categoryPercentage, 1) }}%</span>
                            @if($categoryPercentage >= 100)
                                <small class="text-danger fw-semibold">
                                    <i class="bi bi-x-circle"></i> Limit reached
                                </small>
                            @else
                                <small class="text-success">
                                    <i class="bi bi-check-circle"></i> Available
                                </small>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Total Documents --}}
                <div class="col-md-6">
                    <div class="usage-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="usage-icon me-3" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                <i class="bi bi-files"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">Total Documents</h6>
                                <small class="text-muted">All time</small>
                            </div>
                        </div>
                        <div class="stat-value" style="color: #f59e0b;">{{ $user->documents()->count() }}</div>
                    </div>
                </div>
            </div>

            {{-- Plan Features --}}
            <div class="usage-card">
                <h5 class="fw-bold mb-4">
                    <i class="bi bi-stars text-primary me-2"></i>Plan Features
                </h5>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="bi bi-cloud-upload"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">Document Upload & Sign</div>
                        <small class="text-muted">Upload PDFs and add digital signatures</small>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="bi bi-send"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">Send for Signature</div>
                        <small class="text-muted">Send documents to multiple recipients</small>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="bi bi-qr-code"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">QR Code Verification</div>
                        <small class="text-muted">Secure document verification with QR codes</small>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="bi bi-bookmarks"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">Document Categories</div>
                        <small class="text-muted">Organize documents with custom categories</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
