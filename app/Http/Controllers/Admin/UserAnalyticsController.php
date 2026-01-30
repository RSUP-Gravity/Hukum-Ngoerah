<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentHistory;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class UserAnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:admin.user_analytics');
    }

    /**
     * Display user analytics dashboard.
     */
    public function index(Request $request)
    {
        $currentUser = $request->user();
        $isSuperAdmin = $currentUser->hasRole('super_admin');

        $dateFrom = $this->parseDate($request->input('date_from'), true);
        $dateTo = $this->parseDate($request->input('date_to'), false);

        $allowedRoleNames = $isSuperAdmin
            ? Role::active()
                ->whereNotIn('name', ['super_admin', 'viewer'])
                ->pluck('name')
                ->toArray()
            : ['legal_staff', 'unit_staff'];

        $roles = Role::active()
            ->whereIn('name', $allowedRoleNames)
            ->byLevel()
            ->get();

        $units = Unit::active()->sorted()->get();

        $userBaseQuery = User::query()
            ->when($request->filled('search'), fn ($q) => $q->search($request->search))
            ->when($request->filled('unit_id'), fn ($q) => $q->where('unit_id', $request->unit_id))
            ->when($request->filled('role_id'), fn ($q) => $q->where('role_id', $request->role_id))
            ->when($request->filled('active') && $request->active !== '', fn ($q) => $q->where('is_active', $request->boolean('active')))
            ->whereHas('role', fn ($q) => $q->whereIn('name', $allowedRoleNames));

        $filteredUserIds = (clone $userBaseQuery)->pluck('users.id');

        $applyDocumentDateFilter = function ($query) use ($dateFrom, $dateTo) {
            if ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->where('created_at', '<=', $dateTo);
            }
        };

        $revisionSubquery = DocumentHistory::query()
            ->selectRaw('COUNT(*)')
            ->join('documents', 'documents.id', '=', 'document_history.document_id')
            ->whereColumn('document_history.performed_by', 'users.id')
            ->where('document_history.action', DocumentHistory::ACTION_VERSION_ADDED)
            ->whereColumn('documents.created_by', 'users.id')
            ->when($dateFrom, fn ($q) => $q->where('document_history.created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->where('document_history.created_at', '<=', $dateTo));

        $users = (clone $userBaseQuery)
            ->with(['role', 'unit', 'position'])
            ->select('users.*')
            ->withCount([
                'documents as uploaded_count' => function ($q) use ($applyDocumentDateFilter) {
                    $applyDocumentDateFilter($q);
                },
                'documents as approved_count' => function ($q) use ($applyDocumentDateFilter) {
                    $applyDocumentDateFilter($q);
                    $q->whereIn('status', [Document::STATUS_APPROVED, Document::STATUS_PUBLISHED]);
                },
                'documents as in_review_count' => function ($q) use ($applyDocumentDateFilter) {
                    $applyDocumentDateFilter($q);
                    $q->whereIn('status', [Document::STATUS_PENDING_REVIEW, Document::STATUS_PENDING_APPROVAL]);
                },
                'documents as rejected_count' => function ($q) use ($applyDocumentDateFilter) {
                    $applyDocumentDateFilter($q);
                    $q->where('status', Document::STATUS_REJECTED);
                },
            ])
            ->selectSub($revisionSubquery, 'revision_count')
            ->orderBy('name')
            ->paginate(20);

        $documentBaseQuery = Document::query()
            ->whereIn('created_by', $filteredUserIds);
        $applyDocumentDateFilter($documentBaseQuery);

        $summary = [
            'total_users' => $filteredUserIds->count(),
            'uploaded' => (clone $documentBaseQuery)->count(),
            'approved' => (clone $documentBaseQuery)
                ->whereIn('status', [Document::STATUS_APPROVED, Document::STATUS_PUBLISHED])
                ->count(),
            'in_review' => (clone $documentBaseQuery)
                ->whereIn('status', [Document::STATUS_PENDING_REVIEW, Document::STATUS_PENDING_APPROVAL])
                ->count(),
            'rejected' => (clone $documentBaseQuery)
                ->where('status', Document::STATUS_REJECTED)
                ->count(),
            'revisions' => DocumentHistory::query()
                ->whereIn('performed_by', $filteredUserIds)
                ->where('action', DocumentHistory::ACTION_VERSION_ADDED)
                ->when($dateFrom, fn ($q) => $q->where('document_history.created_at', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->where('document_history.created_at', '<=', $dateTo))
                ->count(),
        ];

        $chartRange = $this->resolveChartRange($dateFrom, $dateTo);
        $chartData = $this->buildChartData($filteredUserIds, $chartRange['from'], $chartRange['to']);

        return view('admin.user-analytics.index', [
            'users' => $users,
            'roles' => $roles,
            'units' => $units,
            'summary' => $summary,
            'chartData' => $chartData,
            'chartRangeLabel' => $chartRange['label'],
        ]);
    }

    /**
     * Show user analytics detail.
     */
    public function show(Request $request, User $user)
    {
        $currentUser = $request->user();
        $isSuperAdmin = $currentUser->hasRole('super_admin');

        $allowedRoleNames = $isSuperAdmin
            ? Role::active()
                ->whereNotIn('name', ['super_admin', 'viewer'])
                ->pluck('name')
                ->toArray()
            : ['legal_staff', 'unit_staff'];

        if (!$user->role || !in_array($user->role->name, $allowedRoleNames, true)) {
            abort(403);
        }

        $dateFrom = $this->parseDate($request->input('date_from'), true);
        $dateTo = $this->parseDate($request->input('date_to'), false);

        $documentQuery = Document::query()
            ->where('created_by', $user->id)
            ->when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->where('created_at', '<=', $dateTo));

        $summary = [
            'uploaded' => (clone $documentQuery)->count(),
            'approved' => (clone $documentQuery)
                ->whereIn('status', [Document::STATUS_APPROVED, Document::STATUS_PUBLISHED])
                ->count(),
            'in_review' => (clone $documentQuery)
                ->whereIn('status', [Document::STATUS_PENDING_REVIEW, Document::STATUS_PENDING_APPROVAL])
                ->count(),
            'rejected' => (clone $documentQuery)
                ->where('status', Document::STATUS_REJECTED)
                ->count(),
            'revisions' => DocumentHistory::query()
                ->where('performed_by', $user->id)
                ->where('action', DocumentHistory::ACTION_VERSION_ADDED)
                ->when($dateFrom, fn ($q) => $q->where('document_history.created_at', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->where('document_history.created_at', '<=', $dateTo))
                ->count(),
        ];

        $actions = DocumentHistory::ACTIONS;

        $history = DocumentHistory::query()
            ->with('document')
            ->where('performed_by', $user->id)
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->action))
            ->when($dateFrom, fn ($q) => $q->where('document_history.created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->where('document_history.created_at', '<=', $dateTo))
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.user-analytics.show', [
            'user' => $user->load(['role', 'unit', 'position']),
            'summary' => $summary,
            'history' => $history,
            'actions' => $actions,
        ]);
    }

    private function parseDate(?string $value, bool $startOfDay): ?Carbon
    {
        if (!$value) {
            return null;
        }

        $date = Carbon::parse($value);
        return $startOfDay ? $date->startOfDay() : $date->endOfDay();
    }

    private function resolveChartRange(?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        if ($dateFrom || $dateTo) {
            $labelFrom = $dateFrom?->translatedFormat('d M Y') ?? '...';
            $labelTo = $dateTo?->translatedFormat('d M Y') ?? '...';

            return [
                'from' => ($dateFrom ?? now()->subMonths(11))->copy()->startOfMonth(),
                'to' => ($dateTo ?? now())->copy()->endOfMonth(),
                'label' => "{$labelFrom} - {$labelTo}",
            ];
        }

        return [
            'from' => now()->subMonths(11)->startOfMonth(),
            'to' => now()->endOfMonth(),
            'label' => '12 bulan terakhir',
        ];
    }

    private function buildChartData($userIds, Carbon $from, Carbon $to): array
    {
        $period = CarbonPeriod::create($from->copy(), '1 month', $to->copy());

        $monthKeys = [];
        $labels = [];
        foreach ($period as $month) {
            $key = $month->format('Y-m');
            $monthKeys[] = $key;
            $labels[] = $month->translatedFormat('M Y');
        }

        $uploads = [];
        $approvals = [];
        $rejections = [];
        $revisions = [];

        if ($userIds->isNotEmpty()) {
            $uploadsByMonth = Document::query()
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
                ->whereIn('created_by', $userIds)
                ->whereBetween('created_at', [$from, $to])
                ->groupBy('month')
                ->pluck('total', 'month')
                ->toArray();

            $approvalsByMonth = DocumentHistory::query()
                ->selectRaw("DATE_FORMAT(document_history.created_at, '%Y-%m') as month, COUNT(*) as total")
                ->join('documents', 'documents.id', '=', 'document_history.document_id')
                ->whereIn('documents.created_by', $userIds)
                ->where('document_history.action', DocumentHistory::ACTION_APPROVED)
                ->whereBetween('document_history.created_at', [$from, $to])
                ->groupBy('month')
                ->pluck('total', 'month')
                ->toArray();

            $rejectionsByMonth = DocumentHistory::query()
                ->selectRaw("DATE_FORMAT(document_history.created_at, '%Y-%m') as month, COUNT(*) as total")
                ->join('documents', 'documents.id', '=', 'document_history.document_id')
                ->whereIn('documents.created_by', $userIds)
                ->where('document_history.action', DocumentHistory::ACTION_REJECTED)
                ->whereBetween('document_history.created_at', [$from, $to])
                ->groupBy('month')
                ->pluck('total', 'month')
                ->toArray();

            $revisionsByMonth = DocumentHistory::query()
                ->selectRaw("DATE_FORMAT(document_history.created_at, '%Y-%m') as month, COUNT(*) as total")
                ->whereIn('document_history.performed_by', $userIds)
                ->where('document_history.action', DocumentHistory::ACTION_VERSION_ADDED)
                ->whereBetween('document_history.created_at', [$from, $to])
                ->groupBy('month')
                ->pluck('total', 'month')
                ->toArray();

            foreach ($monthKeys as $key) {
                $uploads[] = $uploadsByMonth[$key] ?? 0;
                $approvals[] = $approvalsByMonth[$key] ?? 0;
                $rejections[] = $rejectionsByMonth[$key] ?? 0;
                $revisions[] = $revisionsByMonth[$key] ?? 0;
            }
        } else {
            foreach ($monthKeys as $key) {
                $uploads[] = 0;
                $approvals[] = 0;
                $rejections[] = 0;
                $revisions[] = 0;
            }
        }

        return [
            'labels' => $labels,
            'uploads' => $uploads,
            'approvals' => $approvals,
            'rejections' => $rejections,
            'revisions' => $revisions,
        ];
    }
}
