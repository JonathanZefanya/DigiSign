@extends('layouts.app')

@section('title', 'SMTP Settings')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 ds-animate">
    <div>
        <h1 class="fw-bold mb-1" style="font-size: 1.75rem;">
            <i class="bi bi-envelope-gear text-primary me-2"></i>
            SMTP Email Settings
        </h1>
        <p class="text-muted mb-0">Configure SMTP server for sending emails</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
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

        <div class="ds-card ds-animate ds-animate-delay-1 mb-4">
            <div class="card-header">
                <i class="bi bi-gear me-2"></i>SMTP Configuration
            </div>
            <div class="card-body">
                <form action="{{ route('admin.smtp.update') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="smtp_host" class="form-label">
                            <i class="bi bi-server me-1"></i> SMTP Host *
                        </label>
                        <input type="text" name="smtp_host" id="smtp_host" 
                               value="{{ old('smtp_host', $settings['smtp_host']) }}" required
                               placeholder="smtp.gmail.com"
                               class="form-control form-control-lg @error('smtp_host') is-invalid @enderror">
                        @error('smtp_host')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="smtp_port" class="form-label">
                                <i class="bi bi-plug me-1"></i> SMTP Port *
                            </label>
                            <input type="number" name="smtp_port" id="smtp_port"
                                   value="{{ old('smtp_port', $settings['smtp_port']) }}" required
                                   placeholder="587"
                                   class="form-control form-control-lg @error('smtp_port') is-invalid @enderror">
                            @error('smtp_port')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="smtp_encryption" class="form-label">
                                <i class="bi bi-shield-lock me-1"></i> Encryption *
                            </label>
                            <select name="smtp_encryption" id="smtp_encryption" required
                                    class="form-select form-select-lg">
                                <option value="tls" {{ old('smtp_encryption', $settings['smtp_encryption']) == 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ old('smtp_encryption', $settings['smtp_encryption']) == 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="none" {{ old('smtp_encryption', $settings['smtp_encryption']) == 'none' ? 'selected' : '' }}>None</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="smtp_username" class="form-label">
                            <i class="bi bi-person me-1"></i> Username *
                        </label>
                        <input type="text" name="smtp_username" id="smtp_username"
                               value="{{ old('smtp_username', $settings['smtp_username']) }}" required
                               placeholder="your-email@gmail.com"
                               class="form-control form-control-lg @error('smtp_username') is-invalid @enderror">
                        @error('smtp_username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="smtp_password" class="form-label">
                            <i class="bi bi-key me-1"></i> Password
                        </label>
                        <input type="password" name="smtp_password" id="smtp_password" 
                               value="" autocomplete="new-password"
                               placeholder="Leave empty to keep current password"
                               class="form-control form-control-lg">
                        <div class="form-text">
                            <i class="bi bi-info-circle"></i> Leave empty to keep the current password
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="smtp_from_address" class="form-label">
                            <i class="bi bi-envelope me-1"></i> From Email *
                        </label>
                        <input type="email" name="smtp_from_address" id="smtp_from_address"
                               value="{{ old('smtp_from_address', $settings['smtp_from_address']) }}" required
                               placeholder="noreply@yourdomain.com"
                               class="form-control form-control-lg @error('smtp_from_address') is-invalid @enderror">
                        @error('smtp_from_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="smtp_from_name" class="form-label">
                            <i class="bi bi-tag me-1"></i> From Name *
                        </label>
                        <input type="text" name="smtp_from_name" id="smtp_from_name"
                               value="{{ old('smtp_from_name', $settings['smtp_from_name']) }}" required
                               placeholder="Digital Signature App"
                               class="form-control form-control-lg @error('smtp_from_name') is-invalid @enderror">
                        @error('smtp_from_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-save"></i> Save SMTP Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="ds-card ds-animate ds-animate-delay-2">
            <div class="card-header bg-success text-white">
                <i class="bi bi-send-check me-2"></i>Test Email Configuration
            </div>
            <div class="card-body">
                <form action="{{ route('admin.smtp.test') }}" method="POST">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label for="test_email" class="form-label">
                                <i class="bi bi-envelope-at me-1"></i> Test Email Address
                            </label>
                            <input type="email" name="test_email" id="test_email" 
                                   placeholder="test@example.com" required
                                   class="form-control form-control-lg">
                        </div>
                        <div class="col-md-4">
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-send"></i> Send Test
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
