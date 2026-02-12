<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'sso_id',
        'avatar',
        'is_active',
        'current_plan_id',
        'storage_used_kb',
        'documents_count_current_month',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'storage_used_kb' => 'integer',
            'documents_count_current_month' => 'integer',
        ];
    }

    /**
     * Boot the model and auto-assign default plan
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->current_plan_id)) {
                $defaultPlan = SubscriptionPlan::where('is_default', true)->first();
                if ($defaultPlan) {
                    $user->current_plan_id = $defaultPlan->id;
                }
            }
        });
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'current_plan_id');
    }

    /**
     * Check if user has exceeded storage quota
     */
    public function hasExceededStorageQuota(int $additionalKb = 0): bool
    {
        if (!$this->subscriptionPlan) {
            return false;
        }
        
        // -1 means unlimited
        if ($this->subscriptionPlan->storage_limit_mb == -1) {
            return false;
        }
        
        $totalUsage = $this->storage_used_kb + $additionalKb;
        $limit = $this->subscriptionPlan->storage_limit_mb * 1024;
        
        return $totalUsage > $limit;
    }

    /**
     * Check if user has exceeded document quota this month
     */
    public function hasExceededDocumentQuota(): bool
    {
        if (!$this->subscriptionPlan) {
            return false;
        }
        
        // -1 means unlimited
        if ($this->subscriptionPlan->max_documents_per_month == -1) {
            return false;
        }
        
        return $this->documents_count_current_month >= $this->subscriptionPlan->max_documents_per_month;
    }

    /**
     * Check if user has exceeded category quota
     */
    public function hasExceededCategoryQuota(): bool
    {
        if (!$this->subscriptionPlan) {
            return false;
        }
        
        // -1 means unlimited
        if ($this->subscriptionPlan->max_categories == -1) {
            return false;
        }
        
        return $this->categories()->count() >= $this->subscriptionPlan->max_categories;
    }

    /**
     * Get storage usage percentage
     */
    public function getStorageUsagePercentage(): float
    {
        if (!$this->subscriptionPlan || $this->subscriptionPlan->storage_limit_mb == 0 || $this->subscriptionPlan->storage_limit_mb == -1) {
            return 0;
        }
        
        $limit = $this->subscriptionPlan->storage_limit_mb * 1024;
        return min(100, ($this->storage_used_kb / $limit) * 100);
    }

    /**
     * Get document usage percentage this month
     */
    public function getDocumentUsagePercentage(): float
    {
        if (!$this->subscriptionPlan || $this->subscriptionPlan->max_documents_per_month == 0 || $this->subscriptionPlan->max_documents_per_month == -1) {
            return 0;
        }
        
        return min(100, ($this->documents_count_current_month / $this->subscriptionPlan->max_documents_per_month) * 100);
    }

    /**
     * Increment storage usage
     */
    public function incrementStorageUsage(int $kb)
    {
        $this->increment('storage_used_kb', $kb);
    }

    /**
     * Decrement storage usage
     */
    public function decrementStorageUsage(int $kb)
    {
        $this->decrement('storage_used_kb', $kb);
    }

    /**
     * Increment document count for current month
     */
    public function incrementDocumentCount()
    {
        $this->increment('documents_count_current_month');
    }

    /**
     * Decrement document count for current month
     */
    public function decrementDocumentCount()
    {
        if ($this->documents_count_current_month > 0) {
            $this->decrement('documents_count_current_month');
        }
    }

    /**
     * Reset monthly document counter
     */
    public function resetMonthlyDocumentCount()
    {
        $this->update(['documents_count_current_month' => 0]);
    }
}
