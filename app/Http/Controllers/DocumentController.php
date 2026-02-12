<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use setasign\Fpdi\Fpdi;

class DocumentController extends Controller
{
    /**
     * Display user's documents.
     */
    public function index(Request $request)
    {
        $query = auth()->user()->documents()->with('category');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('original_filename', 'like', "%{$search}%");
            });
        }

        // Filter Category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $documents = $query->latest()->paginate(12);

        if ($request->ajax()) {
            return view('documents.partials.list', compact('documents'))->render();
        }

        // Get categories for filter (own + global)
        $categories = \App\Models\Category::where(function ($q) {
            $q->where('user_id', auth()->id())->orWhereNull('user_id');
        })->active()->orderBy('name')->get();

        // Get documents pending user's signature (where user is recipient)
        $pendingSignatures = \App\Models\Document::whereHas('recipients', function($q) {
            $q->where('email', auth()->user()->email)
              ->where('status', 'PENDING')
              ->where('role', 'SIGNER');
        })->with(['user', 'recipients' => function($q) {
            $q->where('email', auth()->user()->email);
        }])->latest()->get();

        return view('documents.index', compact('documents', 'categories', 'pendingSignatures'));
    }

    /**
     * Show the upload form.
     */
    public function create()
    {
        // Show user's own categories plus global categories (user_id is null)
        $categories = Category::where(function ($query) {
            $query->where('user_id', auth()->id())
                  ->orWhereNull('user_id');
        })
        ->active()
        ->orderBy('name')
        ->get();
        
        $maxSizeMb = (int) \App\Models\Setting::get('max_upload_size', 10);
        
        return view('documents.create', compact('categories', 'maxSizeMb'));
    }

    /**
     * Store a new uploaded document.
     */
    public function store(Request $request)
    {
        $maxSizeMb = (int) \App\Models\Setting::get('max_upload_size', 10);
        $maxSizeKb = $maxSizeMb * 1024;

        $request->validate([
            'title' => 'required|string|max:255',
            'pdf_file' => 'required|mimes:pdf|max:' . $maxSizeKb,
            'category_id' => 'nullable|exists:categories,id',
        ], [
            'pdf_file.max' => "The document must not be larger than {$maxSizeMb} MB.",
        ]);

        $file = $request->file('pdf_file');
        $originalName = $file->getClientOriginalName();
        $hash = hash('sha256', file_get_contents($file->getRealPath()) . now()->timestamp . auth()->id());

        $path = $file->store('documents/originals', 'public');

        $document = Document::create([
            'user_id' => auth()->id(),
            'category_id' => $request->category_id,
            'title' => $request->title,
            'original_filename' => $originalName,
            'file_path' => $path,
            'document_hash' => $hash,
            'status' => 'draft',
        ]);

        return redirect()->route('documents.sign', $document)
            ->with('success', 'Document uploaded successfully! Place the QR stamp and sign.');
    }

    /**
     * Show the signing interface.
     */
    public function sign(Document $document)
    {
        // Ensure user owns the document
        if ($document->user_id != auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        if ($document->isSigned()) {
            return redirect()->route('documents.show', $document)
                ->with('info', 'This document has already been signed.');
        }

        return view('documents.sign', compact('document'));
    }

    /**
     * Process the signing (flatten QR onto PDF).
     */
    public function processSign(Request $request, Document $document)
    {
        if ($document->user_id != auth()->id()) {
            abort(403);
        }

        if ($document->isSigned()) {
            return back()->withErrors(['document' => 'This document is already signed.']);
        }

        $request->validate([
            'qr_x' => 'required|numeric',
            'qr_y' => 'required|numeric',
            'qr_page' => 'required|integer|min:1',
            'qr_width' => 'required|numeric|min:10',
            'qr_height' => 'required|numeric|min:10',
        ]);

        try {
            // Generate QR code image
            $verificationUrl = route('verify.show', $document->document_hash);
            $qrImageContent = QrCode::format('png')
                ->size(300)
                ->margin(1)
                ->generate($verificationUrl);

            $qrTempPath = storage_path('app/temp_qr_' . $document->id . '.png');
            file_put_contents($qrTempPath, $qrImageContent);

            // Use FPDI to stamp QR onto PDF
            $pdf = new Fpdi();
            $sourcePath = Storage::disk('public')->path($document->file_path);
            $pageCount = $pdf->setSourceFile($sourcePath);

            $targetPage = min((int) $request->qr_page, $pageCount);

            for ($i = 1; $i <= $pageCount; $i++) {
                $templateId = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($templateId);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);

                // Place QR code on the target page
                if ($i === $targetPage) {
                    // Convert pixel coordinates to PDF points
                    // PDF.js renders at 96 DPI, PDF points are 72 DPI
                    $scale = $size['width'] / ($request->input('canvas_width', $size['width']));
                    $qrX = $request->qr_x * $scale;
                    $qrY = $request->qr_y * $scale;
                    $qrW = $request->qr_width * $scale;
                    $qrH = $request->qr_height * $scale;

                    $pdf->Image($qrTempPath, $qrX, $qrY, $qrW, $qrH);
                }
            }

            // Save signed PDF
            $signedPath = 'documents/signed/' . Str::uuid() . '.pdf';
            $signedFullPath = Storage::disk('public')->path($signedPath);

            // Ensure directory exists
            $dir = dirname($signedFullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $pdf->Output('F', $signedFullPath);

            // Clean up temp QR
            if (file_exists($qrTempPath)) {
                unlink($qrTempPath);
            }

            // Update document record
            $document->update([
                'signed_file_path' => $signedPath,
                'status' => 'signed',
                'signed_at' => now(),
                'qr_position' => [
                    'x' => $request->qr_x,
                    'y' => $request->qr_y,
                    'page' => $request->qr_page,
                    'width' => $request->qr_width,
                    'height' => $request->qr_height,
                ],
            ]);

            return redirect()->route('documents.show', $document)
                ->with('success', 'Document signed successfully! QR verification stamp has been placed.');

        } catch (\Exception $e) {
            // Clean up on error
            if (isset($qrTempPath) && file_exists($qrTempPath)) {
                unlink($qrTempPath);
            }

            return back()->withErrors(['signing' => 'Failed to sign document: ' . $e->getMessage()]);
        }
    }

    /**
     * Show a specific document.
     */
    public function show(Document $document)
    {
        if ($document->user_id != auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        return view('documents.show', compact('document'));
    }

    /**
     * Download the signed PDF.
     */
    public function download(Document $document)
    {
        if ($document->user_id != auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $path = $document->signed_file_path ?? $document->file_path;

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File not found.');
        }

        $filename = pathinfo($document->original_filename, PATHINFO_FILENAME)
            . ($document->isSigned() ? '_signed' : '')
            . '.pdf';

        return Storage::disk('public')->download($path, $filename);
    }

    /**
     * Delete a document.
     */
    public function destroy(Document $document)
    {
        if ($document->user_id != auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $owner = $document->user;
        $totalSizeKb = 0;

        // Delete files from storage and calculate size
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            $totalSizeKb += (int) ceil(Storage::disk('public')->size($document->file_path) / 1024);
            Storage::disk('public')->delete($document->file_path);
        }
        if ($document->signed_file_path && Storage::disk('public')->exists($document->signed_file_path)) {
            $totalSizeKb += (int) ceil(Storage::disk('public')->size($document->signed_file_path) / 1024);
            Storage::disk('public')->delete($document->signed_file_path);
        }

        // Decrement user's usage counters
        if ($owner) {
            $owner->decrementStorageUsage($totalSizeKb);
            $owner->decrementDocumentCount();
        }

        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Document deleted successfully.');
    }

    /**
     * Serve PDF file for viewer (prevents direct public access).
     */
    public function servePdf(Document $document)
    {
        if ($document->user_id != auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $path = $document->file_path;

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($path), [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
