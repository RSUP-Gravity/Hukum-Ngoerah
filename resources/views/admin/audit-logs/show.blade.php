@extends('layouts.app')

@section('title', 'Detail Audit Log')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Admin'],
        ['label' => 'Audit Log', 'url' => route('admin.audit-logs.index')],
        ['label' => 'Detail']
    ]" />
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            {{-- Page Header --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Detail Audit Log</h1>
                    <p class="text-muted mb-0">{{ $auditLog->created_at->format('d F Y H:i:s') }}</p>
                </div>
                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>

            {{-- Log Info --}}
            <div class="glass-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Log</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3 text-muted">Waktu</dt>
                        <dd class="col-sm-9">{{ $auditLog->created_at->format('d F Y H:i:s') }}</dd>
                        
                        <dt class="col-sm-3 text-muted">Pengguna</dt>
                        <dd class="col-sm-9">
                            @if($auditLog->user)
                                <a href="{{ route('admin.users.show', $auditLog->user) }}">{{ $auditLog->user->name }}</a>
                                <span class="text-muted">({{ $auditLog->username }})</span>
                            @else
                                {{ $auditLog->username ?? 'System' }}
                            @endif
                        </dd>
                        
                        <dt class="col-sm-3 text-muted">Aksi</dt>
                        <dd class="col-sm-9">
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
                            <span class="badge bg-{{ $actionColors[$auditLog->action] ?? 'secondary' }}">
                                {{ strtoupper($auditLog->action) }}
                            </span>
                        </dd>
                        
                        <dt class="col-sm-3 text-muted">Modul</dt>
                        <dd class="col-sm-9">{{ ucfirst($auditLog->module) }}</dd>
                        
                        <dt class="col-sm-3 text-muted">Entity Type</dt>
                        <dd class="col-sm-9">{{ $auditLog->entity_type ?? '-' }}</dd>
                        
                        <dt class="col-sm-3 text-muted">Entity ID</dt>
                        <dd class="col-sm-9">{{ $auditLog->entity_id ?? '-' }}</dd>
                        
                        <dt class="col-sm-3 text-muted">Entity Name</dt>
                        <dd class="col-sm-9">{{ $auditLog->entity_name ?? '-' }}</dd>
                        
                        <dt class="col-sm-3 text-muted">IP Address</dt>
                        <dd class="col-sm-9"><code>{{ $auditLog->ip_address ?? '-' }}</code></dd>
                        
                        <dt class="col-sm-3 text-muted">User Agent</dt>
                        <dd class="col-sm-9 mb-0">
                            <small class="text-muted">{{ $auditLog->user_agent ?? '-' }}</small>
                        </dd>
                    </dl>
                </div>
            </div>
            
            {{-- Description --}}
            @if($auditLog->description)
            <div class="glass-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Deskripsi</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $auditLog->description }}</p>
                </div>
            </div>
            @endif

            <div class="row">
                {{-- Old Values --}}
                <div class="col-md-6">
                    <div class="glass-card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-arrow-left-circle text-danger me-2"></i>Nilai Sebelumnya
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($auditLog->old_values)
                                <pre class="bg-light p-3 rounded mb-0" style="max-height: 400px; overflow: auto;"><code>{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                            @else
                                <p class="text-muted mb-0">Tidak ada data sebelumnya.</p>
                            @endif
                        </div>
                    </div>
                </div>
                
                {{-- New Values --}}
                <div class="col-md-6">
                    <div class="glass-card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-arrow-right-circle text-success me-2"></i>Nilai Sesudahnya
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($auditLog->new_values)
                                <pre class="bg-light p-3 rounded mb-0" style="max-height: 400px; overflow: auto;"><code>{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                            @else
                                <p class="text-muted mb-0">Tidak ada data sesudahnya.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
