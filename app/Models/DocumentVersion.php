<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DocumentVersion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'document_id',
        'version_number',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'file_hash',
        'change_summary',
        'change_type',
        'is_current',
        'uploaded_by',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'file_size' => 'integer',
        'is_current' => 'boolean',
    ];

    /**
     * Change type constants
     */
    const CHANGE_INITIAL = 'initial';
    const CHANGE_MINOR = 'minor';
    const CHANGE_MAJOR = 'major';
    const CHANGE_CORRECTION = 'correction';

    /**
     * Get the document
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the uploader
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get file URL
     */
    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get human readable file size
     */
    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get version label
     */
    public function getVersionLabelAttribute(): string
    {
        return 'v' . $this->version_number;
    }

    /**
     * Get change type label
     */
    public function getChangeTypeLabelAttribute(): string
    {
        return match ($this->change_type) {
            self::CHANGE_INITIAL => 'Versi Awal',
            self::CHANGE_MINOR => 'Perubahan Minor',
            self::CHANGE_MAJOR => 'Perubahan Major',
            self::CHANGE_CORRECTION => 'Koreksi',
            default => $this->change_type,
        };
    }

    /**
     * Check if file exists
     */
    public function fileExists(): bool
    {
        return Storage::exists($this->file_path);
    }

    /**
     * Verify file integrity
     */
    public function verifyIntegrity(): bool
    {
        if (!$this->fileExists()) {
            return false;
        }
        
        $currentHash = hash_file('sha256', Storage::path($this->file_path));
        return $currentHash === $this->file_hash;
    }

    /**
     * Scope: Current versions only
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope: Order by version
     */
    public function scopeLatestVersion($query)
    {
        return $query->orderBy('version_number', 'desc');
    }
}
