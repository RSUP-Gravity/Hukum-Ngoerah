# 📁 Dokumentasi Struktur Kode - Sistem Manajemen Dokumen Hukum Terpusat

> **Versi:** 1.0  
> **Terakhir Diperbarui:** Januari 2025  
> **Framework:** Laravel 11 + Tailwind CSS + Alpine.js

---

## 📋 Daftar Isi

1. [Gambaran Arsitektur](#gambaran-arsitektur)
2. [Struktur Direktori](#struktur-direktori)
3. [Layer Aplikasi](#layer-aplikasi)
4. [Komponen Utama](#komponen-utama)
5. [Alur Data](#alur-data)
6. [Konvensi Kode](#konvensi-kode)

---

## 🏗️ Gambaran Arsitektur

Aplikasi menggunakan arsitektur **MVC (Model-View-Controller)** standar Laravel dengan tambahan **Service Layer** untuk logika bisnis kompleks.

```
┌─────────────────────────────────────────────────────────────┐
│                        Browser/Client                        │
├─────────────────────────────────────────────────────────────┤
│                    Routes (web.php, api.php)                 │
├─────────────────────────────────────────────────────────────┤
│                        Middleware                            │
│    (Auth, RolePermission, AuditLog, ForcePasswordChange)    │
├─────────────────────────────────────────────────────────────┤
│                        Controllers                           │
│    (DashboardController, DocumentController, etc.)           │
├─────────────────────────────────────────────────────────────┤
│                      Service Layer                           │
│    (DocumentStatusService, PdfWatermarkService, etc.)        │
├─────────────────────────────────────────────────────────────┤
│                         Models                               │
│    (User, Document, DocumentType, Role, Permission, etc.)    │
├─────────────────────────────────────────────────────────────┤
│                        Database                              │
│                      (MySQL 8.0)                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 📂 Struktur Direktori

```
hukum-ngoerah/
├── app/                          # Kode Aplikasi Utama
│   ├── Console/                  # Artisan Commands
│   │   └── Kernel.php
│   │
│   ├── Exports/                  # Export Classes (Maatwebsite Excel)
│   │   └── DocumentsExport.php   # Export dokumen ke Excel
│   │
│   ├── Http/                     # Layer HTTP
│   │   ├── Controllers/          # Controller Classes
│   │   │   ├── Admin/            # Controller untuk modul Admin
│   │   │   │   ├── UserController.php
│   │   │   │   ├── RoleController.php
│   │   │   │   ├── AuditLogController.php
│   │   │   │   └── SettingsController.php
│   │   │   │
│   │   │   ├── Auth/             # Controller Autentikasi
│   │   │   │   ├── LoginController.php
│   │   │   │   └── PasswordController.php
│   │   │   │
│   │   │   ├── Master/           # Controller Master Data
│   │   │   │   ├── DirectorateController.php
│   │   │   │   ├── UnitController.php
│   │   │   │   ├── DocumentTypeController.php
│   │   │   │   └── DocumentCategoryController.php
│   │   │   │
│   │   │   ├── DashboardController.php
│   │   │   ├── DocumentController.php
│   │   │   ├── NotificationController.php
│   │   │   └── ProfileController.php
│   │   │
│   │   └── Middleware/           # Custom Middleware
│   │       ├── CheckRolePermission.php
│   │       ├── AuditLogMiddleware.php
│   │       └── ForcePasswordChange.php
│   │
│   ├── Models/                   # Eloquent Models
│   │   ├── User.php              # Model Pengguna
│   │   ├── Role.php              # Model Role
│   │   ├── Permission.php        # Model Permission
│   │   ├── Document.php          # Model Dokumen Utama
│   │   ├── DocumentType.php      # Model Jenis Dokumen
│   │   ├── DocumentCategory.php  # Model Kategori Dokumen
│   │   ├── DocumentVersion.php   # Model Versi Dokumen
│   │   ├── DocumentHistory.php   # Model Riwayat Dokumen
│   │   ├── DocumentApproval.php  # Model Workflow Approval
│   │   ├── DocumentAccess.php    # Model Hak Akses Dokumen
│   │   ├── Directorate.php       # Model Direktorat
│   │   ├── Unit.php              # Model Unit Kerja
│   │   ├── Position.php          # Model Jabatan
│   │   ├── Notification.php      # Model Notifikasi
│   │   ├── AuditLog.php          # Model Audit Log
│   │   └── SystemSetting.php     # Model Pengaturan Sistem
│   │
│   ├── Notifications/            # Notification Classes
│   │   └── DocumentExpiryNotification.php
│   │
│   ├── Providers/                # Service Providers
│   │   └── AppServiceProvider.php
│   │
│   └── Services/                 # Business Logic Services
│       ├── DocumentStatusService.php  # Kalkulasi status dokumen
│       ├── PdfWatermarkService.php    # Watermark PDF
│       └── FpdiWithRotation.php       # FPDI extension
│
├── bootstrap/                    # Bootstrap Laravel
│   ├── app.php
│   └── cache/
│
├── config/                       # Konfigurasi Aplikasi
│   ├── app.php                   # Konfigurasi Utama
│   ├── database.php              # Konfigurasi Database
│   ├── filesystems.php           # Konfigurasi Storage
│   └── ...
│
├── database/                     # Database Files
│   ├── factories/                # Model Factories (Testing)
│   │   ├── UserFactory.php
│   │   ├── DocumentFactory.php
│   │   ├── DocumentTypeFactory.php
│   │   ├── RoleFactory.php
│   │   ├── PermissionFactory.php
│   │   ├── DirectorateFactory.php
│   │   └── UnitFactory.php
│   │
│   ├── migrations/               # Database Migrations
│   │   ├── 2024_01_01_000001_create_roles_table.php
│   │   ├── 2024_01_01_000002_create_permissions_table.php
│   │   ├── ...
│   │   └── 2024_01_01_000018_create_document_templates_table.php
│   │
│   └── seeders/                  # Database Seeders
│       ├── DatabaseSeeder.php
│       ├── RoleSeeder.php
│       ├── PermissionSeeder.php
│       └── UserSeeder.php
│
├── docs/                         # Dokumentasi
│   ├── INSTALLATION.md           # Panduan Instalasi
│   ├── DEPLOYMENT.md             # Panduan Deployment
│   ├── DATABASE_SCHEMA.md        # Dokumentasi Database
│   ├── USER_GUIDE_ADMIN.md       # Panduan Admin
│   ├── USER_GUIDE_EXECUTIVE.md   # Panduan Eksekutif
│   ├── FAQ.md                    # FAQ
│   └── CODEBASE_STRUCTURE.md     # Dokumen ini
│
├── public/                       # Public Assets
│   ├── index.php                 # Entry Point
│   ├── build/                    # Compiled Assets (Vite)
│   └── storage/                  # Symlink ke storage/app/public
│
├── resources/                    # Resources (Views, Assets)
│   ├── css/                      # CSS Source
│   │   └── app.css               # Main CSS (Tailwind + Custom)
│   │
│   ├── js/                       # JavaScript Source
│   │   ├── app.js                # Main JS (Alpine.js)
│   │   └── bootstrap.js          # Bootstrap Configuration
│   │
│   └── views/                    # Blade Templates
│       ├── admin/                # View modul Admin
│       │   ├── users/            # CRUD User
│       │   ├── roles/            # CRUD Role
│       │   ├── audit-logs/       # Audit Log
│       │   └── settings/         # Pengaturan Sistem
│       │
│       ├── auth/                 # View Autentikasi
│       │   ├── login.blade.php
│       │   └── change-password.blade.php
│       │
│       ├── components/           # Blade Components
│       │   ├── layouts/          # Layout Components
│       │   │   ├── app.blade.php # Main App Layout
│       │   │   └── auth.blade.php
│       │   │
│       │   ├── button.blade.php  # Button Component
│       │   ├── input.blade.php   # Input Component
│       │   ├── modal.blade.php   # Modal Component
│       │   ├── alert.blade.php   # Alert Component
│       │   ├── glass-card.blade.php
│       │   ├── breadcrumb.blade.php
│       │   └── ...
│       │
│       ├── documents/            # View Dokumen
│       │   ├── index.blade.php   # Daftar Dokumen
│       │   ├── show.blade.php    # Detail Dokumen
│       │   ├── create.blade.php  # Form Tambah
│       │   ├── edit.blade.php    # Form Edit
│       │   └── ...
│       │
│       ├── layouts/              # Legacy Layouts
│       │   └── partials/         # Partials (sidebar, navbar, etc.)
│       │
│       ├── master/               # View Master Data
│       │   ├── directorates/
│       │   ├── units/
│       │   ├── document-types/
│       │   └── document-categories/
│       │
│       ├── notifications/        # View Notifikasi
│       ├── profile/              # View Profil
│       └── dashboard.blade.php   # Dashboard
│
├── routes/                       # Route Definitions
│   ├── web.php                   # Web Routes
│   ├── api.php                   # API Routes (if needed)
│   └── console.php               # Console Routes
│
├── storage/                      # Storage Directory
│   ├── app/                      # Application Files
│   │   ├── documents/            # Uploaded Documents
│   │   ├── temp/                 # Temporary Files
│   │   └── public/               # Public Files
│   ├── framework/                # Framework Cache
│   └── logs/                     # Application Logs
│
├── tests/                        # Testing
│   ├── Feature/                  # Feature Tests
│   │   ├── AuthenticationTest.php
│   │   ├── PasswordChangeTest.php
│   │   ├── DocumentCrudTest.php
│   │   ├── DocumentDownloadTest.php
│   │   └── DocumentSearchFilterTest.php
│   │
│   └── Unit/                     # Unit Tests
│       ├── DocumentStatusServiceTest.php
│       ├── PdfWatermarkServiceTest.php
│       └── DocumentsExportTest.php
│
├── vendor/                       # Composer Dependencies
├── node_modules/                 # NPM Dependencies
│
├── .env                          # Environment Variables
├── .env.example                  # Example Environment
├── composer.json                 # PHP Dependencies
├── package.json                  # JS Dependencies
├── tailwind.config.js            # Tailwind Configuration
├── vite.config.js                # Vite Configuration
├── phpunit.xml                   # PHPUnit Configuration
└── tasks.md                      # Project Tasks
```

---

## 🎯 Layer Aplikasi

### 1. Routes Layer

File: `routes/web.php`

```php
// Contoh struktur routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::resource('documents', DocumentController::class);
    
    Route::prefix('admin')->middleware('can:admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
    });
});
```

### 2. Middleware Layer

| Middleware | Fungsi |
|------------|--------|
| `CheckRolePermission` | Cek permission berdasarkan route |
| `AuditLogMiddleware` | Log semua aktivitas user |
| `ForcePasswordChange` | Paksa ganti password jika `must_change_password` |

### 3. Controller Layer

Controllers bertanggung jawab untuk:
- Menerima request dari routes
- Validasi input
- Memanggil service/model yang diperlukan
- Mengembalikan response (view/JSON)

```php
// Contoh Controller
class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = Document::query()
            ->with(['type', 'unit', 'creator'])
            ->filter($request->all())
            ->paginate(20);
            
        return view('documents.index', compact('documents'));
    }
}
```

### 4. Service Layer

Services mengandung business logic yang kompleks:

| Service | Fungsi |
|---------|--------|
| `DocumentStatusService` | Menghitung status dokumen (expired, expiring, active) |
| `PdfWatermarkService` | Menambahkan watermark ke PDF |

```php
// Contoh Service
class DocumentStatusService
{
    public function calculateStatus(Document $document): string
    {
        if (!$document->expiry_date) return 'perpetual';
        
        $days = now()->diffInDays($document->expiry_date, false);
        
        if ($days < 0) return 'expired';
        if ($days <= 30) return 'critical';
        if ($days <= 90) return 'warning';
        
        return 'active';
    }
}
```

### 5. Model Layer

Models merepresentasikan tabel database:

```php
// Contoh Model dengan Relationships
class Document extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = ['title', 'document_number', ...];
    
    // Relationships
    public function type(): BelongsTo { ... }
    public function versions(): HasMany { ... }
    public function creator(): BelongsTo { ... }
    
    // Scopes
    public function scopeActive($query) { ... }
    public function scopeExpired($query) { ... }
    
    // Accessors
    public function isExpired(): bool { ... }
}
```

---

## 🧩 Komponen Utama

### Blade Components

| Component | Path | Fungsi |
|-----------|------|--------|
| `<x-layouts.app>` | `components/layouts/app.blade.php` | Layout utama |
| `<x-glass-card>` | `components/glass-card.blade.php` | Glassmorphism card |
| `<x-button>` | `components/button.blade.php` | Button dengan variants |
| `<x-input>` | `components/input.blade.php` | Input field |
| `<x-modal>` | `components/modal.blade.php` | Modal dialog |
| `<x-alert>` | `components/alert.blade.php` | Alert messages |

### Alpine.js Components

| Component | Lokasi | Fungsi |
|-----------|--------|--------|
| `documentsIndex()` | `documents/index.blade.php` | State management daftar dokumen |
| `swipeableCard()` | `documents/index.blade.php` | Swipe gestures mobile |
| `filterPresets()` | `documents/index.blade.php` | Simpan preset filter |
| `searchAutocomplete()` | `documents/index.blade.php` | Autocomplete pencarian |

---

## 🔄 Alur Data

### 1. Alur Request-Response

```
User Action
    ↓
