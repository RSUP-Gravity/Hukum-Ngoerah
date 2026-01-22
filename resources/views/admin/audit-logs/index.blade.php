@extends('layouts.app')

@section('title', 'Audit Log')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Admin'],
        ['label' => 'Audit Log']
    ]" />
@endsection

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Audit Log</h1>
            <p class="text-muted mb-0">Riwayat aktivitas sistem</p>
        </div>
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#exportModal">
            <i class="bi bi-download me-2"></i>Export
        </button>
    </div>

    {{-- Filters --}}
    <div class="glass-card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.audit-logs.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Pencarian</label>
                        <input type="text" class="form-control" name="search" 
                               value="{{ request('search') }}" placeholder="Cari aktivitas...">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Modul</label>
                        <select name="module" class="form-select">
                            <option value="">Semua Modul</option>
                            @foreach($modules as $module)
                                <option value="{{ $module }}" {{ request('module') === $module ? 'selected' : '' }}>
                                    {{ ucfirst($module) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Aksi</label>
                        <select name="action" class="form-select">
                            <option value="">Semua Aksi</option>
                            @foreach($actions as $action)
                                <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                                    {{ ucfirst($action) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-1">
                            <i class="bi bi-funnel"></i>
                        </button>
                        <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Logs Table --}}
    <div class="glass-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Pengguna</th>
                            <th>Aksi</th>
                            <th>Modul</th>
                            <th>Entity</th>
                            <th>Deskripsi</th>
                            <th>IP</th>
                            <th class="text-end">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>
                                <span title="{{ $log->created_at->format('d/m/Y H:i:s') }}">
                                    {{ $log->created_at->diffForHumans() }}
                                </span>
                            </td>
                            <td>
                                @if($log->user)
                                    <a href="{{ route('admin.users.show', $log->user) }}" class="text-decoration-none">
                                        {{ $log->user->name }}
                                    </a>
                                @else
                                    <span class="text-muted">{{ $log->username ?? 'System' }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $actionColors = [
                                        'created' => 'success',
                                        'updated' => 'info',
                                        'deleted' => 'danger',
                                        'login' => 'primary',
                                        'logout' => 'secondary',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $actionColors[$log->action] ?? 'secondary' }}">
                                    {{ strtoupper($log->action) }}
                                </span>
                            </td>
                            <td>{{ ucfirst($log->module) }}</td>
                            <td>{{ Str::limit($log->entity_name, 30) ?? '-' }}</td>
                            <td>{{ Str::limit($log->description, 40) ?? '-' }}</td>
                            <td>
                                <code class="small">{{ $log->ip_address ?? '-' }}</code>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.audit-logs.show', $log) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-journal-text fs-1 d-block mb-3 opacity-25"></i>
                                    <p class="mb-0">Tidak ada log ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($logs->hasPages())
        <div class="card-footer bg-transparent">
            {{ $logs->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Export Modal --}}
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.audit-logs.export') }}" method="GET">
                <div class="modal-header">
                    <h5 class="modal-title">Export Audit Log</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Dari Tanggal <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="date_from" required
                               value="{{ date('Y-m-01') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sampai Tanggal <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="date_to" required
                               value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Modul</label>
                        <select name="module" class="form-select">
                            <option value="">Semua Modul</option>
                            @foreach($modules as $module)
                                <option value="{{ $module }}">{{ ucfirst($module) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-download me-1"></i>Export CSV
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
