<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentType;
use App\Models\Unit;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Service for optimized document queries with caching
 */
class DocumentQueryService
{
    /**
     * Cache TTL in seconds (5 minutes)
     */
    protected const CACHE_TTL = 300;

    /**
     * Build query with filters applied
     */
    public function buildQuery(Request $request, $user): Builder
    {
        $query = Document::with(['type', 'category', 'unit', 'creator']);

        // Apply access control unless user is admin
        if (!$user->isAdmin()) {
            $query->accessibleBy($user);
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by type
        if ($request->filled('type_id')) {
            $query->where('document_type_id', $request->type_id);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('document_category_id', $request->category_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Filter by unit
        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('document_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('document_date', '<=', $request->date_to);
        }

        // Filter expired
        if ($request->boolean('expired')) {
            $query->expired();
        }

        // Filter expiring soon
        if ($request->filled('expiring_days')) {
            $query->expiringSoon($request->expiring_days);
        }

        return $query;
    }

    /**
     * Apply sorting to query
     */
    public function applySort(Builder $query, Request $request): Builder
    {
        $sortFields = explode(',', $request->input('sort', 'updated_at'));
        $sortDirs = explode(',', $request->input('dir', 'desc'));

        foreach ($sortFields as $index => $field) {
            $field = trim($field);
            $dir = isset($sortDirs[$index]) ? trim($sortDirs[$index]) : 'asc';
            $dir = in_array(strtolower($dir), ['asc', 'desc']) ? strtolower($dir) : 'asc';
            
            $allowedFields = ['document_number', 'title', 'document_date', 'updated_at', 'created_at', 'expiry_date', 'status'];
            if (in_array($field, $allowedFields)) {
                $query->orderBy($field, $dir);
            }
        }

        return $query;
    }

    /**
     * Get documents with regular pagination
     */
    public function getPaginated(Request $request, $user, int $perPage = 20)
    {
        $query = $this->buildQuery($request, $user);
        $query = $this->applySort($query, $request);

        return $query->paginate($perPage);
    }

    /**
     * Get documents with cursor pagination for large datasets
     * More efficient for large datasets as it doesn't count total rows
     */
    public function getCursorPaginated(Request $request, $user, int $perPage = 20): CursorPaginator
    {
        $query = $this->buildQuery($request, $user);
        
        // For cursor pagination, we need a unique, ordered column
        // Using id as the cursor key with the sort applied
        $query->orderBy('id', 'desc');

        return $query->cursorPaginate($perPage);
    }

    /**
     * Generate cache key for filter combination
     */
    protected function generateCacheKey(Request $request, $user): string
    {
        $params = [
            'user_id' => $user->id,
            'is_admin' => $user->isAdmin(),
            'search' => $request->input('search'),
            'type_id' => $request->input('type_id'),
            'category_id' => $request->input('category_id'),
            'status' => $request->input('status'),
            'unit_id' => $request->input('unit_id'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'expired' => $request->boolean('expired'),
            'expiring_days' => $request->input('expiring_days'),
        ];

        return 'documents:query:' . md5(serialize($params));
    }

    /**
     * Get cached filter results count
     */
    public function getCachedCount(Request $request, $user): int
    {
        $cacheKey = $this->generateCacheKey($request, $user) . ':count';

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($request, $user) {
            return $this->buildQuery($request, $user)->count();
        });
    }

    /**
     * Get cached filter results (for small result sets)
     */
    public function getCachedResults(Request $request, $user, int $limit = 100)
    {
        $cacheKey = $this->generateCacheKey($request, $user) . ':results:' . $limit;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($request, $user, $limit) {
            $query = $this->buildQuery($request, $user);
            $query = $this->applySort($query, $request);
            return $query->limit($limit)->get();
        });
    }

    /**
     * Get cached status counts for filters
     */
    public function getCachedStatusCounts($user): array
    {
        $cacheKey = 'documents:status_counts:' . ($user->isAdmin() ? 'admin' : 'user:' . $user->id);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            $query = Document::query();
            
            if (!$user->isAdmin()) {
                $query->accessibleBy($user);
            }

            $counts = [
                'total' => $query->count(),
                'active' => (clone $query)->byStatus('active')->count(),
                'attention' => (clone $query)->byStatus('attention')->count(),
                'warning' => (clone $query)->byStatus('warning')->count(),
                'critical' => (clone $query)->byStatus('critical')->count(),
                'expired' => (clone $query)->expired()->count(),
            ];

            return $counts;
        });
    }

    /**
     * Clear document query cache
     */
    public function clearCache(): void
    {
        Cache::flush(); // In production, use tags: Cache::tags('documents')->flush()
    }

    /**
     * Get filter options with lazy loading support
     */
    public function getFilterOptions(string $type, ?string $search = null, int $limit = 50): array
    {
        $cacheKey = "filter_options:{$type}:" . md5($search ?? '') . ":{$limit}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($type, $search, $limit) {
            switch ($type) {
                case 'types':
                    $query = DocumentType::active()->sorted();
                    if ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    }
                    return $query->limit($limit)->get(['id', 'name', 'code'])->toArray();

                case 'categories':
                    $query = DocumentCategory::active()->sorted();
                    if ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    }
                    return $query->limit($limit)->get(['id', 'name', 'code'])->toArray();

                case 'units':
                    $query = Unit::with('directorate:id,name')->active()->sorted();
                    if ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    }
                    return $query->limit($limit)->get(['id', 'name', 'code', 'directorate_id'])->toArray();

                default:
                    return [];
            }
        });
    }
}