Browser Request (HTTP)
    ↓
Route Matching (web.php)
    ↓
Middleware Stack
    ↓
Controller Method
    ↓
Service (if needed)
    ↓
Model / Database Query
    ↓
View Rendering (Blade)
    ↓
Browser Response (HTML)
```

### 2. Alur Upload Dokumen

```
User uploads file
    ↓
DocumentController@store
    ↓
Validation (title, type, file, etc.)
    ↓
Store file to storage/app/documents
    ↓
Create Document record
    ↓
Create DocumentVersion record
    ↓
Create DocumentHistory record
    ↓
Return redirect with success message
```

### 3. Alur Download Dokumen

```
User clicks download
    ↓
DocumentController@download
    ↓
Check permission (can:documents.download)
    ↓
Get document version file
    ↓
Apply watermark (PdfWatermarkService)
    ↓
Log download to DocumentHistory
    ↓
Return file response
```

---

## 📝 Konvensi Kode

### Naming Conventions

| Tipe | Konvensi | Contoh |
|------|----------|--------|
| Model | Singular, PascalCase | `Document`, `DocumentType` |
| Controller | Singular + Controller | `DocumentController` |
| Migration | Snake_case, descriptive | `create_documents_table` |
| View | Kebab-case | `document-types/index.blade.php` |
| Component | Kebab-case | `<x-glass-card>` |
| Route Name | Dot notation | `documents.index`, `admin.users.store` |

### Folder Organization

```php
// Controllers dikelompokkan berdasarkan modul
app/Http/Controllers/
├── Admin/          # Semua controller admin
├── Auth/           # Autentikasi
├── Master/         # Master data
└── DocumentController.php  # Dokumen (utama)

// Views mengikuti struktur controller
resources/views/
├── admin/
│   ├── users/
│   └── roles/
├── master/
│   ├── directorates/
│   └── document-types/
└── documents/
```

### Best Practices

1. **Fat Models, Skinny Controllers** - Logika di model/service
2. **Form Request Validation** - Validasi di FormRequest class
3. **Blade Components** - Reusable UI components
4. **Query Scopes** - Filter queries via model scopes
5. **Repository Pattern** - (Optional) Untuk complex queries
6. **Service Pattern** - Business logic di services
7. **Event/Listener** - Untuk side effects (notifications, logs)

---

## 🔗 Referensi Tambahan

- [Laravel 11 Documentation](https://laravel.com/docs/11.x)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Alpine.js Documentation](https://alpinejs.dev/start-here)
- [Maatwebsite Excel](https://docs.laravel-excel.com/3.1/)
- [FPDF/FPDI Documentation](https://www.setasign.com/products/fpdi/about/)

---

*Dokumen ini adalah bagian dari Sistem Manajemen Dokumen Hukum Terpusat RS Ngoerah*
