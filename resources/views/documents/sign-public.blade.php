@extends('layouts.app')
@section('title', 'Sign Document - ' . $document->title)

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

    .ds-pdf-viewer {
        background: #fff;
        border: 1px solid var(--ds-border);
        border-radius: var(--ds-radius);
        box-shadow: var(--ds-card-shadow);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        min-height: 600px;
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
        opacity: 0.5;
        cursor: not-allowed;
    }

    .ds-pdf-canvas-wrapper {
        flex: 1;
        overflow: auto;
        padding: 1.5rem;
        background: #e2e8f0;
        display: flex;
        justify-content: center;
        align-items: flex-start;
    }

    .ds-pdf-page-container {
        position: relative;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        background: #fff;
        display: inline-block;
    }
    
    #pdfCanvas {
        display: block;
        min-width: 200px;
        min-height: 200px;
    }

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
    }

    .ds-qr-stamp:hover {
        border-color: var(--ds-primary);
        box-shadow: 0 0 0 4px rgba(13,110,253,0.15);
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
        color: var(--ds-teal);
        pointer-events: none;
    }

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

    .ds-tool-card {
        background: #fff;
        border: 1px solid var(--ds-border);
        border-radius: var(--ds-radius);
        box-shadow: var(--ds-card-shadow);
        padding: 1.25rem;
        margin-bottom: 1rem;
    }

    .ds-stamp-preview {
        width: 100%;
        padding: 1.5rem;
        border: 2px dashed var(--ds-border);
        border-radius: 0.75rem;
        text-align: center;
        cursor: pointer;
        background: #f8fafc;
        transition: var(--ds-transition);
    }

    .ds-stamp-preview:hover {
        border-color: var(--ds-teal);
        background: #f0fdfa;
    }

    .ds-coord-display {
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
        background: #f1f5f9;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        margin-bottom: 0.5rem;
    }
@endsection

@section('content')
@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h4 class="fw-bold mb-0">
                    <i class="bi bi-pencil-square text-primary"></i> Sign Document
                </h4>
            </div>
            <p class="text-muted mb-0">{{ $document->title }}</p>
        </div>
    </div>
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <div class="alert alert-info mb-4">
        <h6 class="fw-bold mb-2">
            <i class="bi bi-envelope-open"></i> Document Signature Request
        </h6>
        <div class="row">
            <div class="col-md-4">
                <small class="text-muted">Document:</small>
                <div class="fw-semibold">{{ $document->title }}</div>
            </div>
            <div class="col-md-4">
                <small class="text-muted">From:</small>
                <div class="fw-semibold">{{ $document->user->name }}</div>
            </div>
            <div class="col-md-4">
                <small class="text-muted">Your Role:</small>
                <div>
                    @if($recipient->isSigner())
                        <span class="badge bg-primary">✍️ Signer (Action Required)</span>
                    @else
                        <span class="badge bg-success">👁️ Viewer (Read Only)</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    @if($recipient->isSigner())
    <div class="ds-sign-container">
        <div class="ds-pdf-viewer">
            <div class="ds-pdf-toolbar">
                <div class="page-nav">
                    <button onclick="prevPage()" id="prevPageBtn" disabled>
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <span class="fw-semibold small">
                        Page <span id="currentPage">1</span> of <span id="totalPages">-</span>
                    </span>
                    <button onclick="nextPage()" id="nextPageBtn" disabled>
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-secondary" onclick="zoomOut()">
                        <i class="bi bi-zoom-out"></i>
                    </button>
                    <span class="small fw-semibold" id="zoomLevel">100%</span>
                    <button class="btn btn-sm btn-outline-secondary" onclick="zoomIn()">
                        <i class="bi bi-zoom-in"></i>
                    </button>
                </div>
            </div>
            
            <div class="ds-pdf-canvas-wrapper">
                <div class="ds-pdf-page-container" id="pageContainer">
                    <canvas id="pdfCanvas"></canvas>
                    <div class="ds-qr-stamp" id="qrStamp">
                        <div class="ds-qr-stamp-inner">
                            <i class="bi bi-qr-code" style="font-size:1.5rem;"></i>
                            <span>QR Stamp</span>
                        </div>
                        <div class="ds-qr-resize"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div>
            <div class="ds-tool-card">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-qr-code text-primary"></i> QR Code Stamp
                </h6>
                <button class="ds-stamp-preview mb-3" onclick="placeStamp()" id="placeStampBtn">
                    <i class="bi bi-qr-code d-block mb-2" style="font-size:2rem;color:var(--ds-teal);"></i>
                    <span class="fw-bold d-block">Click to Place Stamp</span>
                    <small class="text-muted">Then drag to position</small>
                </button>
                
                <div id="stampCoords" style="display:none;">
                    <small class="text-muted fw-semibold">Position:</small>
                    <div class="ds-coord-display">
                        X: <span id="coordX">0</span>, Y: <span id="coordY">0</span>
                    </div>
                    <small class="text-muted fw-semibold">Size:</small>
                    <div class="ds-coord-display">
                        W: <span id="coordW">100</span> × H: <span id="coordH">100</span>
                    </div>
                </div>
            </div>
            
            <div class="ds-tool-card">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-info-circle text-primary"></i> Instructions
                </h6>
                <ol class="small ps-3 mb-0">
                    <li>Click "Place Stamp" above</li>
                    <li>Drag the QR stamp to desired position</li>
                    <li>Resize using bottom-right handle</li>
                    <li>Click "Sign Document" below</li>
                </ol>
            </div>
            
            <form action="{{ route('documents.sign.token.process', $token) }}" method="POST" id="signForm">
                @csrf
                <input type="hidden" name="action" value="sign">
                <input type="hidden" name="qr_x" id="inputQrX">
                <input type="hidden" name="qr_y" id="inputQrY">
                <input type="hidden" name="qr_page" id="inputQrPage">
                <input type="hidden" name="qr_width" id="inputQrW">
                <input type="hidden" name="qr_height" id="inputQrH">
                <input type="hidden" name="canvas_width" id="inputCanvasW">
                
                <button type="submit" class="btn btn-primary btn-lg w-100 mb-2" id="signBtn" disabled>
                    <i class="bi bi-shield-check"></i> Sign Document
                </button>
                <button type="button" onclick="rejectDocument()" class="btn btn-outline-danger w-100">
                    <i class="bi bi-x-circle"></i> Reject
                </button>
            </form>
        </div>
    </div>
    @else
    {{-- Viewer Only --}}
    <div class="card">
        <div class="card-body text-center py-5">
            <iframe src="{{ route('documents.sign.token.pdf', $token) }}" 
                    class="w-100 border rounded mb-3" 
                    style="height: 600px;">
            </iframe>
            <form action="{{ route('documents.sign.token.process', $token) }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="view">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-check-circle"></i> Mark as Viewed
                </button>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/interactjs@1.10.27/dist/interact.min.js"></script>

