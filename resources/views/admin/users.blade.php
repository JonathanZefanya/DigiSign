@extends('layouts.app')
@section('title', 'Manage Users')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 ds-animate">
    <div>
        <h1 class="fw-bold mb-1" style="font-size: 1.75rem;">
            <i class="bi bi-people text-primary me-2"></i>Manage Users
        </h1>
        <p class="text-muted mb-0">View and manage all user accounts</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary" id="addUserBtn">
        <i class="bi bi-person-plus"></i> Add New User
    </a>
</div>

<div class="ds-card ds-animate ds-animate-delay-1">
    <div class="card-body">
        {{-- Filter Controls --}}
        <div class="row g-3 mb-3 align-items-end">
            <div class="col-md-4">
                <label for="searchUser" class="form-label small text-muted mb-1">
                    <i class="bi bi-search me-1"></i>Search User
                </label>
                <input type="text" 
                       class="form-control" 
                       id="searchUser" 
                       placeholder="Search by name or email..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label for="statusFilter" class="form-label small text-muted mb-1">
                    <i class="bi bi-funnel me-1"></i>Status
                </label>
                <select class="form-select" id="statusFilter">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Users</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="sortBy" class="form-label small text-muted mb-1">
                    <i class="bi bi-sort-down me-1"></i>Sort by Documents
                </label>
                <select class="form-select" id="sortBy">
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Newest First</option>
                    <option value="docs_desc" {{ request('sort') == 'docs_desc' ? 'selected' : '' }}>Most Documents</option>
                    <option value="docs_asc" {{ request('sort') == 'docs_asc' ? 'selected' : '' }}>Least Documents</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-secondary w-100" id="resetFilters">
                    <i class="bi bi-arrow-clockwise me-1"></i>Reset
                </button>
            </div>
        </div>
    </div>

    {{-- User List Container (AJAX Target) --}}
    <div id="userListContainer">
        @include('admin.partials.user-list', ['users' => $users])
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-danger" id="deleteUserModalLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>Delete User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Are you sure you want to delete <strong id="deleteUserName"></strong>?</p>
                <div class="alert alert-warning mb-0">
                    <strong>This will also delete:</strong>
                    <ul class="mb-0 mt-2">
                        <li><span id="deleteDocsCount"></span> document(s)</li>
                        <li>All categories created by this user</li>
                    </ul>
                    <hr class="my-2">
                    <small class="text-danger fw-bold">This action cannot be undone!</small>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash me-1"></i>Delete User
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let deleteUserId = null;
let searchTimeout = null;

function showDeleteModal(userId, userName, docsCount) {
    deleteUserId = userId;
    document.getElementById('deleteUserName').textContent = userName;
    document.getElementById('deleteDocsCount').textContent = docsCount;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteUserId) {
        document.getElementById('delete-form-' + deleteUserId).submit();
    }
});

// Real-time Filter Function
function applyFilters() {
    const search = document.getElementById('searchUser').value;
    const status = document.getElementById('statusFilter').value;
    const sort = document.getElementById('sortBy').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (status && status !== 'all') params.append('status', status);
    if (sort && sort !== 'latest') params.append('sort', sort);
    
    // Show loading state
    const container = document.getElementById('userListContainer');
    container.style.opacity = '0.5';
    container.style.pointerEvents = 'none';
    
    // Fetch filtered results
    fetch('{{ route('admin.users') }}?' + params.toString(), {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
        }
    })
    .then(response => response.text())
    .then(html => {
        container.innerHTML = html;
        container.style.opacity = '1';
        container.style.pointerEvents = 'auto';
        
        // Update URL without page reload
        const newUrl = '{{ route('admin.users') }}' + (params.toString() ? '?' + params.toString() : '');
        window.history.pushState({}, '', newUrl);
    })
    .catch(error => {
        console.error('Error fetching users:', error);
        container.style.opacity = '1';
        container.style.pointerEvents = 'auto';
    });
}

// Search with debounce (wait 500ms after user stops typing)
document.getElementById('searchUser').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
});

// Instant filter on select change
document.getElementById('statusFilter').addEventListener('change', applyFilters);
document.getElementById('sortBy').addEventListener('change', applyFilters);

// Reset filters
document.getElementById('resetFilters').addEventListener('click', function() {
    document.getElementById('searchUser').value = '';
    document.getElementById('statusFilter').value = 'all';
    document.getElementById('sortBy').value = 'latest';
    applyFilters();
});
</script>
@endpush
@endsection
