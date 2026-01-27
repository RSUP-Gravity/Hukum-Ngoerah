<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'document_number',
        'title',
        'description',
        'document_type_id',
        'document_category_id',
        'directorate_id',
        'unit_id',
        'effective_date',
        'expiry_date',
        'review_date',
        'retention_days',
        'status',
        'rejection_reason',
        'current_version',
        'download_count',
        'is_locked',
        'confidentiality',
        'keywords',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
        'published_by',
        'published_at',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'review_date' => 'date',
        'is_locked' => 'boolean',
        'retention_days' => 'integer',
        'current_version' => 'integer',
        'download_count' => 'integer',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_REVIEW = 'pending_review';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_PUBLISHED = 'published';
    const STATUS_EXPIRED = 'expired';
    const STATUS_ARCHIVED = 'archived';
    const STATUS_REJECTED = 'rejected';

    /**
     * All statuses with labels
     */
    const STATUSES = [
        'draft' => 'Draft',
        'pending_review' => 'Menunggu Review',
        'pending_approval' => 'Menunggu Persetujuan',
        'approved' => 'Disetujui',
        'published' => 'Dipublikasikan',
        'expired' => 'Kadaluarsa',
        'archived' => 'Diarsipkan',
        'rejected' => 'Ditolak',
    ];

    /**
     * Confidentiality constants
     */
    const CONF_PUBLIC = 'public';
    const CONF_INTERNAL = 'internal';
    const CONF_CONFIDENTIAL = 'confidential';
    const CONF_RESTRICTED = 'restricted';

    /**
     * Confidentiality labels
     */
    const CONFIDENTIALITIES = [
        self::CONF_PUBLIC => 'Publik',
        self::CONF_INTERNAL => 'Internal',
        self::CONF_CONFIDENTIAL => 'Rahasia',
        self::CONF_RESTRICTED => 'Terbatas',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($document) {
            if (!$document->created_by) {
                $document->created_by = Auth::id();
            }
        });

        static::updating(function ($document) {
            $document->updated_by = Auth::id();
        });
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the document type
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    /**
     * Alias for documentType relationship
     */
    public function type(): BelongsTo
    {
        return $this->documentType();
    }

    /**
     * Get the document category
     */
    public function documentCategory(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class);
    }

    /**
     * Alias for documentCategory relationship
     */
    public function category(): BelongsTo
    {
        return $this->documentCategory();
    }

    /**
     * Get the directorate
     */
    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class);
    }

    /**
     * Get the unit
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the last updater
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the publisher
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    /**
     * Get all versions
     */
    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)->orderBy('version_number', 'desc');
    }

    /**
     * Get current version
     */
    public function currentVersion(): HasOne
    {
        return $this->hasOne(DocumentVersion::class)->where('is_current', true);
    }

    /**
     * Get current version (legacy alias)
     */
    public function currentVersionRelation(): HasOne
    {
        return $this->currentVersion();
    }

    /**
     * Get history
     */
    public function history(): HasMany
    {
        return $this->hasMany(DocumentHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get approvals
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(DocumentApproval::class)->orderBy('sequence');
    }

    /**
     * Get latest approval (approved or rejected)
     */
    public function latestApproval(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(DocumentApproval::class)
            ->whereIn('status', [DocumentApproval::STATUS_APPROVED, DocumentApproval::STATUS_REJECTED])
            ->latest('responded_at');
    }

    /**
     * Get document access permissions
     */
    public function accessPermissions(): HasMany
    {
        return $this->hasMany(DocumentAccess::class, 'document_id');
    }

    /**
     * Get access controls
     */
    public function accessControls(): HasMany
    {
        return $this->hasMany(DocumentAccess::class);
    }

    // ==================== DOWNLOAD TRACKING ====================

    /**
     * Increment download count
     */
    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }

    /**
     * Get formatted download count
     */
    public function getFormattedDownloadCountAttribute(): string
    {
        $count = $this->download_count ?? 0;
        
        if ($count >= 1000000) {
            return round($count / 1000000, 1) . 'M';
        }
        
        if ($count >= 1000) {
            return round($count / 1000, 1) . 'K';
        }
        
        return (string) $count;
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Filter by status
     */
    public function scopeStatus($query, $status)
    {
        if (is_array($status)) {
            return $query->whereIn('status', $status);
        }
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by status (alias)
     */
    public function scopeByStatus($query, $status)
    {
        return $this->scopeStatus($query, $status);
    }

    /**
     * Scope: Published documents
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Scope: Active documents
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', self::STATUS_ARCHIVED);
    }

    /**
     * Scope: Draft documents
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope: Expired documents
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    /**
     * Scope: Expiring soon (within days)
     */
    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereBetween('expiry_date', [now(), now()->addDays($days)]);
    }

    /**
     * Scope: Need review
     */
    public function scopeNeedsReview($query)
    {
        return $query->where('review_date', '<=', now());
    }

    /**
     * Scope: Filter by confidentiality
     */
    public function scopeConfidentiality($query, $level)
    {
        return $query->where('confidentiality', $level);
    }

    /**
     * Scope: Filter by type
     */
    public function scopeOfType($query, $typeId)
    {
        return $query->where('document_type_id', $typeId);
    }

    /**
     * Scope: Filter by directorate
     */
    public function scopeOfDirectorate($query, $directorateId)
    {
        return $query->where('directorate_id', $directorateId);
    }

    /**
     * Scope: Filter by unit
     */
    public function scopeOfUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    /**
     * Scope: Created by user
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope: Search by keyword
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('document_number', 'like', "%{$search}%")
              ->orWhere('title', 'like', "%{$search}%")
              ->orWhere('keywords', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope: Filter documents accessible by user
     * Based on user's unit, directorate, or role
     */
    public function scopeAccessibleBy($query, $user)
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        // Admin or view-all can see all documents
        if ($user->isAdmin() || $user->hasPermission('documents.view_all')) {
            return $query;
        }

        // Viewer can only see published documents
        if ($user->hasRole('viewer')) {
            return $query->where('status', self::STATUS_PUBLISHED);
        }

        return $query->where(function ($q) use ($user) {
            // Documents in user's unit
            if ($user->unit_id) {
                $q->orWhere('unit_id', $user->unit_id);
            }
            
            // Documents in user's directorate (if user has directorate access)
            if ($user->directorate_id) {
                $q->orWhere('directorate_id', $user->directorate_id);
            }
            
            // Public documents (published status)
            $q->orWhere('status', self::STATUS_PUBLISHED);
            
            // Documents created by user
            $q->orWhere('created_by', $user->id);
        });
    }

    // ==================== HELPERS ====================

    /**
     * Check if document is editable
     */
    public function isEditable(): bool
    {
        return !$this->is_locked && in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_REJECTED,
        ]);
    }

    /**
     * Check if document can be submitted for review
     */
    public function canSubmitForReview(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_REJECTED,
        ]) && $this->currentVersionRelation;
    }

    /**
     * Check if document is expired
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if document is expiring soon
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiry_date && $this->expiry_date->between(now(), now()->addDays($days));
    }

    /**
     * Get status label in Indonesian
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draf',
            self::STATUS_PENDING_REVIEW => 'Menunggu Review',
            self::STATUS_PENDING_APPROVAL => 'Menunggu Persetujuan',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_PUBLISHED => 'Dipublikasi',
            self::STATUS_EXPIRED => 'Kadaluarsa',
            self::STATUS_ARCHIVED => 'Diarsipkan',
            self::STATUS_REJECTED => 'Ditolak',
            default => $this->status,
        };
    }

    /**
     * Get status color for badge
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_PENDING_REVIEW => 'yellow',
            self::STATUS_PENDING_APPROVAL => 'orange',
            self::STATUS_APPROVED => 'lime',
            self::STATUS_PUBLISHED => 'primary',
            self::STATUS_EXPIRED => 'red',
            self::STATUS_ARCHIVED => 'gray',
            self::STATUS_REJECTED => 'red',
            default => 'gray',
        };
    }

    /**
     * Get confidentiality label
     */
    public function getConfidentialityLabelAttribute(): string
    {
        return match ($this->confidentiality) {
            self::CONF_PUBLIC => 'Publik',
            self::CONF_INTERNAL => 'Internal',
            self::CONF_CONFIDENTIAL => 'Rahasia',
            self::CONF_RESTRICTED => 'Terbatas',
            default => $this->confidentiality,
        };
    }

    /**
     * Check if document is accessible by the given user
     */
    public function isAccessibleBy($user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->isAdmin() || $user->hasPermission('documents.view_all')) {
            return true;
        }

        if ($user->hasRole('viewer')) {
            return $this->status === self::STATUS_PUBLISHED;
        }

        if ($this->created_by === $user->id) {
            return true;
        }

        if ($this->status === self::STATUS_PUBLISHED) {
            return true;
        }

        if ($user->unit_id && $this->unit_id === $user->unit_id) {
            return true;
        }

        if ($user->directorate_id && $this->directorate_id === $user->directorate_id) {
            return true;
        }

        return false;
    }

    /**
     * Check if user has specific access level for this document
     */
    public function hasAccess(?User $user, string $permission = DocumentAccess::PERM_VIEW): bool
    {
        if (!$user) {
            return false;
        }

        $permission = $permission ?: DocumentAccess::PERM_VIEW;

        if ($user->isAdmin() || $user->hasPermission('documents.view_all')) {
            return true;
        }

        if ($this->created_by === $user->id) {
            return true;
        }

        $explicitAccess = $this->accessPermissions()
            ->valid()
            ->forUser($user)
            ->get()
            ->contains(fn (DocumentAccess $access) => $access->hasPermission($permission));

        if ($explicitAccess) {
            return true;
        }

        if (in_array($permission, [DocumentAccess::PERM_VIEW, DocumentAccess::PERM_DOWNLOAD], true)) {
            return $this->isAccessibleBy($user);
        }

        return false;
    }
}
