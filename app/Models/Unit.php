<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'directorate_id',
        'code',
        'name',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the directorate this unit belongs to
     */
    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class);
    }

    /**
     * Get users in this unit
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get documents owned by this unit
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Scope: Active units only
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
     * Scope: Filter by directorate
     */
    public function scopeOfDirectorate($query, $directorateId)
    {
        return $query->where('directorate_id', $directorateId);
    }

    /**
     * Get full name with directorate
     */
    public function getFullNameAttribute(): string
    {
        return $this->directorate ? "{$this->directorate->name} - {$this->name}" : $this->name;
    }
}
