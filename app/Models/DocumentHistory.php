<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentHistory extends Model
{
    protected $table = 'document_history';

    protected $fillable = [
        'document_id',
        'document_version_id',
        'action',
        'old_status',
        'new_status',
        'notes',
        'changes',
        'performed_by',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    /**
     * Action constants
     */
    const ACTION_CREATED = 'created';
    const ACTION_UPDATED = 'updated';
    const ACTION_VERSION_UPLOADED = 'version_uploaded';
    const ACTION_SUBMITTED_REVIEW = 'submitted_for_review';
    const ACTION_REVIEWED = 'reviewed';
    const ACTION_SUBMITTED_APPROVAL = 'submitted_for_approval';
    const ACTION_APPROVED = 'approved';
    const ACTION_REJECTED = 'rejected';
    const ACTION_PUBLISHED = 'published';
    const ACTION_UNPUBLISHED = 'unpublished';
    const ACTION_ARCHIVED = 'archived';
    const ACTION_RESTORED = 'restored';
    const ACTION_DELETED = 'deleted';
    const ACTION_VIEWED = 'viewed';
    const ACTION_DOWNLOADED = 'downloaded';
    const ACTION_PRINTED = 'printed';
    const ACTION_SHARED = 'shared';
    const ACTION_LOCKED = 'locked';
    const ACTION_UNLOCKED = 'unlocked';
    const ACTION_COMMENT = 'comment_added';
    const ACTION_STATUS_CHANGED = 'status_changed';

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
     * Get the performer
     */
    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Get action label in Indonesian
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_CREATED => 'Dokumen dibuat',
            self::ACTION_UPDATED => 'Dokumen diperbarui',
            self::ACTION_VERSION_UPLOADED => 'Versi baru diunggah',
            self::ACTION_SUBMITTED_REVIEW => 'Dikirim untuk review',
            self::ACTION_REVIEWED => 'Direview',
            self::ACTION_SUBMITTED_APPROVAL => 'Dikirim untuk persetujuan',
            self::ACTION_APPROVED => 'Disetujui',
            self::ACTION_REJECTED => 'Ditolak',
            self::ACTION_PUBLISHED => 'Dipublikasi',
            self::ACTION_UNPUBLISHED => 'Dibatalkan publikasi',
            self::ACTION_ARCHIVED => 'Diarsipkan',
            self::ACTION_RESTORED => 'Dipulihkan',
            self::ACTION_DELETED => 'Dihapus',
            self::ACTION_VIEWED => 'Dilihat',
            self::ACTION_DOWNLOADED => 'Diunduh',
            self::ACTION_PRINTED => 'Dicetak',
            self::ACTION_SHARED => 'Dibagikan',
            self::ACTION_LOCKED => 'Dikunci',
            self::ACTION_UNLOCKED => 'Dibuka kunci',
            self::ACTION_COMMENT => 'Komentar ditambahkan',
            self::ACTION_STATUS_CHANGED => 'Status diubah',
            default => $this->action,
        };
    }

    /**
     * Get action icon
     */
    public function getActionIconAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_CREATED => 'plus-circle',
            self::ACTION_UPDATED => 'pencil',
            self::ACTION_VERSION_UPLOADED => 'arrow-up-tray',
            self::ACTION_SUBMITTED_REVIEW, self::ACTION_SUBMITTED_APPROVAL => 'paper-airplane',
            self::ACTION_REVIEWED => 'eye',
            self::ACTION_APPROVED => 'check-circle',
            self::ACTION_REJECTED => 'x-circle',
            self::ACTION_PUBLISHED => 'globe-alt',
            self::ACTION_ARCHIVED => 'archive-box',
            self::ACTION_DELETED => 'trash',
            self::ACTION_VIEWED => 'eye',
            self::ACTION_DOWNLOADED => 'arrow-down-tray',
            self::ACTION_LOCKED => 'lock-closed',
            self::ACTION_UNLOCKED => 'lock-open',
            default => 'information-circle',
        };
    }

    /**
     * Scope: Filter by action
     */
    public function scopeAction($query, $action)
    {
        if (is_array($action)) {
            return $query->whereIn('action', $action);
        }
        return $query->where('action', $action);
    }

    /**
     * Scope: Recent first
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
