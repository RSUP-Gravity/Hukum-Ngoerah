<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get statistics based on user role
        $stats = $this->getStatistics($user);
        
        // Get pending approvals for the user
        $pendingApprovals = $this->getPendingApprovals($user);
        
        // Get recent documents
        $recentDocuments = $this->getRecentDocuments($user);
        
        // Get expiring documents
        $expiringDocuments = $this->getExpiringDocuments($user);
        
        // Get recent notifications
        $recentNotifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Get chart data
        $chartData = $this->getChartData($user);

        return view('dashboard', compact(
            'stats',
            'pendingApprovals',
            'recentDocuments',
            'expiringDocuments',
            'recentNotifications',
            'chartData'
        ));
    }

    /**
     * Get dashboard statistics
     */
    protected function getStatistics($user): array
    {
        $query = Document::query();
        
        // Filter based on user permissions
        if (!$user->hasPermission('documents.view_all')) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('unit_id', $user->unit_id);
            });
        }

        return [
            'total_documents' => (clone $query)->count(),
            'draft_documents' => (clone $query)->status(Document::STATUS_DRAFT)->count(),
            'pending_approval' => (clone $query)->status([
                Document::STATUS_PENDING_REVIEW,
                Document::STATUS_PENDING_APPROVAL,
            ])->count(),
            'published_documents' => (clone $query)->status(Document::STATUS_PUBLISHED)->count(),
            'expiring_soon' => (clone $query)->expiringSoon(30)->count(),
            'expired_documents' => (clone $query)->expired()->count(),
            'my_pending_approvals' => DocumentApproval::forApprover($user->id)->pending()->count(),
            'unread_notifications' => $user->unreadNotifications()->count(),
        ];
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
    protected function getRecentDocuments($user)
    {
        $query = Document::with(['documentType', 'creator', 'unit'])
            ->orderBy('created_at', 'desc');
        
        // Filter based on user permissions
        if (!$user->hasPermission('documents.view_all')) {
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
    protected function getExpiringDocuments($user)
    {
        $query = Document::with(['documentType', 'creator'])
            ->expiringSoon(30)
            ->status(Document::STATUS_PUBLISHED)
            ->orderBy('expiry_date', 'asc');
        
        // Filter based on user permissions
        if (!$user->hasPermission('documents.view_all')) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('unit_id', $user->unit_id);
            });
        }

        return $query->limit(5)->get();
    }

    /**
     * Get chart data for documents
     */
    protected function getChartData($user): array
    {
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
                    'name' => $item->documentType?->name ?? 'Tidak Diketahui',
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

        return [
            'by_status' => $byStatus,
            'by_type' => $byType,
            'per_month' => $perMonth,
        ];
    }
}
