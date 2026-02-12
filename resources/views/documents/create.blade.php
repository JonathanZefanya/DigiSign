@extends('layouts.app')
@section('title', 'Upload Document')

@section('styles')
    .ds-upload-zone {
        border: 3px dashed var(--ds-border);
        border-radius: 1rem;
        padding: 4rem 2rem;
        text-align: center;
        background: #f8fafc;
        transition: var(--ds-transition);
        cursor: pointer;
        position: relative;
    }

    .ds-upload-zone:hover,
    .ds-upload-zone.dragover {
        border-color: var(--ds-primary);
        background: #eff6ff;
    }

    .ds-upload-zone.dragover {
        transform: scale(1.01);
    }

    .ds-upload-zone .upload-icon {
        font-size: 4rem;
        color: #94a3b8;
        margin-bottom: 1rem;
        transition: var(--ds-transition);
    }

    .ds-upload-zone:hover .upload-icon {
        color: var(--ds-primary);
        transform: translateY(-5px);
    }

    .ds-upload-zone h4 {
        color: var(--ds-dark);
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .ds-upload-zone p {
        color: var(--ds-gray);
        margin-bottom: 0;
    }

    .ds-file-preview {
        background: #fff;
        border: 2px solid #d1fae5;
        border-radius: 0.75rem;
        padding: 1.25rem;
        display: none;
        align-items: center;
        gap: 1rem;
    }

    .ds-file-preview.active {
        display: flex;
    }

    .ds-file-icon {
        width: 56px;
        height: 56px;
        background: #fef3c7;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .ds-file-icon i {
        font-size: 1.5rem;
        color: #d97706;
    }

    .ds-step-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-bottom: 2rem;
    }

    .ds-step {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 50rem;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .ds-step.active {
        background: var(--ds-primary);
        color: #fff;
    }

    .ds-step.completed {
        background: #d1fae5;
        color: #065f46;
    }

    .ds-step.pending {
        background: #f1f5f9;
        color: #94a3b8;
    }

    .ds-step-divider {
        width: 40px;
        height: 2px;
        background: var(--ds-border);
    }
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        {{-- Step Indicator --}}
        <div class="ds-step-indicator ds-animate">
            <div class="ds-step active" id="step1Indicator">
                <i class="bi bi-1-circle-fill"></i> Upload PDF
            </div>
            <div class="ds-step-divider"></div>
            <div class="ds-step pending" id="step2Indicator">
                <i class="bi bi-2-circle"></i> Place QR Stamp
            </div>
            <div class="ds-step-divider"></div>
            <div class="ds-step pending" id="step3Indicator">
                <i class="bi bi-3-circle"></i> Sign & Save
            </div>
        </div>

        <div class="ds-card ds-animate ds-animate-delay-1">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-cloud-arrow-up text-primary" style="font-size:1.3rem;"></i>
                Upload New Document
            </div>
            <div class="card-body">
                <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    @csrf

                    {{-- Document Title --}}
                    <div class="mb-4">
                        <label for="title" class="form-label">
                            <i class="bi bi-tag me-1"></i> Document Title
                        </label>
                        <input type="text"
                               class="form-control form-control-lg @error('title') is-invalid @enderror"
                               id="title"
                               name="title"
                               value="{{ old('title') }}"
                               placeholder="e.g. Contract Agreement 2025"
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Category --}}
                    <div class="mb-4">
                        <label for="category_id" class="form-label">
                            <i class="bi bi-bookmark me-1"></i> Category
                            <small class="text-muted">(optional)</small>
                        </label>
                        <select class="form-select @error('category_id') is-invalid @enderror"
                                id="category_id" name="category_id">
                            <option value="">— No Category —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Upload Zone --}}
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="bi bi-file-earmark-pdf me-1"></i> PDF File
                        </label>

                        <div class="ds-upload-zone" id="uploadZone" onclick="document.getElementById('pdfFile').click()">
                            <i class="bi bi-cloud-arrow-up upload-icon"></i>
                            <h4>Drop your PDF here</h4>
                            <p>or click to browse — Maximum file size: {{ $maxSizeMb }}MB</p>
                        </div>

                        <input type="file"
                               class="d-none @error('pdf_file') is-invalid @enderror"
                               id="pdfFile"
                               name="pdf_file"
                               accept=".pdf"
                               required>
                        @error('pdf_file')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror

                        {{-- File Preview --}}
                        <div class="ds-file-preview mt-3" id="filePreview">
                            <div class="ds-file-icon">
                                <i class="bi bi-file-earmark-pdf-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold" id="fileName">—</div>
                                <div class="text-muted small" id="fileSize">—</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearFile()" title="Remove file">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="d-flex gap-3">
                        <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg flex-grow-1" id="uploadSubmitBtn" disabled>
                            <i class="bi bi-arrow-right-circle"></i> Upload & Continue to Sign
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tips Card --}}
        <div class="ds-card mt-4 ds-animate ds-animate-delay-2">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-lightbulb text-warning me-2"></i>Tips for Best Results</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="d-flex gap-2">
                            <i class="bi bi-check-circle text-success mt-1"></i>
                            <div class="small">Use clear, readable PDF files for best QR stamp placement</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-2">
                            <i class="bi bi-check-circle text-success mt-1"></i>
                            <div class="small">Maximum file size is {{ $maxSizeMb }}MB. Larger files may take longer to process</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-2">
                            <i class="bi bi-check-circle text-success mt-1"></i>
                            <div class="small">After uploading, you'll place a QR code stamp on the document</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <div style="width: 80px; height: 80px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
                <h5 class="fw-bold mb-2">File Too Large</h5>
                <p class="text-muted mb-4" id="errorModalMessage">The selected file exceeds the maximum allowed size.</p>
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Okay, I understand</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const uploadZone = document.getElementById('uploadZone');
const pdfFile = document.getElementById('pdfFile');
const filePreview = document.getElementById('filePreview');
const submitBtn = document.getElementById('uploadSubmitBtn');
const MAX_SIZE_MB = {{ $maxSizeMb }};
const MAX_SIZE_BYTES = MAX_SIZE_MB * 1024 * 1024;
const errorModalElement = document.getElementById('errorModal');
const errorModal = new bootstrap.Modal(errorModalElement);

