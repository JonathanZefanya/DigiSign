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
}
