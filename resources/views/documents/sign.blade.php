@extends('layouts.app')
@section('title', 'Sign Document')

@section('styles')
    .ds-sign-container {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 1.5rem;
        min-height: calc(100vh - 250px);
    }

    @media (max-width: 991px) {
        .ds-sign-container {
            grid-template-columns: 1fr;
        }
    }

    /* PDF Viewer */
    .ds-pdf-viewer {
        background: #fff;
        border: 1px solid var(--ds-border);
        border-radius: var(--ds-radius);
        box-shadow: var(--ds-card-shadow);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .ds-pdf-toolbar {
        background: #f8fafc;
        border-bottom: 1px solid var(--ds-border);
        padding: 0.75rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .ds-pdf-toolbar .page-nav {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .ds-pdf-toolbar .page-nav button {
        width: 36px;
        height: 36px;
        border-radius: 0.375rem;
        border: 1px solid var(--ds-border);
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--ds-transition);
    }

    .ds-pdf-toolbar .page-nav button:hover:not(:disabled) {
        background: var(--ds-primary);
        color: #fff;
        border-color: var(--ds-primary);
    }

    .ds-pdf-toolbar .page-nav button:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .ds-pdf-canvas-wrapper {
        flex: 1;
        overflow: auto;
        padding: 1.5rem;
        background: #e2e8f0;
        display: flex;
        justify-content: center;
        position: relative;
    }

    .ds-pdf-page-container {
        position: relative;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        background: #fff;
    }

    #pdfCanvas {
        display: block;
    }

    /* QR Stamp Draggable */
    .ds-qr-stamp {
        position: absolute;
        width: 100px;
        height: 100px;
        border: 3px dashed var(--ds-teal);
        border-radius: 0.5rem;
        background: rgba(13, 148, 136, 0.08);
        cursor: move;
        display: none;
        z-index: 100;
        touch-action: none;
        user-select: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .ds-qr-stamp:hover {
        border-color: var(--ds-primary);
        box-shadow: 0 0 0 4px rgba(13,110,253,0.15);
    }

    .ds-qr-stamp.placed {
        border-style: solid;
        background: rgba(13, 148, 136, 0.12);
    }

    .ds-qr-stamp-inner {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--ds-teal-dark);
        pointer-events: none;
    }

    .ds-qr-stamp-inner i {
        font-size: 1.5rem;
        margin-bottom: 0.25rem;
    }

    /* Resize handle */
    .ds-qr-resize {
        position: absolute;
        bottom: -4px;
        right: -4px;
        width: 16px;
        height: 16px;
        background: var(--ds-teal);
        border-radius: 50%;
        cursor: nwse-resize;
        border: 2px solid #fff;
    }

    /* Toolbox Sidebar */
    .ds-toolbox {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .ds-tool-card {
        background: #fff;
        border: 1px solid var(--ds-border);
        border-radius: var(--ds-radius);
        box-shadow: var(--ds-card-shadow);
        padding: 1.25rem;
    }

    .ds-tool-card h6 {
        font-weight: 700;
        font-size: 0.95rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .ds-stamp-preview {
        width: 100%;
        padding: 1.5rem;
        border: 2px dashed var(--ds-border);
        border-radius: 0.75rem;
        text-align: center;
        cursor: pointer;
        transition: var(--ds-transition);
        background: #f8fafc;
    }

    .ds-stamp-preview:hover {
        border-color: var(--ds-teal);
        background: #f0fdfa;
    }

    .ds-stamp-preview i {
        font-size: 2.5rem;
        color: var(--ds-teal);
    }

    .ds-coord-display {
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
        background: #f1f5f9;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        color: var(--ds-dark);
    }

    .ds-instructions li {
        font-size: 0.9rem;
        color: var(--ds-gray);
        padding: 0.35rem 0;
    }

    .ds-instructions li i {
        color: var(--ds-teal);
        margin-right: 0.5rem;
    }
@endsection

@section('content')
{{-- Header --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 ds-animate">
    <div>
        <h1 class="fw-bold mb-1" style="font-size: 1.5rem;">
            <i class="bi bi-pen text-primary me-2"></i>Sign Document
        </h1>
        <p class="text-muted mb-0">{{ $document->title }}</p>
    </div>
    <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Documents
    </a>
</div>

{{-- Step Indicator --}}
<div class="d-flex align-items-center justify-content-center gap-2 mb-4 ds-animate">
    <div class="ds-badge" style="background:#d1fae5;color:#065f46;">
        <i class="bi bi-check-circle-fill me-1"></i> 1. Uploaded
    </div>
    <div style="width:30px;height:2px;background:var(--ds-border);"></div>
    <div class="ds-badge" style="background:#dbeafe;color:#1e40af;">
        <i class="bi bi-arrow-right-circle me-1"></i> 2. Place QR Stamp
    </div>
    <div style="width:30px;height:2px;background:var(--ds-border);"></div>
    <div class="ds-badge" style="background:#f1f5f9;color:#94a3b8;">
        3. Sign & Save
    </div>
</div>

<div class="ds-sign-container ds-animate ds-animate-delay-1">
    {{-- PDF Viewer --}}
    <div class="ds-pdf-viewer">
        <div class="ds-pdf-toolbar">
            <div class="page-nav">
                <button onclick="prevPage()" id="prevPageBtn" disabled title="Previous Page">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <span class="fw-semibold" style="font-size:0.9rem;">
                    Page <span id="currentPage">1</span> of <span id="totalPages">-</span>
                </span>
                <button onclick="nextPage()" id="nextPageBtn" disabled title="Next Page">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary" onclick="zoomOut()" title="Zoom Out">
                    <i class="bi bi-zoom-out"></i>
                </button>
                <span class="small fw-semibold" id="zoomLevel">100%</span>
                <button class="btn btn-sm btn-outline-secondary" onclick="zoomIn()" title="Zoom In">
                    <i class="bi bi-zoom-in"></i>
                </button>
            </div>
        </div>

        <div class="ds-pdf-canvas-wrapper" id="canvasWrapper">
            <div class="ds-pdf-page-container" id="pageContainer">
                <canvas id="pdfCanvas"></canvas>

                {{-- Draggable QR Stamp --}}
                <div class="ds-qr-stamp" id="qrStamp">
                    <div class="ds-qr-stamp-inner">
                        <i class="bi bi-qr-code"></i>
                        <span>QR Stamp</span>
                    </div>
                    <div class="ds-qr-resize" id="qrResize"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Toolbox Sidebar --}}
    <div class="ds-toolbox">
        {{-- Stamp Tool --}}
        <div class="ds-tool-card">
            <h6><i class="bi bi-qr-code text-teal"></i> QR Code Stamp</h6>
            <p class="text-muted small mb-3">Click the button below to place a QR verification stamp on the document.</p>

            <button class="ds-stamp-preview w-100 mb-3" onclick="placeStamp()" id="placeStampBtn">
                <i class="bi bi-qr-code d-block mb-2"></i>
                <span class="fw-bold d-block">Click to Place Stamp</span>
                <small class="text-muted">Then drag to position</small>
            </button>

            <div id="stampCoords" style="display:none;">
                <div class="mb-2">
                    <small class="text-muted fw-semibold">Position:</small>
                    <div class="ds-coord-display mt-1">
                        X: <span id="coordX">0</span>, Y: <span id="coordY">0</span>
                    </div>
                </div>
                <div>
                    <small class="text-muted fw-semibold">Size:</small>
                    <div class="ds-coord-display mt-1">
                        W: <span id="coordW">100</span> × H: <span id="coordH">100</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Instructions --}}
        <div class="ds-tool-card">
            <h6><i class="bi bi-info-circle text-primary"></i> How to Sign</h6>
            <ol class="ds-instructions ps-3 mb-0">
                <li><i class="bi bi-1-circle-fill"></i> Click "Place Stamp" above</li>
                <li><i class="bi bi-2-circle-fill"></i> Drag the QR stamp to the desired position</li>
                <li><i class="bi bi-3-circle-fill"></i> Resize by dragging the bottom-right handle</li>
                <li><i class="bi bi-4-circle-fill"></i> Click "Save & Sign Document"</li>
            </ol>
        </div>

        {{-- Sign Button --}}
        <form action="{{ route('documents.processSign', $document) }}" method="POST" id="signForm">
            @csrf
            <input type="hidden" name="qr_x" id="inputQrX">
            <input type="hidden" name="qr_y" id="inputQrY">
            <input type="hidden" name="qr_page" id="inputQrPage">
            <input type="hidden" name="qr_width" id="inputQrW">
            <input type="hidden" name="qr_height" id="inputQrH">
            <input type="hidden" name="canvas_width" id="inputCanvasW">

            <button type="submit" class="btn btn-success btn-lg w-100" id="signBtn" disabled>
                <i class="bi bi-shield-check"></i> Save & Sign Document
            </button>
        </form>

        {{-- Document Info --}}
        <div class="ds-tool-card">
            <h6><i class="bi bi-file-earmark-text text-primary"></i> Document Info</h6>
            <table class="table table-sm table-borderless mb-0" style="font-size:0.85rem;">
                <tr>
                    <td class="text-muted fw-semibold" style="width:80px;">Title</td>
                    <td>{{ Str::limit($document->title, 25) }}</td>
                </tr>
                <tr>
                    <td class="text-muted fw-semibold">File</td>
                    <td>{{ Str::limit($document->original_filename, 25) }}</td>
                </tr>
                <tr>
                    <td class="text-muted fw-semibold">Status</td>
                    <td><span class="ds-badge ds-badge-{{ $document->status }}">{{ ucfirst($document->status) }}</span></td>
                </tr>
            </table>
        </div>
    </div>
</div>
@endsection

@push('head')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/interactjs@1.10.27/dist/interact.min.js"></script>
@endpush

@push('scripts')
<script>
    // Configure PDF.js worker
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    let pdfDoc = null;
    let currentPageNum = 1;
    let totalPagesCount = 0;
    let currentScale = 1.0;
    let stampPlaced = false;

    const canvas = document.getElementById('pdfCanvas');
    const ctx = canvas.getContext('2d');
    const pageContainer = document.getElementById('pageContainer');
    const qrStamp = document.getElementById('qrStamp');

    // Load PDF
    const pdfUrl = "{{ route('documents.pdf', $document) }}";

    pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
        pdfDoc = pdf;
        totalPagesCount = pdf.numPages;
        document.getElementById('totalPages').textContent = totalPagesCount;
        document.getElementById('nextPageBtn').disabled = totalPagesCount <= 1;
        renderPage(currentPageNum);
    }).catch(function(error) {
        console.error('Error loading PDF:', error);
        document.getElementById('canvasWrapper').innerHTML =
            '<div class="text-center py-5"><i class="bi bi-exclamation-triangle text-danger" style="font-size:3rem;"></i><p class="mt-3 text-muted">Failed to load PDF. Please try again.</p></div>';
    });

    function renderPage(num) {
        pdfDoc.getPage(num).then(function(page) {
            const viewport = page.getViewport({ scale: currentScale });
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            pageContainer.style.width = viewport.width + 'px';
            pageContainer.style.height = viewport.height + 'px';

            const renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };
            page.render(renderContext);

            document.getElementById('currentPage').textContent = num;
            document.getElementById('prevPageBtn').disabled = num <= 1;
            document.getElementById('nextPageBtn').disabled = num >= totalPagesCount;
            document.getElementById('inputCanvasW').value = viewport.width;
        });
    }

    function prevPage() {
        if (currentPageNum > 1) {
            currentPageNum--;
            renderPage(currentPageNum);
        }
    }

    function nextPage() {
        if (currentPageNum < totalPagesCount) {
            currentPageNum++;
            renderPage(currentPageNum);
        }
    }

    function zoomIn() {
        if (currentScale < 2.5) {
            currentScale += 0.25;
            document.getElementById('zoomLevel').textContent = Math.round(currentScale * 100) + '%';
            renderPage(currentPageNum);
        }
    }

    function zoomOut() {
        if (currentScale > 0.5) {
            currentScale -= 0.25;
            document.getElementById('zoomLevel').textContent = Math.round(currentScale * 100) + '%';
            renderPage(currentPageNum);
        }
    }

    // Place stamp on canvas
    function placeStamp() {
        qrStamp.style.display = 'block';
        qrStamp.style.left = '50px';
        qrStamp.style.top = '50px';
        qrStamp.setAttribute('data-x', 50);
        qrStamp.setAttribute('data-y', 50);
        stampPlaced = true;

        document.getElementById('stampCoords').style.display = 'block';
        document.getElementById('signBtn').disabled = false;
        document.getElementById('placeStampBtn').innerHTML =
            '<i class="bi bi-check-circle text-success d-block mb-1" style="font-size:1.5rem;"></i>' +
            '<span class="fw-bold text-success">Stamp Placed!</span><br>' +
            '<small class="text-muted">Drag to reposition</small>';

        updateCoords();
    }

    function updateCoords() {
        const x = parseFloat(qrStamp.getAttribute('data-x')) || parseFloat(qrStamp.style.left) || 0;
        const y = parseFloat(qrStamp.getAttribute('data-y')) || parseFloat(qrStamp.style.top) || 0;
        const w = qrStamp.offsetWidth;
        const h = qrStamp.offsetHeight;

        document.getElementById('coordX').textContent = Math.round(x);
        document.getElementById('coordY').textContent = Math.round(y);
        document.getElementById('coordW').textContent = Math.round(w);
        document.getElementById('coordH').textContent = Math.round(h);

        // Update hidden form fields
        document.getElementById('inputQrX').value = x;
        document.getElementById('inputQrY').value = y;
        document.getElementById('inputQrPage').value = currentPageNum;
        document.getElementById('inputQrW').value = w;
        document.getElementById('inputQrH').value = h;
    }

    // Interact.js - Draggable
    interact('#qrStamp').draggable({
        inertia: true,
        modifiers: [
            interact.modifiers.restrictRect({
                restriction: '#pageContainer',
                endOnly: true
            })
        ],
        listeners: {
            move(event) {
                const target = event.target;
                const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
                const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

                target.style.left = x + 'px';
                target.style.top = y + 'px';
                target.setAttribute('data-x', x);
                target.setAttribute('data-y', y);
                target.classList.add('placed');

                updateCoords();
            }
        }
    }).resizable({
        edges: { right: true, bottom: true },
        modifiers: [
            interact.modifiers.restrictSize({
                min: { width: 40, height: 40 },
                max: { width: 250, height: 250 }
            })
        ],
        listeners: {
            move(event) {
                const target = event.target;
                target.style.width = event.rect.width + 'px';
                target.style.height = event.rect.height + 'px';
                updateCoords();
            }
        }
    });

    // Form submission validation
    document.getElementById('signForm').addEventListener('submit', function(e) {
        if (!stampPlaced) {
            e.preventDefault();
            alert('Please place the QR stamp on the document before signing.');
            return;
        }

        updateCoords();

        if (!confirm('Are you sure you want to sign this document? This action cannot be undone.')) {
            e.preventDefault();
        }
    });
</script>
@endpush
