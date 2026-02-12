@extends('layouts.app')
@section('title', 'My Categories')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 ds-animate">
    <div>
        <h1 class="fw-bold mb-1" style="font-size: 1.75rem;">
            <i class="bi bi-bookmarks text-primary me-2"></i>My Categories
        </h1>
        <p class="text-muted mb-0">Manage your personal document categories</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-file-earmark-pdf"></i> My Documents
        </a>
        <a href="{{ route('categories.create') }}" class="btn btn-primary" id="addCategoryBtn">
            <i class="bi bi-plus-circle"></i> New Category
        </a>
    </div>
</div>

<div class="ds-card ds-animate ds-animate-delay-1">
    <div class="card-body p-0">
        @if($categories->count() > 0)
            <div class="table-responsive">
                <table class="table ds-table mb-0">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Documents</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span style="width:14px;height:14px;border-radius:50%;background:{{ $category->color }};display:inline-block;flex-shrink:0;border:2px solid rgba(0,0,0,.1);"></span>
                                        <div>
                                            <div class="fw-semibold">{{ $category->name }}</div>
                                            @if($category->description)
                                                <div class="text-muted small">{{ Str::limit($category->description, 50) }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $category->documents_count }}</span>
                                    <span class="text-muted small">docs</span>
                                </td>
                                <td>
                                    @if($category->is_active)
                                        <span class="ds-badge ds-badge-signed">
                                            <i class="bi bi-check-circle me-1"></i> Active
                                        </span>
                                    @else
                                        <span class="ds-badge ds-badge-revoked">
                                            <i class="bi bi-x-circle me-1"></i> Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $category->created_at->timezone($appTimezone)->format('M d, Y') }}</td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <a href="{{ route('categories.edit', $category) }}"
                                           class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                                                onclick="showDeleteModal('{{ $category->id }}', '{{ addslashes($category->name) }}', {{ $category->documents_count }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $category->id }}" action="{{ route('categories.destroy', $category) }}" method="POST" class="d-none">
                                            @csrf @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3 d-flex justify-content-center">
                {{ $categories->links() }}
            </div>
        @else
            <div class="ds-empty-state">
                <i class="bi bi-bookmarks"></i>
                <h5>No Categories Yet</h5>
                <p>Create categories to organize your documents efficiently.</p>
                <a href="{{ route('categories.create') }}" class="btn btn-primary mt-2">
                    <i class="bi bi-plus-circle"></i> Create First Category
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Delete Category Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-danger" id="deleteCategoryModalLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>Delete Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Are you sure you want to delete <strong id="deleteCategoryName"></strong>?</p>
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    <span id="deleteDocsCount"></span> document(s) will be unassigned from this category.
                    <hr class="my-2">
                    <small class="text-danger fw-bold">This action cannot be undone!</small>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash me-1"></i>Delete Category
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let deleteCategoryId = null;

function showDeleteModal(categoryId, categoryName, docsCount) {
    deleteCategoryId = categoryId;
    document.getElementById('deleteCategoryName').textContent = categoryName;
    document.getElementById('deleteDocsCount').textContent = docsCount;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteCategoryModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteCategoryId) {
        document.getElementById('delete-form-' + deleteCategoryId).submit();
    }
});
</script>
@endpush
@endsection
