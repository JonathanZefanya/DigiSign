<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'email',
        'name',
        'role',
        'status',
        'signing_order',
        'signature_token',
        'otp_code',
        'otp_expires_at',
        'otp_verified_at',
        'signed_at',
        'signature_data',
    ];

    protected function casts(): array
    {
        return [
            'signed_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'otp_verified_at' => 'datetime',
            'signing_order' => 'integer',
        ];
    }

    /**
     * Get the document this recipient belongs to
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Check if recipient is a signer
     */
    public function isSigner(): bool
    {
        return $this->role === 'SIGNER';
    }

    /**
     * Check if recipient is a viewer
     */
    public function isViewer(): bool
    {
        return $this->role === 'VIEWER';
    }

    /**
     * Check if recipient has signed
     */
    public function hasSigned(): bool
    {
        return $this->status === 'SIGNED';
    }

    /**
     * Check if recipient has viewed
     */
    public function hasViewed(): bool
    {
        return $this->status === 'VIEWED';
    }

    /**
     * Check if recipient has rejected
     */
    public function hasRejected(): bool
    {
        return $this->status === 'REJECTED';
    }

    /**
     * Mark as signed
     */
    public function markAsSigned(string $signatureData = null)
    {
        $this->update([
            'status' => 'SIGNED',
            'signed_at' => now(),
            'signature_data' => $signatureData,
        ]);
    }

    /**
     * Mark as viewed
     */
    public function markAsViewed()
    {
        $this->update([
            'status' => 'VIEWED',
            'signed_at' => now(),
        ]);
    }

    /**
     * Mark as rejected
     */
    public function markAsRejected()
    {
        $this->update([
            'status' => 'REJECTED',
            'signed_at' => now(),
        ]);
    }
}
