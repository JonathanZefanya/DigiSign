<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentRecipient;
use App\Services\DynamicMailService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DocumentSigningService
{
    /**
     * Add recipients to a document
     */
    public function addRecipients(Document $document, array $recipients): void
    {
        foreach ($recipients as $index => $recipient) {
            DocumentRecipient::create([
                'document_id' => $document->id,
                'email' => $recipient['email'],
                'name' => $recipient['name'] ?? null,
                'role' => $recipient['role'] ?? 'SIGNER',
                'signing_order' => $index + 1,
                'signature_token' => Str::random(64),
                'status' => 'PENDING',
            ]);
        }
    }

    /**
     * Send invitation emails to recipients
     */
    public function sendInvitations(Document $document, bool $skipCurrentUser = false): void
    {
        // Configure dynamic SMTP (optional - don't fail if not configured)
        try {
            DynamicMailService::configureMail();
        } catch (\Exception $e) {
            \Log::warning('SMTP not configured, emails will not be sent', ['error' => $e->getMessage()]);
            return; // Skip email sending if SMTP not configured
        }

        $currentUserEmail = auth()->user()->email ?? null;

        foreach ($document->recipients as $recipient) {
            // Skip sending email to current user if requested (for self-signing)
            if ($skipCurrentUser && $currentUserEmail && $recipient->email === $currentUserEmail) {
                continue;
            }

            $this->sendInvitationEmail($document, $recipient);
        }
    }

    /**
     * Send invitation email to a single recipient
     */
    private function sendInvitationEmail(Document $document, DocumentRecipient $recipient): void
    {
        $action = $recipient->isSigner() ? 'sign' : 'view';
        $url = route('documents.sign.token', ['token' => $recipient->signature_token]);

        try {
            Mail::send('emails.document-invitation', [
                'document' => $document,
                'recipient' => $recipient,
                'action' => $action,
                'url' => $url,
            ], function ($message) use ($recipient, $document, $action) {
                $message->to($recipient->email, $recipient->name)
                    ->subject("Document {$action} request: {$document->title}");
            });
        } catch (\Exception $e) {
            \Log::warning('Failed to send invitation email (SMTP may not be configured)', [
                'recipient' => $recipient->email,
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);
            // Don't throw - document should still be created even if email fails
        }
    }

    /**
     * Process signature/view action
     */
    public function processRecipientAction(DocumentRecipient $recipient, string $action, $signatureData = null): void
    {
        if ($action === 'sign' && $recipient->isSigner()) {
            // If QR coordinates provided, place QR stamp on document
            if (is_array($signatureData)) {
                $this->placeRecipientQrStamp($recipient, $signatureData);
            }
            $recipient->markAsSigned(is_array($signatureData) ? json_encode($signatureData) : $signatureData);
        } elseif ($action === 'view' && $recipient->isViewer()) {
            $recipient->markAsViewed();
        } elseif ($action === 'reject') {
            $recipient->markAsRejected();
        }

        // Update document status
        $recipient->document->updateStatusFromRecipients();
    }

    /**
     * Place recipient QR stamp on document
     */
    private function placeRecipientQrStamp(DocumentRecipient $recipient, array $qrData): void
    {
        $document = $recipient->document;
        
        // Generate QR code for recipient signature verification
        $verificationData = json_encode([
            'type' => 'recipient_signature',
            'document_id' => $document->id,
            'recipient_id' => $recipient->id,
            'recipient_email' => $recipient->email,
            'recipient_name' => $recipient->name,
            'signed_at' => now()->toIso8601String(),
        ]);
        
        $qrImageContent = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
            ->size(300)
            ->margin(1)
            ->generate($verificationData);

        $qrTempPath = storage_path('app/temp_qr_recipient_' . $recipient->id . '.png');
        file_put_contents($qrTempPath, $qrImageContent);

        try {
            // Use FPDI to stamp QR onto PDF
            $pdf = new \setasign\Fpdi\Fpdi();
            
            // Use signed file if exists, otherwise use original
            $sourcePath = $document->signed_file_path 
                ? \Storage::disk('public')->path($document->signed_file_path)
                : \Storage::disk('public')->path($document->file_path);
            
            $pageCount = $pdf->setSourceFile($sourcePath);
            $targetPage = min((int) $qrData['qr_page'], $pageCount);

            for ($i = 1; $i <= $pageCount; $i++) {
                $templateId = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($templateId);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);

                // Place QR code on the target page
                if ($i === $targetPage) {
                    $scale = $size['width'] / ($qrData['canvas_width'] ?? $size['width']);
                    $qrX = $qrData['qr_x'] * $scale;
                    $qrY = $qrData['qr_y'] * $scale;
                    $qrW = $qrData['qr_width'] * $scale;
                    $qrH = $qrData['qr_height'] * $scale;

                    $pdf->Image($qrTempPath, $qrX, $qrY, $qrW, $qrH);
                }
            }

            // Save signed PDF (overwrite or create new version)
            if ($document->signed_file_path) {
                // Append to existing signed file
                $signedFullPath = \Storage::disk('public')->path($document->signed_file_path);
            } else {
                // Create new signed version
                $signedPath = 'documents/signed/' . \Str::uuid() . '.pdf';
                $signedFullPath = \Storage::disk('public')->path($signedPath);
                
                $dir = dirname($signedFullPath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                
                $document->update(['signed_file_path' => $signedPath]);
            }

            $pdf->Output('F', $signedFullPath);

        } finally {
            // Clean up temp QR
            if (file_exists($qrTempPath)) {
                unlink($qrTempPath);
            }
        }
    }

    /**
     * Create self-signing document (Sign Myself scenario)
     */
    public function createSelfSigningDocument(Document $document): DocumentRecipient
    {
        $user = auth()->user();

        $recipient = DocumentRecipient::create([
            'document_id' => $document->id,
            'email' => $user->email,
            'name' => $user->name,
            'role' => 'SIGNER',
            'signing_order' => 1,
            'signature_token' => Str::random(64),
            'status' => 'PENDING',
        ]);

        return $recipient;
    }

    /**
     * Check if recipient can access document
     */
    public function canAccess(DocumentRecipient $recipient): bool
    {
        return $recipient->status === 'PENDING';
    }
}
