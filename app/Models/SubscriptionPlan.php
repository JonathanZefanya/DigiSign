<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'storage_limit_mb',
        'max_documents_per_month',
        'max_categories',
        'price',
        'is_default',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'storage_limit_mb' => 'integer',
            'max_documents_per_month' => 'integer',
            'max_categories' => 'integer',
            'price' => 'decimal:2',
            'is_default' => 'boolean',
        ];
    }

    /**
     * Get all users subscribed to this plan
     */
    public function users()
    {
        return $this->hasMany(User::class, 'current_plan_id');
    }

    /**
     * Check if this is the default plan
     */
    public function isDefault(): bool
    {
        return $this->is_default === true;
    }

    /**
     * Get the storage limit in KB
     */
    public function getStorageLimitKbAttribute(): int
    {
        return $this->storage_limit_mb * 1024;
    }
}