<script>
console.log('[DEBUG] Scripts section loaded');
console.log('[DEBUG] Is signer:', {{ $recipient->isSigner() ? 'true' : 'false' }});
console.log('[DEBUG] Recipient role:', '{{ $recipient->role }}');
</script>

@if($recipient->isSigner())
<script>
// Check if libraries are loaded
console.log('[INIT] PDF.js loaded:', typeof pdfjsLib !== 'undefined');
console.log('[INIT] Interact.js loaded:', typeof interact !== 'undefined');

if (typeof pdfjsLib === 'undefined') {
    alert('ERROR: PDF.js library failed to load!');
} else {
    // Set PDF.js worker
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    
    var pdfDoc = null;
    var currentPageNum = 1;
    var currentScale = 1.0;
    var stampPlaced = false;
    
    var canvas = document.getElementById('pdfCanvas');
    var ctx = canvas.getContext('2d');
    var qrStamp = document.getElementById('qrStamp');
    
    // Debug: Check if elements exist
    console.log('[DEBUG] Canvas element:', canvas);
    console.log('[DEBUG] Canvas context:', ctx);
    console.log('[DEBUG] Canvas parent:', canvas ? canvas.parentElement : null);
    
    var pdfUrl = "{{ route('documents.sign.token.pdf', $token) }}";
    console.log('[PDF.js] Loading from:', pdfUrl);
    console.log('[PDF.js] Worker:', pdfjsLib.GlobalWorkerOptions.workerSrc);
    
    // Load PDF with proper options
    var loadingTask = pdfjsLib.getDocument({
        url: pdfUrl,
        withCredentials: false,
        isEvalSupported: false,
        cMapUrl: 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/cmaps/',
        cMapPacked: true
    });
    
    loadingTask.promise.then(function(pdf) {
        console.log('[PDF.js] ✓ PDF loaded successfully');
        console.log('[PDF.js] Pages:', pdf.numPages);
        console.log('[PDF.js] PDF object:', pdf);
        pdfDoc = pdf;
        document.getElementById('totalPages').textContent = pdf.numPages;
        document.getElementById('nextPageBtn').disabled = pdf.numPages <= 1;
        
        console.log('[PDF.js] Calling renderPage(1)...');
        renderPage(currentPageNum);
    }).catch(function(error) {
        console.error('[PDF.js] ✗ Error loading PDF:', error);
        console.error('[PDF.js] Error name:', error.name);
        console.error('[PDF.js] Error message:', error.message);
        console.error('[PDF.js] PDF URL:', pdfUrl);
        
        // Show user-friendly error
        var wrapper = document.querySelector('.ds-pdf-canvas-wrapper');
        if (wrapper) {
            wrapper.innerHTML = `
                <div class="alert alert-danger m-4" style="max-width: 500px;">
                    <h5><i class="bi bi-exclamation-triangle"></i> Failed to Load PDF</h5>
                    <p class="mb-2"><strong>URL:</strong><br><code class="small">${pdfUrl}</code></p>
                    <p class="mb-0"><strong>Error:</strong> ${error.message || 'Unknown error'}</p>
                    <hr>
                    <small class="text-muted">
                        <strong>Troubleshooting:</strong><br>
                        1. Open browser console (F12) for details<br>
                        2. Try opening URL directly in new tab<br>
                        3. Check network tab for failed requests
                    </small>
                </div>
            `;
        }
    });
    
    function renderPage(num) {
        console.log('[renderPage] Starting render for page:', num);
        console.log('[renderPage] pdfDoc:', pdfDoc);
        console.log('[renderPage] Canvas:', canvas, 'Size:', canvas.width, 'x', canvas.height);
        
        pdfDoc.getPage(num).then(function(page) {
            console.log('[renderPage] Page loaded:', page);
            
            var viewport = page.getViewport({ scale: currentScale });
            console.log('[renderPage] Viewport:', viewport.width, 'x', viewport.height);
            
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            console.log('[renderPage] Canvas resized to:', canvas.width, 'x', canvas.height);
            
            var renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };
            
            console.log('[renderPage] Starting render...');
            return page.render(renderContext).promise;
        }).then(function() {
            console.log('[renderPage] ✓ Render complete!');
            document.getElementById('currentPage').textContent = num;
            document.getElementById('prevPageBtn').disabled = num <= 1;
            document.getElementById('nextPageBtn').disabled = num >= pdfDoc.numPages;
            document.getElementById('inputCanvasW').value = canvas.width;
        }).catch(function(error) {
            console.error('[renderPage] ✗ Error rendering page:', error);
        });
    }
    
    function prevPage() {
        if (currentPageNum > 1) {
            currentPageNum--;
            renderPage(currentPageNum);
        }
    }
    
    function nextPage() {
        if (currentPageNum < pdfDoc.numPages) {
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
            '<i class="bi bi-check-circle text-success d-block mb-2" style="font-size:2rem;"></i>' +
            '<span class="fw-bold text-success d-block">Stamp Placed!</span>' +
            '<small class="text-muted">Drag to reposition</small>';
        
        updateCoords();
    }
    
    function updateCoords() {
        var x = parseFloat(qrStamp.getAttribute('data-x')) || 0;
        var y = parseFloat(qrStamp.getAttribute('data-y')) || 0;
        var w = qrStamp.offsetWidth;
        var h = qrStamp.offsetHeight;
        
        document.getElementById('coordX').textContent = Math.round(x);
        document.getElementById('coordY').textContent = Math.round(y);
        document.getElementById('coordW').textContent = Math.round(w);
        document.getElementById('coordH').textContent = Math.round(h);
        
        document.getElementById('inputQrX').value = x;
        document.getElementById('inputQrY').value = y;
        document.getElementById('inputQrPage').value = currentPageNum;
        document.getElementById('inputQrW').value = w;
        document.getElementById('inputQrH').value = h;
    }
    
    // Initialize Interact.js
    interact('#qrStamp').draggable({
        inertia: true,
        modifiers: [
            interact.modifiers.restrictRect({
                restriction: '#pageContainer',
                endOnly: true
            })
        ],
        listeners: {
            move: function(event) {
                var target = event.target;
                var x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
                var y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;
                
                target.style.left = x + 'px';
                target.style.top = y + 'px';
                target.setAttribute('data-x', x);
                target.setAttribute('data-y', y);
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
            move: function(event) {
                var target = event.target;
                target.style.width = event.rect.width + 'px';
                target.style.height = event.rect.height + 'px';
                updateCoords();
            }
        }
    });
    
    // Form validation
    document.getElementById('signForm').addEventListener('submit', function(e) {
        if (!stampPlaced) {
            e.preventDefault();
            alert('Please place the QR stamp on the document before signing.');
            return;
        }
        
        if (!confirm('Are you sure you want to sign this document?')) {
            e.preventDefault();
        }
    });
    
    function rejectDocument() {
        if (confirm('Are you sure you want to reject this document?')) {
            document.querySelector('input[name="action"]').value = 'reject';
            document.getElementById('signForm').submit();
        }
    }
    
    console.log('[INIT] All functions defined in global scope');
}
</script>
@endif
@endsection
