<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'original_filename',
        'file_path',
        'signed_file_path',
        'document_hash',
        'status',
        'qr_position',
        'signed_at',
    ];

    protected function casts(): array
    {
        return [
            'qr_position' => 'array',
            'signed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isSigned(): bool
    {
        return $this->status === 'signed';
    }

    public function getVerificationUrl(): string
    {
        return route('verify.show', $this->document_hash);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function recipients()
    {
        return $this->hasMany(DocumentRecipient::class);
    }

    public function signers()
    {
        return $this->recipients()->where('role', 'SIGNER');
    }

    public function viewers()
    {
        return $this->recipients()->where('role', 'VIEWER');
    }

    /**
     * Check if all signers have signed
     */
    public function allSignersSigned(): bool
    {
        $signers = $this->signers()->get();
        
        if ($signers->isEmpty()) {
            return false;
        }
        
        return $signers->every(function ($signer) {
            return $signer->status === 'SIGNED';
        });
    }

    /**
     * Check if any signer has rejected
     */
    public function anySignerRejected(): bool
    {
        return $this->signers()->where('status', 'REJECTED')->exists();
    }

    /**
     * Update document status based on recipient statuses
     */
    public function updateStatusFromRecipients()
    {
        if ($this->anySignerRejected()) {
            $this->update(['status' => 'rejected']);
        } elseif ($this->allSignersSigned()) {
            $this->update([
                'status' => 'signed',
                'signed_at' => now(),
            ]);
        } elseif ($this->signers()->where('status', 'SIGNED')->exists()) {
            $this->update(['status' => 'partial']);
        }
    }

    /**
     * Get file size in KB
     */
    public function getFileSizeKb(): int
    {
        $path = storage_path('app/' . $this->file_path);
        if (file_exists($path)) {
            return (int) ceil(filesize($path) / 1024);
        }
        return 0;
    }
}

