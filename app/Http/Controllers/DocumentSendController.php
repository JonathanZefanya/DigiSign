<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentRecipient;
use App\Services\DocumentSigningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentSendController extends Controller
{
    protected $signingService;

    public function __construct(DocumentSigningService $signingService)
    {
        $this->signingService = $signingService;
    }

    /**
     * Show the send document form
     */
    public function showForm()
    {
        $categories = auth()->user()->categories()->orderBy('name')->get();
        return view('documents.send', compact('categories'));
    }

    /**
     * Send document to recipients
     */
    public function sendDocument(Request $request)
    {
        // Check SMTP configuration first (required for OTP email)
        if (!$this->isSmtpConfigured()) {
            return back()
                ->withInput()
                ->with('error', 'SMTP not configured! Email delivery is required for OTP verification. Please configure SMTP in Admin Settings first.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'document' => 'required|file|mimes:pdf|max:10240',
            'category_id' => 'nullable|exists:categories,id',
            'sign_myself' => 'nullable|boolean',
            'recipients' => 'required_if:sign_myself,0|array',
            'recipients.*.email' => 'required_with:recipients|email',
            'recipients.*.name' => 'nullable|string',
            'recipients.*.role' => 'required_with:recipients|in:SIGNER,VIEWER',
        ]);

        $user = auth()->user();

        // Check quotas (already done by middleware, but double-check)
        if ($user->hasExceededDocumentQuota()) {
            return back()->with('error', 'Document quota exceeded. Please upgrade your plan.');
        }

        // Handle file upload
        $file = $request->file('document');
        $fileSizeKb = (int) ceil($file->getSize() / 1024);

        if ($user->hasExceededStorageQuota($fileSizeKb)) {
            return back()->with('error', 'Storage quota exceeded. Please upgrade your plan.');
        }

        // Store file
        $filename = time() . '_' . Str::slug($validated['title']) . '.pdf';
        $filePath = $file->storeAs('documents', $filename, 'private');

        // Create document
        $document = Document::create([
            'user_id' => $user->id,
            'category_id' => $validated['category_id'] ?? null,
            'title' => $validated['title'],
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'document_hash' => hash_file('sha256', $file->getRealPath()),
            'status' => 'pending',
        ]);

        // Update user quotas
        $user->incrementStorageUsage($fileSizeKb);
        $user->incrementDocumentCount();

        // Handle "Sign Myself" scenario
        if ($request->boolean('sign_myself')) {
            $recipient = $this->signingService->createSelfSigningDocument($document);
            
            return redirect()->route('documents.sign', ['document' => $document->id])
                ->with('success', 'Document uploaded. Please sign the document.');
        }

        // Handle recipients
        $this->signingService->addRecipients($document, $validated['recipients']);
        
        // Send invitation emails
        $this->signingService->sendInvitations($document);

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document sent successfully to ' . count($validated['recipients']) . ' recipient(s).');
    }

    /**
     * Check if SMTP is configured
     */
    private function isSmtpConfigured(): bool
    {
        $smtpHost = \App\Models\Setting::get('smtp_host');
        $smtpUsername = \App\Models\Setting::get('smtp_username');
        return !empty($smtpHost) && !empty($smtpUsername);
    }

    /**
     * Sign document by token (public access)
     */
    public function signByToken(string $token)
    {
        $recipient = DocumentRecipient::where('signature_token', $token)->firstOrFail();
        $document = $recipient->document;

        // Check if already processed
        if (!$this->signingService->canAccess($recipient)) {
            return view('documents.already-processed', compact('recipient', 'document'));
        }

        // Check if OTP is already verified
        if ($recipient->otp_verified_at) {
            return view('documents.sign-public', compact('document', 'recipient', 'token'));
        }

        // Check if OTP exists and is still valid
        if ($recipient->otp_code && $recipient->otp_expires_at && now()->lessThan($recipient->otp_expires_at)) {
            // OTP already sent and still valid, show verification form
            return view('documents.verify-otp', compact('document', 'recipient', 'token'));
        }

        // Generate new OTP
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $recipient->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
            'otp_verified_at' => null,
        ]);

        // Send OTP email
        try {
            \Mail::send('emails.otp', [
                'recipient' => $recipient,
                'document' => $document,
                'otp' => $otp,
            ], function ($message) use ($recipient, $document) {
                $message->to($recipient->email, $recipient->name)
                    ->subject('OTP Verification - ' . $document->title);
            });
        } catch (\Exception $e) {
            \Log::error('Failed to send OTP email', [
                'recipient' => $recipient->email,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to send OTP email. Please contact support.');
        }

        return view('documents.verify-otp', compact('document', 'recipient', 'token'));
    }

    /**
     * Verify OTP code
     */
    public function verifyOtp(Request $request, string $token)
    {
        $recipient = DocumentRecipient::where('signature_token', $token)->firstOrFail();

        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        // Check if OTP matches and is not expired
        if ($recipient->otp_code !== $request->otp) {
            return back()->with('error', 'Invalid OTP code. Please try again.');
        }

        if (!$recipient->otp_expires_at || now()->greaterThan($recipient->otp_expires_at)) {
            return back()->with('error', 'OTP has expired. Please request a new one.');
        }

        // Mark OTP as verified
        $recipient->update([
            'otp_verified_at' => now(),
        ]);

        return redirect()->route('documents.sign.token', $token)
            ->with('success', 'Email verified successfully! You can now sign the document.');
    }

    /**
     * Resend OTP code
     */
    public function resendOtp(string $token)
    {
        $recipient = DocumentRecipient::where('signature_token', $token)->firstOrFail();
        $document = $recipient->document;

        // Generate new OTP
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $recipient->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
            'otp_verified_at' => null,
        ]);

        // Send OTP email
        try {
            \Mail::send('emails.otp', [
                'recipient' => $recipient,
                'document' => $document,
                'otp' => $otp,
            ], function ($message) use ($recipient, $document) {
                $message->to($recipient->email, $recipient->name)
                    ->subject('OTP Verification - ' . $document->title);
            });

            return back()->with('success', 'A new OTP has been sent to your email.');
        } catch (\Exception $e) {
            \Log::error('Failed to resend OTP email', [
                'recipient' => $recipient->email,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to send OTP email. Please try again later.');
        }
    }

    /**
     * Serve PDF via token (public access)
     */
    public function servePdfByToken(string $token)
    {
        $recipient = DocumentRecipient::where('signature_token', $token)->firstOrFail();
        $document = $recipient->document;

        // Require OTP verification before serving PDF
        if (!$recipient->otp_verified_at) {
            abort(403, 'Please verify your email first');
        }

        $filePath = $document->signed_file_path ?: $document->file_path;
        
        // Try private disk first (where documents are uploaded)
        $fullPath = Storage::disk('private')->path($filePath);
        
        // If not found in private, try public disk
        if (!file_exists($fullPath)) {
            $fullPath = Storage::disk('public')->path($filePath);
        }

        if (!file_exists($fullPath)) {
            \Log::error('PDF file not found', [
                'path_tried_private' => Storage::disk('private')->path($filePath),
                'path_tried_public' => Storage::disk('public')->path($filePath),
                'document_id' => $document->id,
                'file_path' => $filePath
            ]);
            abort(404, 'PDF file not found: ' . $filePath);
        }

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $document->title . '.pdf"',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET',
        ]);
    }

    /**
     * Process signature by token
     */
    public function processSignByToken(Request $request, string $token)
    {
        $recipient = DocumentRecipient::where('signature_token', $token)->firstOrFail();

        // Require OTP verification before processing
        if (!$recipient->otp_verified_at) {
            return redirect()->route('documents.sign.token', $token)
                ->with('error', 'Please verify your email first.');
        }

        if (!$this->signingService->canAccess($recipient)) {
            return redirect()->route('documents.sign.token', $token)
                ->with('error', 'This document has already been processed.');
        }

        $validated = $request->validate([
            'action' => 'required|in:sign,view,reject',
            'qr_x' => 'required_if:action,sign|nullable|numeric',
            'qr_y' => 'required_if:action,sign|nullable|numeric',
            'qr_page' => 'required_if:action,sign|nullable|integer|min:1',
            'qr_width' => 'required_if:action,sign|nullable|numeric|min:10',
            'qr_height' => 'required_if:action,sign|nullable|numeric|min:10',
            'canvas_width' => 'required_if:action,sign|nullable|numeric',
        ]);

        $signatureData = null;
        if ($validated['action'] === 'sign') {
            $signatureData = [
                'qr_x' => $validated['qr_x'],
                'qr_y' => $validated['qr_y'],
                'qr_page' => $validated['qr_page'],
                'qr_width' => $validated['qr_width'],
                'qr_height' => $validated['qr_height'],
                'canvas_width' => $validated['canvas_width'],
            ];
        }

        $this->signingService->processRecipientAction(
            $recipient,
            $validated['action'],
            $signatureData
        );

        $message = match($validated['action']) {
            'sign' => 'Document signed successfully!',
            'view' => 'Document marked as viewed.',
            'reject' => 'Document rejected.',
        };

        return view('documents.sign-complete', [
            'message' => $message,
            'recipient' => $recipient,
            'document' => $recipient->document,
        ]);
    }
}
