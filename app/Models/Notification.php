<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'entity_type',
        'entity_id',
        'action_url',
        'priority',
        'is_read',
        'read_at',
        'email_sent',
        'email_sent_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'email_sent' => 'boolean',
        'read_at' => 'datetime',
        'email_sent_at' => 'datetime',
    ];

    /**
     * Type constants
     */
    const TYPE_DOCUMENT_APPROVAL = 'document_approval';
    const TYPE_DOCUMENT_SUBMITTED = 'document_submitted';
    const TYPE_APPROVAL_REQUIRED = 'approval_required';
    const TYPE_APPROVAL_APPROVED = 'approval_approved';
    const TYPE_APPROVAL_REJECTED = 'approval_rejected';
    const TYPE_DOCUMENT_EXPIRED = 'document_expired';
    const TYPE_DOCUMENT_EXPIRING = 'document_expiring';
    const TYPE_REMINDER = 'reminder';
    const TYPE_SYSTEM = 'system';

    /**
     * Priority constants
     */
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark as unread
     */
    public function markAsUnread(): void
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_DOCUMENT_APPROVAL => 'Persetujuan Dokumen',
            self::TYPE_DOCUMENT_SUBMITTED => 'Dokumen Diajukan',
            self::TYPE_APPROVAL_REQUIRED => 'Persetujuan Diperlukan',
            self::TYPE_APPROVAL_APPROVED => 'Dokumen Disetujui',
            self::TYPE_APPROVAL_REJECTED => 'Dokumen Ditolak',
            self::TYPE_DOCUMENT_EXPIRED => 'Dokumen Kadaluarsa',
            self::TYPE_DOCUMENT_EXPIRING => 'Dokumen Akan Kadaluarsa',
            self::TYPE_REMINDER => 'Pengingat',
            self::TYPE_SYSTEM => 'Sistem',
            default => $this->type,
        };
    }

    /**
     * Get priority label
     */
    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_LOW => 'Rendah',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH => 'Tinggi',
            self::PRIORITY_URGENT => 'Mendesak',
            default => $this->priority,
        };
    }

    /**
     * Get priority color
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_LOW => 'gray',
            self::PRIORITY_NORMAL => 'primary',
            self::PRIORITY_HIGH => 'orange',
            self::PRIORITY_URGENT => 'red',
            default => 'gray',
        };
    }

    /**
     * Scope: Unread only
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: For user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: By type
     */
    public function scopeType($query, $type)
    {
        if (is_array($type)) {
            return $query->whereIn('type', $type);
        }
        return $query->where('type', $type);
    }

    /**
     * Scope: By priority
     */
    public function scopePriority($query, $priority)
    {
        if (is_array($priority)) {
            return $query->whereIn('priority', $priority);
        }
        return $query->where('priority', $priority);
    }

    /**
     * Scope: Recent first
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
