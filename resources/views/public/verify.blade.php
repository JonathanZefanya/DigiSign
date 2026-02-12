@extends('layouts.app')
@section('title', 'Document Verification')

@section('styles')
    .ds-verify-wrapper {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 0;
    }

    .ds-certificate {
        width: 100%;
        max-width: 640px;
        background: #fff;
        border-radius: 1rem;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,.12);
        overflow: hidden;
        border: 1px solid var(--ds-border);
    }

    .ds-cert-header-valid {
        background: linear-gradient(135deg, #059669 0%, #0d9488 100%);
        padding: 2.5rem 2rem;
        text-align: center;
        color: #fff;
        position: relative;
    }

    .ds-cert-header-invalid {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        padding: 2.5rem 2rem;
        text-align: center;
        color: #fff;
        position: relative;
    }

    .ds-cert-header-valid::after,
    .ds-cert-header-invalid::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        right: 0;
        height: 20px;
        background: #fff;
        clip-path: ellipse(55% 100% at 50% 100%);
    }

    .ds-cert-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 2.5rem;
    }

    .ds-cert-body {
        padding: 2rem;
    }

    .ds-cert-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 0.875rem 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .ds-cert-row:last-child {
        border-bottom: none;
    }

    .ds-cert-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--ds-gray);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        min-width: 140px;
    }

    .ds-cert-value {
        font-weight: 600;
        color: var(--ds-dark);
        text-align: right;
        word-break: break-all;
    }

    .ds-cert-footer {
        background: #f8fafc;
        padding: 1.25rem 2rem;
        text-align: center;
        border-top: 1px solid var(--ds-border);
    }

    .ds-cert-footer p {
        margin: 0;
        font-size: 0.8rem;
        color: var(--ds-gray);
    }

    .ds-cert-watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 8rem;
        opacity: 0.08;
        font-weight: 900;
        pointer-events: none;
    }

    .ds-hash-display {
        font-family: 'Courier New', monospace;
        font-size: 0.75rem;
        background: #f1f5f9;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        word-break: break-all;
        color: var(--ds-dark);
    }
@endsection

@section('content')
<div class="ds-verify-wrapper">
    <div class="ds-certificate ds-animate">
        @if($isValid)
            {{-- Valid Document --}}
            <div class="ds-cert-header-valid">
                <div class="ds-cert-watermark">✓</div>
                <div class="ds-cert-icon">
                    <i class="bi bi-shield-fill-check"></i>
                </div>
                <h2 class="fw-bold mb-1" style="font-size: 1.75rem;">Document Verified</h2>
                <p style="opacity:0.9;margin:0;">This document has been digitally signed and verified</p>
            </div>

            <div class="ds-cert-body">
                <div class="text-center mb-4">
                    <span class="ds-badge ds-badge-signed" style="font-size:1rem;padding:0.5rem 1.25rem;">
                        <i class="bi bi-check-circle-fill me-1"></i> VALID SIGNATURE
                    </span>
                </div>

                <div class="ds-cert-row">
                    <div class="ds-cert-label">
                        <i class="bi bi-tag me-1"></i> Document
                    </div>
                    <div class="ds-cert-value">{{ $document->title }}</div>
                </div>

                <div class="ds-cert-row">
                    <div class="ds-cert-label">
                        <i class="bi bi-person me-1"></i> Signer
                    </div>
                    <div class="ds-cert-value">{{ $document->user->name }}</div>
                </div>

                <div class="ds-cert-row">
                    <div class="ds-cert-label">
                        <i class="bi bi-calendar3 me-1"></i> Signed Date
                    </div>
                    <div class="ds-cert-value">
                        {{ $document->signed_at ? $document->signed_at->timezone($appTimezone)->format('F d, Y \a\t h:i A') : 'N/A' }}
                    </div>
                </div>

                <div class="ds-cert-row">
                    <div class="ds-cert-label">
                        <i class="bi bi-shield me-1"></i> Status
                    </div>
                    <div class="ds-cert-value text-success">
                        <i class="bi bi-check-circle-fill me-1"></i> Verified & Valid
                    </div>
                </div>

                <div class="mt-3">
                    <div class="ds-cert-label mb-2">
                        <i class="bi bi-fingerprint me-1"></i> Document Hash (SHA-256)
                    </div>
                    <div class="ds-hash-display">{{ $hash }}</div>
                </div>
            </div>

            <div class="ds-cert-footer">
                <p>
                    <i class="bi bi-shield-check me-1"></i>
                    This verification was generated by <strong>{{ $appName ?? 'DigiSign' }}</strong>
                    — Digital Signature Platform
                </p>
            </div>

        @else
            {{-- Invalid / Not Found --}}
            <div class="ds-cert-header-invalid">
                <div class="ds-cert-watermark">✗</div>
                <div class="ds-cert-icon">
                    <i class="bi bi-shield-fill-x"></i>
                </div>
                <h2 class="fw-bold mb-1" style="font-size: 1.75rem;">Verification Failed</h2>
                <p style="opacity:0.9;margin:0;">This document could not be verified</p>
            </div>

            <div class="ds-cert-body text-center">
                <div class="mb-4">
                    <span class="ds-badge ds-badge-revoked" style="font-size:1rem;padding:0.5rem 1.25rem;">
                        <i class="bi bi-x-circle-fill me-1"></i>
                        @if($document)
                            DOCUMENT NOT SIGNED
                        @else
                            DOCUMENT NOT FOUND
                        @endif
                    </span>
                </div>

                <p class="text-muted mb-3">
                    @if($document)
                        This document exists in our system but has not been signed yet.
                    @else
                        No document matching this verification hash was found in our system.
                        The document may have been deleted or the link may be incorrect.
                    @endif
                </p>

                <div class="mt-3">
                    <div class="ds-cert-label mb-2 text-center">Verification Hash</div>
                    <div class="ds-hash-display">{{ $hash }}</div>
                </div>
            </div>

            <div class="ds-cert-footer">
                <p>
                    <i class="bi bi-info-circle me-1"></i>
                    If you believe this is an error, please contact the document owner.
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
