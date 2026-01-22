<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Daftar Dokumen Hukum - RSUP Prof. Dr. I.G.N.G. Ngoerah</title>
    <style>
        @page {
            margin: 15mm 10mm 20mm 10mm;
            size: A4 landscape;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            line-height: 1.4;
            color: #1e293b;
        }
        
        .header {
            position: relative;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #00A0B0;
        }
        
        .header-table {
            width: 100%;
        }
        
        .logo-cell {
            width: 60px;
            vertical-align: middle;
        }
        
        .logo {
            width: 50px;
            height: auto;
        }
        
        .title-cell {
            vertical-align: middle;
            text-align: center;
        }
        
        .hospital-name {
            font-size: 14px;
            font-weight: bold;
            color: #00A0B0;
            margin: 0;
        }
        
        .hospital-subtitle {
            font-size: 10px;
            color: #64748B;
            margin: 2px 0 0 0;
        }
        
        .report-title {
            font-size: 13px;
            font-weight: bold;
            color: #1e293b;
            margin: 10px 0 5px 0;
            text-transform: uppercase;
        }
        
        .report-date {
            font-size: 9px;
            color: #64748B;
        }
        
        .date-cell {
            width: 80px;
            vertical-align: middle;
            text-align: right;
        }
        
        .filters-info {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 10px;
            margin-bottom: 12px;
            font-size: 8px;
        }
        
        .filters-info strong {
            color: #00A0B0;
        }
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        table.data-table thead tr {
            background: linear-gradient(135deg, #00A0B0, #00B4C8);
        }
        
        table.data-table thead th {
            color: white;
            font-weight: bold;
            font-size: 8px;
            padding: 8px 5px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #007a87;
        }
        
        table.data-table tbody tr {
            border-bottom: 1px solid #e2e8f0;
        }
        
        table.data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        table.data-table tbody td {
            padding: 6px 5px;
            font-size: 8px;
            vertical-align: top;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-active { 
            background-color: #dcfce7; 
            color: #166534; 
        }
        .status-attention { 
            background-color: #dbeafe; 
            color: #1e40af; 
        }
        .status-warning { 
            background-color: #fef9c3; 
            color: #854d0e; 
        }
        .status-critical { 
            background-color: #fef2f2; 
            color: #991b1b; 
        }
        .status-expired { 
            background-color: #fee2e2; 
            color: #7f1d1d; 
        }
        .status-perpetual { 
            background-color: #f3e8ff; 
            color: #6b21a8; 
        }
        .status-draft {
            background-color: #f1f5f9;
            color: #475569;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 15mm;
            font-size: 8px;
            color: #64748B;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }
        
        .footer-table {
            width: 100%;
        }
        
        .page-number:after {
            content: counter(page);
        }
        
        .summary-box {
            margin-top: 15px;
            padding: 10px;
            background-color: #f0fdfa;
            border: 1px solid #99f6e4;
            border-radius: 4px;
        }
        
        .summary-box h4 {
            margin: 0 0 8px 0;
            font-size: 10px;
            color: #00A0B0;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
        }
        
        .summary-item {
            display: table-cell;
            width: 16.66%;
            text-align: center;
            padding: 5px;
        }
        
        .summary-count {
            font-size: 14px;
            font-weight: bold;
            color: #1e293b;
        }
        
        .summary-label {
            font-size: 8px;
            color: #64748B;
        }
        
        .col-no { width: 4%; }
        .col-number { width: 12%; }
        .col-title { width: 20%; }
        .col-type { width: 10%; }
        .col-directorate { width: 10%; }
        .col-effective { width: 8%; }
        .col-expiry { width: 8%; }
        .col-days { width: 6%; }
        .col-status { width: 10%; }
        .col-conf { width: 7%; }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Logo RS Ngoerah">
                </td>
                <td class="title-cell">
                    <p class="hospital-name">RSUP Prof. Dr. I.G.N.G. Ngoerah</p>
                    <p class="hospital-subtitle">Kementerian Kesehatan Republik Indonesia</p>
                    <p class="report-title">Laporan Daftar Dokumen Hukum</p>
                    <p class="report-date">Dicetak pada: {{ now()->locale('id')->translatedFormat('d F Y H:i') }} WITA</p>
                </td>
                <td class="date-cell">
                    {{-- Empty for balance --}}
                </td>
            </tr>
        </table>
    </div>
    
    {{-- Filter Info --}}
    @if(!empty($activeFilters))
    <div class="filters-info">
        <strong>Filter Aktif:</strong> 
        @foreach($activeFilters as $key => $value)
            {{ $key }}: <em>{{ $value }}</em>@if(!$loop->last), @endif
        @endforeach
    </div>
    @endif
    
    {{-- Data Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-no text-center">No</th>
                <th class="col-number">Nomor Dokumen</th>
                <th class="col-title">Judul</th>
                <th class="col-type">Jenis</th>
                <th class="col-directorate">Direktorat</th>
                <th class="col-effective text-center">Tgl Berlaku</th>
                <th class="col-expiry text-center">Tgl Berakhir</th>
                <th class="col-days text-center">Sisa Hari</th>
                <th class="col-status text-center">Status</th>
                <th class="col-conf text-center">Klasifikasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($documents as $index => $doc)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $doc->document_number }}</td>
                <td>{{ Str::limit($doc->title, 50) }}</td>
                <td>{{ $doc->documentType?->name ?? '-' }}</td>
                <td>{{ $doc->directorate?->name ?? '-' }}</td>
                <td class="text-center">{{ $doc->effective_date?->format('d/m/Y') ?? '-' }}</td>
                <td class="text-center">{{ $doc->expiry_date?->format('d/m/Y') ?? '-' }}</td>
                <td class="text-center">
                    @if($doc->expiry_date)
                        @php
                            $days = now()->diffInDays($doc->expiry_date, false);
                        @endphp
                        @if($days < 0)
                            <span style="color: #dc2626;">{{ abs($days) }} (lewat)</span>
                        @else
                            {{ $days }}
                        @endif
                    @else
                        ∞
                    @endif
                </td>
                <td class="text-center">
                    @php
                        $statusClass = match($doc->expiry_status ?? 'active') {
                            'expired' => 'status-expired',
                            'critical' => 'status-critical',
                            'warning' => 'status-warning',
                            'attention' => 'status-attention',
                            'perpetual' => 'status-perpetual',
                            default => 'status-active',
                        };
                        $statusLabel = match($doc->expiry_status ?? 'active') {
                            'expired' => 'Kadaluarsa',
                            'critical' => '≤1 Bulan',
                            'warning' => '≤3 Bulan',
                            'attention' => '≤6 Bulan',
                            'perpetual' => 'Permanen',
                            default => 'Aktif',
                        };
                    @endphp
                    <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                </td>
                <td class="text-center">
                    @php
                        $confLabel = match($doc->confidentiality ?? 'internal') {
                            'public' => 'Publik',
                            'internal' => 'Internal',
                            'confidential' => 'Rahasia',
                            'restricted' => 'Terbatas',
                            default => 'Internal',
                        };
                    @endphp
                    {{ $confLabel }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center" style="padding: 20px; color: #94a3b8;">
                    Tidak ada dokumen yang ditemukan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    {{-- Summary --}}
    <div class="summary-box">
        <h4>Ringkasan Dokumen</h4>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-count">{{ $summary['total'] }}</div>
                <div class="summary-label">Total</div>
            </div>
            <div class="summary-item">
                <div class="summary-count" style="color: #16a34a;">{{ $summary['active'] }}</div>
                <div class="summary-label">Aktif</div>
            </div>
            <div class="summary-item">
                <div class="summary-count" style="color: #6b21a8;">{{ $summary['perpetual'] }}</div>
                <div class="summary-label">Permanen</div>
            </div>
            <div class="summary-item">
                <div class="summary-count" style="color: #ea580c;">{{ $summary['expiring_soon'] }}</div>
                <div class="summary-label">Segera Berakhir</div>
            </div>
            <div class="summary-item">
                <div class="summary-count" style="color: #dc2626;">{{ $summary['expired'] }}</div>
                <div class="summary-label">Kadaluarsa</div>
            </div>
        </div>
    </div>
    
    {{-- Footer --}}
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td style="width: 33%;">
                    Sistem Manajemen Dokumen Hukum Terpusat
                </td>
                <td style="width: 34%; text-align: center;">
                    RSUP Prof. Dr. I.G.N.G. Ngoerah - Denpasar
                </td>
                <td style="width: 33%; text-align: right;">
                    Halaman <span class="page-number"></span>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
