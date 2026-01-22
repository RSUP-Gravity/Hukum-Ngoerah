<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'employee_id',
        'role_id',
        'unit_id',
        'position_id',
        'phone',
        'avatar',
        'is_active',
        'must_change_password',
        'last_login_at',
        'last_login_ip',
        'password_changed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the user's role
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the user's unit
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the user's position
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get documents created by this user
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'created_by');
    }

    /**
     * Get pending approvals for this user
     */
    public function pendingApprovals(): HasMany
    {
        return $this->hasMany(DocumentApproval::class, 'approver_id')
            ->where('status', 'pending');
    }

    /**
     * Get notifications for this user
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get unread notifications
     */
    public function unreadNotifications(): HasMany
    {
        return $this->hasMany(Notification::class)->where('is_read', false);
    }

    /**
     * Get audit logs for this user
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    // ==================== PERMISSIONS ====================

    /**
     * Get user's permissions (cached)
     */
    public function getPermissions(): array
    {
        if (!$this->role_id) {
            return [];
        }

        return Cache::remember(
            "user.{$this->id}.permissions",
            3600,
            fn () => $this->role->permissions->pluck('name')->toArray()
        );
    }

    /**
     * Check if user has permission
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->getPermissions());
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return !empty(array_intersect($permissions, $this->getPermissions()));
    }

    /**
     * Check if user has all given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        return empty(array_diff($permissions, $this->getPermissions()));
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->role && in_array($this->role->name, $roleNames);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->hasRole('super_admin');
    }

    /**
     * Check if user can approve documents
     */
    public function canApproveDocuments(): bool
    {
        return $this->position && $this->position->can_approve_documents;
    }

    /**
     * Clear permission cache
     */
    public function clearPermissionCache(): void
    {
        Cache::forget("user.{$this->id}.permissions");
    }

    // ==================== ACCESSORS ====================

    /**
     * Get the user's directorate through unit
     */
    public function getDirectorateAttribute()
    {
        return $this->unit?->directorate;
    }

    /**
     * Get avatar URL
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        // Generate initials avatar
        $initials = collect(explode(' ', $this->name))
            ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
            ->take(2)
            ->join('');

        return "https://ui-avatars.com/api/?name={$initials}&background=00A0B0&color=fff&size=128";
    }

    /**
     * Get role name
     */
    public function getRoleNameAttribute(): string
    {
        return $this->role?->display_name ?? 'Tidak ada role';
    }

    /**
     * Get unit name
     */
    public function getUnitNameAttribute(): string
    {
        return $this->unit?->name ?? 'Tidak ada unit';
    }

    /**
     * Get position name
     */
    public function getPositionNameAttribute(): string
    {
        return $this->position?->name ?? 'Tidak ada jabatan';
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Active users only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by role
     */
    public function scopeOfRole($query, $roleId)
    {
        return $query->where('role_id', $roleId);
    }

    /**
     * Scope: Filter by unit
     */
    public function scopeOfUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    /**
     * Scope: Search by name or username
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('username', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('employee_id', 'like', "%{$search}%");
        });
    }

    // ==================== HELPERS ====================

    /**
     * Record login
     */
    public function recordLogin(): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ]);
    }

    /**
     * Check if password needs to be changed
     */
    public function needsPasswordChange(): bool
    {
        if ($this->must_change_password) {
            return true;
        }

        // Check if password is older than 90 days
        if ($this->password_changed_at && $this->password_changed_at->diffInDays(now()) > 90) {
            return true;
        }

        return false;
    }
}
