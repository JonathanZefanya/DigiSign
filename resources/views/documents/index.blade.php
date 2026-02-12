@extends('layouts.app')
@section('title', 'My Documents')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 ds-animate">
    <div>
        <h1 class="fw-bold mb-1" style="font-size: 1.75rem;">
            <i class="bi bi-file-earmark-pdf text-primary me-2"></i>My Documents
        </h1>
        <p class="text-muted mb-0">Manage and sign your PDF documents</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('documents.create') }}" class="btn btn-outline-primary btn-lg">
            <i class="bi bi-cloud-upload"></i> Upload & Sign
        </a>
        <a href="{{ route('documents.send.form') }}" class="btn btn-primary btn-lg" id="sendDocumentBtn">
            <i class="bi bi-send"></i> Send Document
        </a>
    </div>
</div>

{{-- Documents Pending Your Signature --}}
@if($pendingSignatures->isNotEmpty())
    <div class="alert alert-info border-0 shadow-sm ds-animate ds-animate-delay-1 mb-4" style="background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%);">
        <div class="d-flex align-items-start">
            <div class="flex-shrink-0">
                <div style="width: 48px; height: 48px; background: #3b82f6; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-pencil-square text-white" style="font-size: 1.5rem;"></i>
                </div>
            </div>
            <div class="flex-grow-1 ms-3">
                <h5 class="fw-bold mb-2" style="color: #1e40af;">
                    <i class="bi bi-inbox"></i> Documents Pending Your Signature
                    <span class="badge bg-primary ms-2">{{ $pendingSignatures->count() }}</span>
                </h5>
                <p class="mb-3 text-muted">The following documents require your signature:</p>
                
                <div class="row g-3">
                    @foreach($pendingSignatures as $doc)
                        @php
                            $recipient = $doc->recipients->first();
                        @endphp
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold mb-0">{{ Str::limit($doc->title, 35) }}</h6>
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-clock"></i> Pending
                                        </span>
                                    </div>
                                    <p class="text-muted small mb-2">
                                        <i class="bi bi-person"></i> From: <strong>{{ $doc->user->name }}</strong>
                                    </p>
                                    <p class="text-muted small mb-3">
                                        <i class="bi bi-calendar3"></i> {{ $doc->created_at->diffForHumans() }}
                                    </p>
                                    <a href="{{ route('documents.sign.token', $recipient->signature_token) }}" 
                                       class="btn btn-primary btn-sm w-100">
                                        <i class="bi bi-pen"></i> Sign Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Section Divider --}}
<div class="d-flex align-items-center mb-4 ds-animate ds-animate-delay-2">
    <h4 class="fw-bold mb-0 me-3">
        <i class="bi bi-folder2-open text-primary"></i> My Uploaded Documents
    </h4>
    <hr class="flex-grow-1">
</div>

{{-- Filters --}}
<div class="row mb-4 g-3 ds-animate ds-animate-delay-1">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Search by title or filename..." value="{{ request('search') }}">
        </div>
    </div>
    <div class="col-6 col-md-3">
        <select id="categoryFilter" class="form-select">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-6 col-md-3">
        <select id="statusFilter" class="form-select">
            <option value="">All Statuses</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Signature</option>
            <option value="signed" {{ request('status') === 'signed' ? 'selected' : '' }}>Signed</option>
            <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>Revoked</option>
        </select>
    </div>
</div>

<div id="document-list">
    @include('documents.partials.list')
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
                <p class="fw-semibold mb-3" id="deleteDocTitle" style="color:var(--ds-dark);"></p>
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
@endsection

@push('scripts')
<script>
let searchTimeout;

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');

    // Debounce search
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => fetchDocuments(), 500);
    });

    categoryFilter.addEventListener('change', () => fetchDocuments());
    statusFilter.addEventListener('change', () => fetchDocuments());

    // Handle pagination clicks within the list container
    document.getElementById('document-list').addEventListener('click', function(e) {
        if (e.target.tagName === 'A' && e.target.closest('.pagination')) {
            e.preventDefault();
            const url = e.target.closest('a').href;
            fetchDocuments(url);
        }
    });
});

function fetchDocuments(url = null) {
    const container = document.getElementById('document-list');
    const search = document.getElementById('searchInput').value;
    const category = document.getElementById('categoryFilter').value;
    const status = document.getElementById('statusFilter').value;

    // Build URL
    let fetchUrl = url || '{{ route('documents.index') }}';
    const params = new URLSearchParams(url ? url.split('?')[1] : null);
    
    if (search) params.set('search', search); else params.delete('search');
    if (category) params.set('category', category); else params.delete('category');
    if (status) params.set('status', status); else params.delete('status');

    // If base URL doesn't have ? query, add it properly
    const baseUrl = fetchUrl.split('?')[0];
    const finalUrl = `${baseUrl}?${params.toString()}`;

    // Add loading state
    container.style.opacity = '0.6';

    fetch(finalUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        container.innerHTML = html;
        container.style.opacity = '1';
        
        // Update URL bar without reload
        window.history.pushState({}, '', finalUrl);
    })
    .catch(error => {
        console.error('Error:', error);
        container.style.opacity = '1';
    });
}

// Global helper to filter by category (called from badge click)
function filterCategory(id) {
    const select = document.getElementById('categoryFilter');
    select.value = id;
    fetchDocuments();
}

function filterStatus(status) {
    const select = document.getElementById('statusFilter');
    select.value = status;
    fetchDocuments();
}

// Delete document modal handler (event delegation for AJAX-loaded content)
let currentDeleteDocId = null;
const deleteModal = new bootstrap.Modal(document.getElementById('deleteDocModal'));

document.getElementById('document-list').addEventListener('click', function(e) {
    const deleteBtn = e.target.closest('.delete-doc-btn');
    if (deleteBtn) {
        e.preventDefault();
        currentDeleteDocId = deleteBtn.getAttribute('data-doc-id');
        document.getElementById('deleteDocTitle').textContent = deleteBtn.getAttribute('data-doc-title');
        deleteModal.show();
    }
});

document.getElementById('confirmDeleteDocBtn').addEventListener('click', function() {
    if (currentDeleteDocId) {
        const form = document.getElementById('delete-doc-form-' + currentDeleteDocId);
        if (form) form.submit();
    }
});
</script>
@endpush
