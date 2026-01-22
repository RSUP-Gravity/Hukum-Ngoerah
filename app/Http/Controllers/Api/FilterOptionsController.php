<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DocumentQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API controller for lazy loading filter options
 */
class FilterOptionsController extends Controller
{
    protected DocumentQueryService $queryService;

    public function __construct(DocumentQueryService $queryService)
    {
        $this->queryService = $queryService;
    }

    /**
     * Get document types for filter dropdown
     */
    public function types(Request $request): JsonResponse
    {
        $search = $request->input('search');
        $limit = min($request->input('limit', 50), 100);

        $types = $this->queryService->getFilterOptions('types', $search, $limit);

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    /**
     * Get document categories for filter dropdown
     */
    public function categories(Request $request): JsonResponse
    {
        $search = $request->input('search');
        $limit = min($request->input('limit', 50), 100);

        $categories = $this->queryService->getFilterOptions('categories', $search, $limit);

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get units for filter dropdown
     */
    public function units(Request $request): JsonResponse
    {
        $search = $request->input('search');
        $limit = min($request->input('limit', 50), 100);

        $units = $this->queryService->getFilterOptions('units', $search, $limit);

        return response()->json([
            'success' => true,
            'data' => $units,
        ]);
    }

    /**
     * Get status counts for filter badges
     */
    public function statusCounts(Request $request): JsonResponse
    {
        $user = auth()->user();
        $counts = $this->queryService->getCachedStatusCounts($user);

        return response()->json([
            'success' => true,
            'data' => $counts,
        ]);
    }

    /**
     * Get all filter options at once (for initial page load)
     */
    public function all(Request $request): JsonResponse
    {
        $types = $this->queryService->getFilterOptions('types', null, 100);
        $categories = $this->queryService->getFilterOptions('categories', null, 100);
        $units = $this->queryService->getFilterOptions('units', null, 100);

        $user = auth()->user();
        $statusCounts = $this->queryService->getCachedStatusCounts($user);

        return response()->json([
            'success' => true,
            'data' => [
                'types' => $types,
                'categories' => $categories,
                'units' => $units,
                'statusCounts' => $statusCounts,
            ],
        ]);
    }
}
