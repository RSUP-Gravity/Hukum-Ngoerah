<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'prefix',
        'requires_approval',
        'has_expiry',
        'default_retention_days',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'has_expiry' => 'boolean',
        'is_active' => 'boolean',
        'default_retention_days' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get categories under this document type
     */
    public function categories(): HasMany
    {
        return $this->hasMany(DocumentCategory::class);
    }

    /**
     * Get documents of this type
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get templates for this type
     */
    public function templates(): HasMany
    {
        return $this->hasMany(DocumentTemplate::class);
    }

    /**
     * Get active categories
     */
    public function activeCategories(): HasMany
    {
        return $this->hasMany(DocumentCategory::class)->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Scope: Active types only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Order by sort order
     */
    public function scopeSorted($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope: Types requiring approval
     */
    public function scopeRequiresApproval($query)
    {
        return $query->where('requires_approval', true);
    }
}
