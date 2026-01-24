<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\DocumentHistory;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Cache TTL in seconds (5 minutes)
     */
    const CACHE_TTL = 300;

    /**
     * Display the dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isViewer = $user->hasRole('viewer');
        
        // Get statistics based on user role
        $stats = $this->getStatistics($user, $isViewer);
        
        // Get pending approvals for the user
        $pendingApprovals = $isViewer ? collect() : $this->getPendingApprovals($user);
        
        // Get recent documents
        $recentDocuments = $this->getRecentDocuments($user, $isViewer);
        
        // Get expiring documents
        $expiringDocuments = $this->getExpiringDocuments($user, $isViewer);
        
        // Get recent activity timeline
        $recentActivities = $this->getRecentActivities($user, $isViewer);
        
        // Get recent notifications
        $recentNotifications = $isViewer ? collect() : $user->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Get chart data
        $chartData = $isViewer ? [] : $this->getChartData($user);

        // Check if we should show login notification popup
        $showLoginNotification = false;
        $criticalDocuments = collect();
        
        if ($request->session()->pull('show_login_notification', false)) {
            // Only show for admin users
            if ($user->hasPermission('documents.view_all') || $user->hasRole('admin')) {
                // Get critical documents (expired or expiring within 30 days)
                $criticalDocuments = Document::with(['documentType', 'directorate'])
                    ->where(function ($query) {
                        $query->expired()
                            ->orWhere(function ($q) {
                                $q->whereNotNull('expiry_date')
                                    ->whereBetween('expiry_date', [now(), now()->addDays(30)]);
                            });
                    })
                    ->orderBy('expiry_date', 'asc')
                    ->limit(10)
                    ->get();
                
                $showLoginNotification = $criticalDocuments->count() > 0;
            }
        }

        return view('dashboard', compact(
            'stats',
            'pendingApprovals',
            'recentDocuments',
            'expiringDocuments',
            'recentActivities',
            'recentNotifications',
            'chartData',
            'showLoginNotification',
            'criticalDocuments',
            'isViewer'
        ));
    }

    /**
     * Refresh dashboard cache (for AJAX refresh button)
     */
    public function refreshCache(Request $request)
    {
        $user = Auth::user();
        
        // Clear user's dashboard caches
        Cache::forget("dashboard_stats_user_{$user->id}");
        Cache::forget("dashboard_charts_user_{$user->id}");
        
        return response()->json([
            'success' => true,
            'message' => 'Dashboard cache berhasil diperbarui',
        ]);
    }

    /**
     * Get dashboard statistics with caching (5 minute TTL)
     */
    protected function getStatistics($user, bool $isViewer = false): array
    {
        $cacheKey = "dashboard_stats_user_{$user->id}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user, $isViewer) {
            $query = Document::query();
            $now = now();
            
            // Filter based on user permissions
            if ($isViewer) {
                $query->where('status', Document::STATUS_PUBLISHED);
            } elseif (!$user->hasPermission('documents.view_all')) {
                $query->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('unit_id', $user->unit_id);
                });
            }

            $totalDocuments = (clone $query)->count();
            $newThisMonth = (clone $query)
                ->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
                ->count();
            $publishedDocuments = (clone $query)->published()->count();
            $activeDocuments = (clone $query)
                ->published()
                ->where(function ($q) use ($now) {
                    $q->whereNull('expiry_date')
                        ->orWhere('expiry_date', '>=', $now);
                })
                ->count();
            $expiringSoon = (clone $query)->published()->expiringSoon(30)->count();
            $expiredDocuments = (clone $query)->published()->expired()->count();

            return [
                'total' => $totalDocuments,
                'new_this_month' => $newThisMonth,
                'active' => $activeDocuments,
                'expiring' => $expiringSoon,
                'expired' => $expiredDocuments,
                'total_documents' => $totalDocuments,
                'draft_documents' => (clone $query)->status(Document::STATUS_DRAFT)->count(),
                'pending_approval' => (clone $query)->status([
                    Document::STATUS_PENDING_REVIEW,
                    Document::STATUS_PENDING_APPROVAL,
                ])->count(),
                'published_documents' => $publishedDocuments,
                'expiring_soon' => $expiringSoon,
                'expired_documents' => $expiredDocuments,
                'my_pending_approvals' => DocumentApproval::forApprover($user->id)->pending()->count(),
                'unread_notifications' => $user->unreadNotifications()->count(),
            ];
        });
    }

    /**
     * Get pending approvals for user
     */
    protected function getPendingApprovals($user)
    {
        return DocumentApproval::with(['document', 'document.documentType', 'document.creator'])
            ->forApprover($user->id)
            ->pending()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get recent documents
     */
    protected function getRecentDocuments($user, bool $isViewer = false)
    {
        $query = Document::with(['documentType', 'creator', 'unit'])
            ->orderBy('created_at', 'desc');
        
        // Filter based on user permissions
        if ($isViewer) {
            $query->where('status', Document::STATUS_PUBLISHED);
        } elseif (!$user->hasPermission('documents.view_all')) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('unit_id', $user->unit_id)
                  ->orWhere('status', Document::STATUS_PUBLISHED);
            });
        }

        return $query->limit(10)->get();
    }

    /**
     * Get expiring documents
     */
    protected function getExpiringDocuments($user, bool $isViewer = false)
    {
        $query = Document::with(['documentType', 'creator'])
            ->expiringSoon(30)
            ->status(Document::STATUS_PUBLISHED)
            ->orderBy('expiry_date', 'asc');
        
        // Filter based on user permissions
        if (!$isViewer && !$user->hasPermission('documents.view_all')) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('unit_id', $user->unit_id);
            });
        }

        return $query->limit(5)->get();
    }

    /**
     * Get recent activities for activity timeline
     */
    protected function getRecentActivities($user, bool $isViewer = false)
    {
        if ($isViewer) {
            return collect();
        }

        $query = DocumentHistory::with(['document', 'performer'])
            ->whereHas('document')
            ->orderBy('created_at', 'desc');
        
        // Filter based on user permissions
        if (!$user->hasPermission('documents.view_all')) {
            $query->whereHas('document', function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('unit_id', $user->unit_id);
            });
        }
        
        // Exclude view actions to reduce noise
        $query->whereNotIn('action', [
            DocumentHistory::ACTION_VIEWED,
        ]);

        return $query->limit(15)->get();
    }

    /**
     * Get chart data for documents with caching (5 minute TTL)
     */
    protected function getChartData($user): array
    {
        $cacheKey = "dashboard_charts_user_{$user->id}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            // Documents by status
            $byStatus = Document::select('status', DB::raw('count(*) as total'))
                ->when(!$user->hasPermission('documents.view_all'), function ($q) use ($user) {
                    $q->where(function ($sub) use ($user) {
                        $sub->where('created_by', $user->id)
                            ->orWhere('unit_id', $user->unit_id);
                    });
                })
                ->groupBy('status')
                ->get()
                ->pluck('total', 'status')
                ->toArray();

            // Documents by type
            $byType = Document::select('document_type_id', DB::raw('count(*) as total'))
                ->with('documentType:id,name')
                ->when(!$user->hasPermission('documents.view_all'), function ($q) use ($user) {
                    $q->where(function ($sub) use ($user) {
                        $sub->where('created_by', $user->id)
                            ->orWhere('unit_id', $user->unit_id);
                    });
                })
                ->groupBy('document_type_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->document_type_id,
                        'name' => $item->documentType?->name ?? 'Tidak Diketahui',
                        'total' => $item->total,
                    ];
                })
                ->toArray();

            // Documents by category (Tipe Dokumen)
            $byCategory = Document::select('document_category_id', DB::raw('count(*) as total'))
                ->with('documentCategory:id,name')
                ->when(!$user->hasPermission('documents.view_all'), function ($q) use ($user) {
                    $q->where(function ($sub) use ($user) {
                        $sub->where('created_by', $user->id)
                            ->orWhere('unit_id', $user->unit_id);
                    });
                })
                ->groupBy('document_category_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->document_category_id,
                        'name' => $item->documentCategory?->name ?? 'Tidak Diketahui',
                        'total' => $item->total,
                    ];
                })
                ->toArray();

            // Documents by directorate
            $byDirectorate = Document::select('directorate_id', DB::raw('count(*) as total'))
                ->with('directorate:id,name')
                ->when(!$user->hasPermission('documents.view_all'), function ($q) use ($user) {
                    $q->where(function ($sub) use ($user) {
                        $sub->where('created_by', $user->id)
                            ->orWhere('unit_id', $user->unit_id);
                    });
                })
                ->groupBy('directorate_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->directorate_id,
                        'name' => $item->directorate?->name ?? 'Tidak Diketahui',
                        'total' => $item->total,
                    ];
                })
                ->toArray();

            // Documents created per month (last 12 months)
            $perMonth = Document::select(
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                    DB::raw('count(*) as total')
                )
                ->where('created_at', '>=', now()->subMonths(12))
                ->when(!$user->hasPermission('documents.view_all'), function ($q) use ($user) {
                    $q->where(function ($sub) use ($user) {
                        $sub->where('created_by', $user->id)
                            ->orWhere('unit_id', $user->unit_id);
                    });
                })
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->pluck('total', 'month')
                ->toArray();

            // Expiry timeline (next 6 months)
            $expiryTimeline = [];
            for ($i = 0; $i < 6; $i++) {
                $startDate = now()->addMonths($i)->startOfMonth();
                $endDate = now()->addMonths($i)->endOfMonth();
                $monthLabel = $startDate->translatedFormat('M Y');
                
                $count = Document::whereNotNull('expiry_date')
                    ->whereBetween('expiry_date', [$startDate, $endDate])
                    ->when(!$user->hasPermission('documents.view_all'), function ($q) use ($user) {
                        $q->where(function ($sub) use ($user) {
                            $sub->where('created_by', $user->id)
                                ->orWhere('unit_id', $user->unit_id);
                        });
                    })
                    ->count();
                
                $expiryTimeline[$monthLabel] = $count;
            }

            return [
                'by_status' => $byStatus,
                'by_type' => $byType,
                'by_category' => $byCategory,
                'by_directorate' => $byDirectorate,
                'per_month' => $perMonth,
                'expiry_timeline' => $expiryTimeline,
            ];
        });
    }
}
