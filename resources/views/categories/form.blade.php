@extends('layouts.app')
@section('title', $category ? 'Edit Category' : 'Create Category')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 ds-animate">
    <div>
        <h1 class="fw-bold mb-1" style="font-size: 1.75rem;">
            <i class="bi bi-{{ $category ? 'pencil' : 'plus-circle' }} text-primary me-2"></i>
            {{ $category ? 'Edit Category' : 'Create New Category' }}
        </h1>
        <p class="text-muted mb-0">{{ $category ? 'Update category details' : 'Add a new category to organize your documents' }}</p>
    </div>
    <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to My Categories
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="ds-card ds-animate ds-animate-delay-1">
            <div class="card-header">
                <i class="bi bi-bookmark-star me-2"></i>Category Details
            </div>
            <div class="card-body">
                <form action="{{ $category ? route('categories.update', $category) : route('categories.store') }}"
                      method="POST" id="categoryForm">
                    @csrf
                    @if($category)
                        @method('PUT')
                    @endif

                    <!-- User ID is automatically handled by controller -->

                    <div class="mb-4">
                        <label for="name" class="form-label">
                            <i class="bi bi-tag me-1"></i> Category Name
                        </label>
                        <input type="text"
                               class="form-control form-control-lg @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name', $category?->name) }}"
                               placeholder="e.g. Personal, Work, Finance"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="color" class="form-label">
                            <i class="bi bi-palette me-1"></i> Badge Color
                        </label>
                        <div class="d-flex align-items-center gap-3">
                            <input type="color"
                                   class="form-control form-control-color @error('color') is-invalid @enderror"
                                   id="color"
                                   name="color"
                                   value="{{ old('color', $category?->color ?? '#0d6efd') }}"
                                   title="Choose badge color"
                                   style="width:60px;height:44px;cursor:pointer;">
                            <div class="flex-grow-1">
                                <div class="d-flex gap-2 flex-wrap">
                                    @php
                                        $presetColors = ['#0d6efd','#0d9488','#059669','#d97706','#dc2626','#7c3aed','#db2777','#475569'];
                                    @endphp
                                    @foreach($presetColors as $pc)
                                        <button type="button"
                                                class="border-0 rounded-circle p-0"
                                                style="width:28px;height:28px;background:{{ $pc }};cursor:pointer;transition:transform 0.15s;"
                                                onclick="document.getElementById('color').value='{{ $pc }}';document.getElementById('colorPreview').style.background='{{ $pc }}';"
                                                onmouseover="this.style.transform='scale(1.2)'"
                                                onmouseout="this.style.transform='scale(1)'"
                                                title="{{ $pc }}">
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @error('color')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label">
                            <i class="bi bi-text-paragraph me-1"></i> Description
                            <small class="text-muted">(optional)</small>
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description"
                                  name="description"
                                  rows="3"
                                  placeholder="Brief description of this category..."
                                  maxlength="500">{{ old('description', $category?->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($category)
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                                   style="width:3rem;height:1.5rem;cursor:pointer;">
                            <label class="form-check-label fw-semibold ms-2" for="is_active" style="cursor:pointer;line-height:1.5rem;">
                                Category is Active
                            </label>
                        </div>
                    </div>
                    @endif

                    <div class="mb-4 p-3 bg-light rounded">
                        <label class="form-label small text-muted mb-2">Preview</label>
                        <div>
                            <span id="colorPreview"
                                  class="badge px-3 py-2"
                                  style="background:{{ old('color', $category?->color ?? '#0d6efd') }};color:#fff;font-size:0.85rem;border-radius:50rem;">
                                {{ old('name', $category?->name ?? 'Category Name') }}
                            </span>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-x-lg"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-success btn-lg flex-grow-1" id="saveCategoryBtn">
                            <i class="bi bi-check-circle"></i>
                            {{ $category ? 'Update Category' : 'Create Category' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const colorInput = document.getElementById('color');
    const preview = document.getElementById('colorPreview');

    nameInput.addEventListener('input', function() {
        preview.textContent = this.value || 'Category Name';
    });

    colorInput.addEventListener('input', function() {
        preview.style.background = this.value;
    });
});
</script>
@endpush
