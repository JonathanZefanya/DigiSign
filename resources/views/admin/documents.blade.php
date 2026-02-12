@extends('layouts.app')
@section('title', 'All Documents')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 ds-animate">
    <div>
        <h1 class="fw-bold mb-1" style="font-size: 1.75rem;">
            <i class="bi bi-files text-primary me-2"></i>All Documents
        </h1>
        <p class="text-muted mb-0">View all documents across the platform</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>
</div>

{{-- Filters --}}
<div class="card mb-4 border-0 shadow-sm ds-animate">
    <div class="card-body bg-light rounded">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Search by title, filename, or user..." value="{{ request('search') }}">
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
                    <option value="signed" {{ request('status') === 'signed' ? 'selected' : '' }}>Signed</option>
                    <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>Revoked</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div id="document-list" class="ds-card ds-animate ds-animate-delay-1">
    <div class="card-body p-0">
        @include('admin.partials.document-list')
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
    const listBody = container.querySelector('.card-body');
    const search = document.getElementById('searchInput').value;
    const category = document.getElementById('categoryFilter').value;
    const status = document.getElementById('statusFilter').value;

    // Build URL
    let fetchUrl = url || '{{ route('admin.documents') }}';
    const params = new URLSearchParams(url ? url.split('?')[1] : null);
    
    if (search) params.set('search', search); else params.delete('search');
    if (category) params.set('category', category); else params.delete('category');
    if (status) params.set('status', status); else params.delete('status');

    // If base URL doesn't have ? query, add it properly
    const baseUrl = fetchUrl.split('?')[0];
    const finalUrl = `${baseUrl}?${params.toString()}`;

    // Add loading state
    if(listBody) listBody.style.opacity = '0.6';

    fetch(finalUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        if(listBody) {
            listBody.innerHTML = html;
            listBody.style.opacity = '1';
        } else {
             // Fallback if structure changes
             container.innerHTML = html;
        }
        
        // Update URL bar without reload
        window.history.pushState({}, '', finalUrl);
    })
    .catch(error => {
        console.error('Error:', error);
        if(listBody) listBody.style.opacity = '1';
    });
}

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
</script>
@endpush
