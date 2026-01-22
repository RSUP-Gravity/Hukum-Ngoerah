<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $document->document_number }} - {{ $document->title }}</title>
    <style>
        @page {
            margin: 2cm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header-logo {
            width: 80px;
            height: auto;
            margin-bottom: 10px;
        }
        
        .header-title {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .header-subtitle {
            font-size: 11pt;
        }
        
        .document-info {
            margin-bottom: 20px;
        }
        
        .document-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .document-info td {
            padding: 4px 0;
            vertical-align: top;
        }
        
        .document-info td:first-child {
            width: 150px;
            font-weight: bold;
        }
        
        .document-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 30px 0;
            padding: 15px;
            border: 2px solid #000;
        }
        
        .document-content {
            text-align: justify;
        }
        
        .section {
            margin-bottom: 20px;
        }
        
        .section-title {
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        
        .meta-info {
            background: #f5f5f5;
            padding: 15px;
            margin-top: 30px;
            font-size: 10pt;
        }
        
        .meta-info table {
            width: 100%;
        }
        
        .meta-info td {
            padding: 3px 10px 3px 0;
        }
        
        .signatures {
            margin-top: 50px;
            display: table;
            width: 100%;
        }
        
        .signature-block {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            width: 200px;
            margin: 80px auto 5px;
        }
        
        .signature-name {
            font-weight: bold;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9pt;
            color: #666;
            padding: 10px;
            border-top: 1px solid #ccc;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100pt;
            color: rgba(0, 0, 0, 0.03);
            text-transform: uppercase;
            pointer-events: none;
            z-index: -1;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-published { background: #d4edda; color: #155724; }
        .status-draft { background: #e2e3e5; color: #383d41; }
        .status-expired { background: #f8d7da; color: #721c24; }
        
        @media print {
            .no-print { display: none !important; }
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
        
        .print-actions {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .print-actions button {
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            background: #00A0B0;
            color: white;
            border: none;
            border-radius: 5px;
            margin-left: 10px;
        }
        
        .print-actions button:hover {
            background: #008b9a;
        }
    </style>
</head>
<body>
    {{-- Print Actions --}}
    <div class="print-actions no-print">
        <button onclick="window.print()">
            <i class="bi bi-printer"></i> Cetak
        </button>
        <button onclick="window.close()">
            Tutup
        </button>
    </div>
    
    {{-- Watermark --}}
    @if($document->confidentiality_level !== 'public')
        <div class="watermark">{{ strtoupper($document->confidentiality_level) }}</div>
    @endif
    
    {{-- Header --}}
    <div class="header">
        <img src="{{ asset('images/logo-rsup.png') }}" alt="Logo" class="header-logo" onerror="this.style.display='none'">
        <div class="header-title">RSUP Prof. Dr. I.G.N.G. Ngoerah</div>
        <div class="header-subtitle">Jl. Diponegoro, Dauh Puri Klod, Denpasar Barat, Kota Denpasar, Bali 80113</div>
    </div>
    
    {{-- Document Title --}}
    <div class="document-title">
        {{ $document->documentType->name ?? 'DOKUMEN' }}<br>
        {{ $document->title }}
    </div>
    
    {{-- Document Info --}}
    <div class="document-info">
        <table>
            <tr>
                <td>Nomor Dokumen</td>
                <td>: {{ $document->document_number }}</td>
            </tr>
            <tr>
                <td>Jenis Dokumen</td>
                <td>: {{ $document->documentType->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Kategori</td>
                <td>: {{ $document->documentCategory->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Unit</td>
                <td>: {{ $document->unit->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Tanggal Berlaku</td>
                <td>: {{ $document->effective_date?->format('d F Y') ?? '-' }}</td>
            </tr>
            @if($document->expiry_date)
            <tr>
                <td>Tanggal Kadaluarsa</td>
                <td>: {{ $document->expiry_date->format('d F Y') }}</td>
            </tr>
            @endif
            <tr>
                <td>Status</td>
                <td>: 
                    @php
                        $statusClass = match($document->status) {
                            'published' => 'status-published',
                            'expired' => 'status-expired',
                            default => 'status-draft'
                        };
                        $statusLabel = match($document->status) {
                            'published' => 'Berlaku',
                            'expired' => 'Kadaluarsa',
                            'archived' => 'Diarsipkan',
                            default => ucfirst($document->status)
                        };
                    @endphp
                    <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                </td>
            </tr>
            <tr>
                <td>Tingkat Kerahasiaan</td>
                <td>: {{ ucfirst($document->confidentiality_level) }}</td>
            </tr>
        </table>
    </div>
    
    {{-- Document Content --}}
    @if($document->description)
    <div class="section">
        <div class="section-title">Deskripsi</div>
        <div class="document-content">
            {!! nl2br(e($document->description)) !!}
        </div>
    </div>
    @endif
    
    {{-- Signatures --}}
    <div class="signatures">
        <div class="signature-block">
            <p>Disusun oleh:</p>
            <div class="signature-line"></div>
            <p class="signature-name">{{ $document->creator->name ?? '-' }}</p>
            <p>{{ $document->creator->position->name ?? '' }}</p>
        </div>
        
        @if($document->latestApproval)
        <div class="signature-block">
            <p>Disetujui oleh:</p>
            <div class="signature-line"></div>
            <p class="signature-name">{{ $document->latestApproval->approver->name ?? '-' }}</p>
            <p>{{ $document->latestApproval->approver->position->name ?? '' }}</p>
        </div>
        @endif
    </div>
    
    {{-- Meta Info --}}
    <div class="meta-info">
        <table>
            <tr>
                <td><strong>Versi:</strong> {{ $document->current_version }}</td>
                <td><strong>Dibuat:</strong> {{ $document->created_at->format('d M Y H:i') }}</td>
                <td><strong>Diperbarui:</strong> {{ $document->updated_at->format('d M Y H:i') }}</td>
            </tr>
        </table>
    </div>
    
    {{-- Footer --}}
    <div class="footer">
        Dicetak pada {{ now()->locale('id')->isoFormat('dddd, D MMMM Y HH:mm') }} WIB | 
        Sistem Manajemen Dokumen Hukum - RSUP Prof. Dr. I.G.N.G. Ngoerah
    </div>
</body>
</html>
