<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'level',
        'can_approve_documents',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'can_approve_documents' => 'boolean',
        'is_active' => 'boolean',
        'level' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get users with this position
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Scope: Active positions only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Positions that can approve
     */
    public function scopeCanApprove($query)
    {
        return $query->where('can_approve_documents', true);
    }

    /**
     * Scope: Order by level
     */
    public function scopeByLevel($query, string $direction = 'desc')
    {
        return $query->orderBy('level', $direction);
    }

    /**
     * Scope: Order by sort order
     */
    public function scopeSorted($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
