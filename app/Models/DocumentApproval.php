<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentApproval extends Model
{
    protected $fillable = [
        'document_id',
        'document_version_id',
        'sequence',
        'approver_id',
        'delegated_to',
        'status',
        'comments',
        'responded_at',
        'due_date',
        'is_overdue',
        'reminder_count',
        'last_reminder_at',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'is_overdue' => 'boolean',
        'reminder_count' => 'integer',
        'responded_at' => 'datetime',
        'due_date' => 'datetime',
        'last_reminder_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_SKIPPED = 'skipped';
    const STATUS_DELEGATED = 'delegated';

    /**
     * Get the document
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the document version
     */
    public function documentVersion(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class);
    }

    /**
     * Get the approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Get the delegate
     */
    public function delegate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegated_to');
    }

    /**
     * Get effective approver (delegate if delegated)
     */
    public function getEffectiveApproverAttribute()
    {
        return $this->delegated_to ? $this->delegate : $this->approver;
    }

    /**
     * Check if pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if overdue
     */
    public function checkOverdue(): bool
    {
        if (!$this->due_date || !$this->isPending()) {
            return false;
        }
        return $this->due_date->isPast();
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_SKIPPED => 'Dilewati',
            self::STATUS_DELEGATED => 'Didelegasikan',
            default => $this->status,
        };
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_APPROVED => 'lime',
            self::STATUS_REJECTED => 'red',
            self::STATUS_SKIPPED => 'gray',
            self::STATUS_DELEGATED => 'primary',
            default => 'gray',
        };
    }

    /**
     * Scope: Pending approvals
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: For approver
     */
    public function scopeForApprover($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('approver_id', $userId)
              ->orWhere('delegated_to', $userId);
        });
    }

    /**
     * Scope: Overdue
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                     ->where('due_date', '<', now());
    }
}
