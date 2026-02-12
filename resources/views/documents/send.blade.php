@extends('layouts.app')

@section('title', 'Send Document')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 ds-animate">
    <div>
        <h1 class="fw-bold mb-1" style="font-size: 1.75rem;">
            <i class="bi bi-send text-primary me-2"></i>
            Send Document for Signature
        </h1>
        <p class="text-muted mb-0">Upload a document and invite recipients to sign</p>
    </div>
    <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Documents
    </a>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show ds-animate" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@php
    $smtpConfigured = \App\Models\Setting::get('smtp_host') && \App\Models\Setting::get('smtp_username');
@endphp

@if(!$smtpConfigured)
    <div class="alert alert-warning alert-dismissible fade show ds-animate" role="alert">
        <h5 class="alert-heading">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>SMTP Not Configured
        </h5>
        <p class="mb-2">
            Email delivery is <strong>required</strong> for OTP verification. Recipients won't be able to access documents without SMTP configured.
        </p>
        <hr>
        <p class="mb-0">
            <strong>Action Required:</strong> 
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.smtp.index') }}" class="alert-link">Configure SMTP Settings</a> before sending documents.
            @else
                Please contact your administrator to configure SMTP settings.
            @endif
        </p>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-lg-8">
        <form action="{{ route('documents.send') }}" method="POST" enctype="multipart/form-data" id="sendDocumentForm">
            @csrf

            <div class="ds-card ds-animate ds-animate-delay-1 mb-4">
                <div class="card-header">
                    <i class="bi bi-file-earmark-pdf me-2"></i>Document Details
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label for="title" class="form-label">
                            <i class="bi bi-text-left me-1"></i> Document Title *
                        </label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                               class="form-control form-control-lg @error('title') is-invalid @enderror"
                               placeholder="Enter document title">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="document" class="form-label">
                            <i class="bi bi-cloud-upload me-1"></i> Upload Document *
                        </label>
                        <input type="file" name="document" id="document" accept=".pdf" required
                               class="form-control form-control-lg @error('document') is-invalid @enderror">
                        <div class="form-text">
                            <i class="bi bi-info-circle"></i> Only PDF files are supported
                        </div>
                        @error('document')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label for="category_id" class="form-label">
                            <i class="bi bi-folder me-1"></i> Category <span class="text-muted">(Optional)</span>
                        </label>
                        <select name="category_id" id="category_id" class="form-select form-select-lg">
                            <option value="">— No Category —</option>
                            @forelse($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @empty
                                <option value="" disabled>No categories available</option>
                            @endforelse
                        </select>
                        @if($categories->isEmpty())
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i> 
                                <a href="{{ route('categories.index') }}" class="text-decoration-none">Create categories</a> to organize your documents
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="ds-card ds-animate ds-animate-delay-2 mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-people me-2"></i>Recipients</span>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="signMyselfCheckbox" style="cursor: pointer;">
                        <label class="form-check-label fw-semibold text-primary" for="signMyselfCheckbox" style="cursor: pointer;">
                            <i class="bi bi-pencil-square"></i> Sign Myself Only
                        </label>
                    </div>
                </div>
                <div class="card-body">
                    <div id="recipientsContainer">
                        <div class="recipient-item mb-3 p-3 border rounded">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small">Name</label>
                                    <input type="text" name="recipients[0][name]" 
                                           class="form-control"
                                           placeholder="Recipient name">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small">Email *</label>
                                    <input type="email" name="recipients[0][email]" required
                                           class="form-control"
                                           placeholder="email@example.com">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small">Role *</label>
                                    <select name="recipients[0][role]" class="form-select">
                                        <option value="SIGNER">✍️ Signer (Must Sign)</option>
                                        <option value="VIEWER">👁️ Viewer (Read Only)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" onclick="addRecipient()" id="addRecipientBtn"
                            class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-plus-circle"></i> Add Another Recipient
                    </button>
                </div>
            </div>

            <input type="hidden" name="sign_myself" id="signMyselfInput" value="0">

            <div class="d-flex gap-3 ds-animate ds-animate-delay-3">
                <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                    <i class="bi bi-send"></i> Send Document
                </button>
                <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-x-lg"></i> Cancel
                </a>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        @include('partials.plan-usage')
    </div>
</div>

<script>
let recipientCount = 1;

function addRecipient() {
    const container = document.getElementById('recipientsContainer');
    const newRecipient = document.createElement('div');
    newRecipient.className = 'recipient-item mb-3 p-3 border rounded position-relative';
    newRecipient.innerHTML = `
        <button type="button" onclick="this.parentElement.remove()" 
                class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2" style="z-index: 10;">
            <i class="bi bi-x-lg"></i>
        </button>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-bold small">Name</label>
                <input type="text" name="recipients[${recipientCount}][name]" 
                       class="form-control"
                       placeholder="Recipient name">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small">Email *</label>
                <input type="email" name="recipients[${recipientCount}][email]" required
                       class="form-control"
                       placeholder="email@example.com">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small">Role *</label>
                <select name="recipients[${recipientCount}][role]" class="form-select">
                    <option value="SIGNER">✍️ Signer (Must Sign)</option>
                    <option value="VIEWER">👁️ Viewer (Read Only)</option>
                </select>
            </div>
        </div>
    `;
    container.appendChild(newRecipient);
    recipientCount++;
}

// Handle "Sign Myself" checkbox
document.getElementById('signMyselfCheckbox').addEventListener('change', function() {
    const recipientsContainer = document.getElementById('recipientsContainer');
    const addRecipientBtn = document.getElementById('addRecipientBtn');
    const signMyselfInput = document.getElementById('signMyselfInput');
    
    if (this.checked) {
        signMyselfInput.value = '1';
        recipientsContainer.style.display = 'none';
        addRecipientBtn.style.display = 'none';
        
        // Remove required from recipient emails
        recipientsContainer.querySelectorAll('input[type="email"]').forEach(input => {
            input.removeAttribute('required');
        });
    } else {
        signMyselfInput.value = '0';
        recipientsContainer.style.display = 'block';
        addRecipientBtn.style.display = 'block';
        
        // Add required back to recipient emails
        recipientsContainer.querySelectorAll('input[type="email"]').forEach(input => {
            input.setAttribute('required', 'required');
        });
    }
});

// Check SMTP before form submission
const smtpConfigured = {{ $smtpConfigured ? 'true' : 'false' }};
const sendForm = document.getElementById('sendDocumentForm');

sendForm.addEventListener('submit', function(e) {
    if (!smtpConfigured) {
        e.preventDefault();
        alert('❌ SMTP Not Configured!\n\nEmail delivery is required for document signing with OTP verification.\n\n' + 
              @if(auth()->user()->isAdmin())
                  'Please configure SMTP settings first before sending documents.'
              @else
                  'Please contact your administrator to configure SMTP settings.'
              @endif
        );
        return false;
    }
});
</script>
@endsection
