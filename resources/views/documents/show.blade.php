@extends('layouts.app')
@section('title', $document->title)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 ds-animate">
    <div>
        <h1 class="fw-bold mb-1" style="font-size: 1.5rem;">
            <i class="bi bi-file-earmark-text text-primary me-2"></i>Document Details
        </h1>
        <p class="text-muted mb-0">{{ $document->title }}</p>
    </div>
    <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Documents
    </a>
</div>

<div class="row g-4">
    {{-- Document Info --}}
    <div class="col-lg-8 ds-animate ds-animate-delay-1">
        <div class="ds-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-info-circle me-2"></i>Document Information</span>
                <span class="ds-badge ds-badge-{{ $document->status }}">
                    @if($document->status === 'signed')
                        <i class="bi bi-check-circle-fill me-1"></i>
                    @endif
                    {{ ucfirst($document->status) }}
                </span>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted fw-semibold" style="width:180px;">
                            <i class="bi bi-tag me-2"></i>Title
                        </td>
                        <td class="fw-semibold">{{ $document->title }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">
                            <i class="bi bi-file-earmark me-2"></i>Original File
                        </td>
                        <td>{{ $document->original_filename }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">
                            <i class="bi bi-person me-2"></i>Uploaded By
                        </td>
                        <td>{{ $document->user->name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">
                            <i class="bi bi-calendar3 me-2"></i>Uploaded On
                        </td>
                        <td>{{ $document->created_at->timezone($appTimezone)->format('F d, Y \a\t h:i A') }}</td>
                    </tr>
                    @if($document->signed_at)
                    <tr>
                        <td class="text-muted fw-semibold">
                            <i class="bi bi-check2-circle me-2"></i>Signed On
                        </td>
                        <td class="text-success fw-semibold">
                            {{ $document->signed_at->timezone($appTimezone)->format('F d, Y \a\t h:i A') }}
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted fw-semibold">
                            <i class="bi bi-fingerprint me-2"></i>Document Hash
                        </td>
                        <td>
                            <code class="bg-light p-1 rounded small">{{ Str::limit($document->document_hash, 40) }}</code>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="col-lg-4 ds-animate ds-animate-delay-2">
        <div class="ds-card mb-3">
            <div class="card-header">
                <i class="bi bi-lightning me-2"></i>Actions
            </div>
            <div class="card-body d-grid gap-2">
                @if(!$document->isSigned())
                    <a href="{{ route('documents.sign', $document) }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-pen"></i> Sign This Document
                    </a>
                @endif

                <a href="{{ route('documents.download', $document) }}" class="btn btn-outline-primary">
                    <i class="bi bi-download"></i> Download {{ $document->isSigned() ? 'Signed' : '' }} PDF
                </a>

                @if($document->isSigned())
                    <a href="{{ route('verify.show', $document->document_hash) }}" class="btn btn-outline-success" target="_blank">
                        <i class="bi bi-shield-check"></i> View Verification Page
                    </a>
                @endif

                <button type="button" class="btn btn-outline-danger w-100" id="deleteDocBtn">
                    <i class="bi bi-trash"></i> Delete Document
                </button>
                <form id="deleteDocForm" action="{{ route('documents.destroy', $document) }}" method="POST" class="d-none">
                    @csrf @method('DELETE')
                </form>
            </div>
        </div>

        @if($document->isSigned() && $document->qr_position)
        <div class="ds-card">
            <div class="card-header">
                <i class="bi bi-qr-code me-2"></i>QR Stamp Position
            </div>
            <div class="card-body">
                <div class="row g-2 text-center">
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <div class="small text-muted">Page</div>
                            <div class="fw-bold">{{ $document->qr_position['page'] ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <div class="small text-muted">X, Y</div>
                            <div class="fw-bold">{{ round($document->qr_position['x'] ?? 0) }}, {{ round($document->qr_position['y'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="bg-light rounded p-2">
                            <div class="small text-muted">Size</div>
                            <div class="fw-bold">{{ round($document->qr_position['width'] ?? 0) }} × {{ round($document->qr_position['height'] ?? 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Delete Document Modal --}}
<div class="modal fade" id="deleteDocModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border:none;border-radius:1rem;overflow:hidden;">
            <div class="modal-body text-center p-4">
                <div style="width:80px;height:80px;margin:0 auto 1.25rem;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size:2.2rem;"></i>
                </div>
                <h5 class="fw-bold mb-2">Delete Document?</h5>
                <p class="text-muted mb-1">You are about to delete:</p>
                <p class="fw-semibold mb-3" style="color:var(--ds-dark);">{{ $document->title }}</p>
                <div class="alert alert-warning py-2 px-3 text-start small mb-4">
                    <i class="bi bi-info-circle me-1"></i>
                    This action is <strong>permanent</strong> and cannot be undone. The file and all associated data will be removed.
                </div>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-danger px-4" id="confirmDeleteDocBtn">
                        <i class="bi bi-trash me-1"></i>Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('deleteDocBtn').addEventListener('click', function() {
    const modal = new bootstrap.Modal(document.getElementById('deleteDocModal'));
    modal.show();
});

document.getElementById('confirmDeleteDocBtn').addEventListener('click', function() {
    document.getElementById('deleteDocForm').submit();
});
</script>
@endpush
@endsection
