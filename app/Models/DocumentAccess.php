<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentAccess extends Model
{
    protected $table = 'document_access';

    protected $fillable = [
        'document_id',
        'user_id',
        'role_id',
        'unit_id',
        'directorate_id',
        'permission',
        'valid_from',
        'valid_until',
        'granted_by',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    /**
     * Permission constants
     */
    const PERM_VIEW = 'view';
    const PERM_DOWNLOAD = 'download';
    const PERM_EDIT = 'edit';
    const PERM_DELETE = 'delete';
    const PERM_APPROVE = 'approve';
    const PERM_FULL = 'full';

    const PERMISSIONS = [
        self::PERM_VIEW => 'Lihat',
        self::PERM_DOWNLOAD => 'Unduh',
        self::PERM_EDIT => 'Edit',
        self::PERM_DELETE => 'Hapus',
        self::PERM_APPROVE => 'Setujui',
        self::PERM_FULL => 'Penuh',
    ];

    /**
     * Get the document
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the role
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the unit
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the directorate
     */
    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class);
    }

    /**
     * Get the granter
     */
    public function granter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * Alias for granter (for views compatibility)
     */
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * Get expires_at attribute from valid_until
     */
    public function getExpiresAtAttribute()
    {
        return $this->valid_until;
    }

    /**
     * Get access_level attribute from permission
     */
    public function getAccessLevelAttribute()
    {
        return $this->permission;
    }

    /**
     * Check if access is valid now
     */
    public function isValid(): bool
    {
        $now = now();
        
        if ($this->valid_from && $this->valid_from->isFuture()) {
            return false;
        }
        
        if ($this->valid_until && $this->valid_until->isPast()) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if permission includes another
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->permission === self::PERM_FULL) {
            return true;
        }
        
        $hierarchy = [
            self::PERM_VIEW => 0,
            self::PERM_DOWNLOAD => 1,
            self::PERM_EDIT => 2,
            self::PERM_DELETE => 3,
            self::PERM_APPROVE => 4,
        ];
        
        $currentLevel = $hierarchy[$this->permission] ?? -1;
        $requestedLevel = $hierarchy[$permission] ?? 999;
        
        return $currentLevel >= $requestedLevel;
    }

    /**
     * Get permission label
     */
    public function getPermissionLabelAttribute(): string
    {
        return match ($this->permission) {
            self::PERM_VIEW => 'Lihat',
            self::PERM_DOWNLOAD => 'Unduh',
            self::PERM_EDIT => 'Edit',
            self::PERM_DELETE => 'Hapus',
            self::PERM_APPROVE => 'Setujui',
            self::PERM_FULL => 'Penuh',
            default => $this->permission,
        };
    }

    /**
     * Scope: Valid access only
     */
    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('valid_from')
              ->orWhere('valid_from', '<=', now());
        })->where(function ($q) {
            $q->whereNull('valid_until')
              ->orWhere('valid_until', '>=', now());
        });
    }

    /**
     * Scope: For user
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('role_id', $user->role_id)
              ->orWhere('unit_id', $user->unit_id)
              ->orWhere('directorate_id', $user->unit?->directorate_id);
        });
    }
}
