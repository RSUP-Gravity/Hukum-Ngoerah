<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user');
        
        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('action', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%")
                  ->orWhere('entity_name', 'like', "%{$request->search}%")
                  ->orWhere('username', 'like', "%{$request->search}%");
            });
        }
        
        // Filter by module
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }
        
        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        
        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $logs = $query->recent()->paginate(50);
        
        // Get unique modules for filter
        $modules = AuditLog::distinct()->pluck('module');
        
        // Get unique actions for filter
        $actions = AuditLog::distinct()->pluck('action');

        return view('admin.audit-logs.index', compact('logs', 'modules', 'actions'));
    }

    /**
     * Display the specified audit log
     */
    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');

        return view('admin.audit-logs.show', compact('auditLog'));
    }

    /**
     * Export audit logs
     */
    public function export(Request $request)
    {
        $request->validate([
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
        ]);

        $query = AuditLog::with('user')
            ->whereBetween('created_at', [
                $request->date_from,
                $request->date_to . ' 23:59:59',
            ])
            ->recent();
        
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        $logs = $query->get();

        // Log export action
        AuditLog::log(
            'exported',
            AuditLog::MODULE_ADMIN,
            'AuditLog',
            null,
            null,
            null,
            [
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'count' => $logs->count(),
            ],
            'Audit log diekspor.'
        );

        // For now, return a simple CSV download
        $filename = 'audit_logs_' . $request->date_from . '_to_' . $request->date_to . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Waktu',
                'Username',
                'Aksi',
                'Modul',
                'Entity',
                'Deskripsi',
                'IP Address',
            ]);
            
            // CSV data
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->username ?? '-',
                    $log->action,
                    $log->module,
                    $log->entity_name ?? '-',
                    $log->description ?? '-',
                    $log->ip_address ?? '-',
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