// Drag & Drop
['dragenter', 'dragover'].forEach(event => {
    uploadZone.addEventListener(event, (e) => {
        e.preventDefault();
        uploadZone.classList.add('dragover');
    });
});

['dragleave', 'drop'].forEach(event => {
    uploadZone.addEventListener(event, (e) => {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
    });
});

uploadZone.addEventListener('drop', (e) => {
    const files = e.dataTransfer.files;
    if (files.length && files[0].type === 'application/pdf') {
        pdfFile.files = files;
        showFilePreview(files[0]);
    }
});

pdfFile.addEventListener('change', function() {
    if (this.files.length) {
        showFilePreview(this.files[0]);
    }
});

function showFilePreview(file) {
    if (file.size > MAX_SIZE_BYTES) {
        // Show Bootstrap Modal instead of alert
        document.getElementById('errorModalMessage').textContent = `The selected file is too large. The maximum allowed size is ${MAX_SIZE_MB} MB.`;
        errorModal.show();
        
        // Clear the file input
        clearFile();
        return;
    }

    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileSize').textContent = formatSize(file.size);
    filePreview.classList.add('active');
    uploadZone.style.display = 'none';
    submitBtn.disabled = false;

    // Auto-fill title if empty
    const titleInput = document.getElementById('title');
    if (!titleInput.value) {
        titleInput.value = file.name.replace('.pdf', '').replace(/[_-]/g, ' ');
    }
}

function clearFile() {
    pdfFile.value = '';
    filePreview.classList.remove('active');
    uploadZone.style.display = 'block';
    submitBtn.disabled = true;
}

function formatSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>
@endpush
