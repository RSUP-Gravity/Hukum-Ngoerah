<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Master\DirectorateController;
use App\Http\Controllers\Master\UnitController;
use App\Http\Controllers\Master\PositionController;
use App\Http\Controllers\Master\DocumentTypeController;
use App\Http\Controllers\Master\DocumentCategoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\SystemSettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Hukum RS Ngoerah - Legal Document Management System
|
*/

// Public Routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Guest Routes (not authenticated)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Password Change
    Route::get('/password/change', [PasswordController::class, 'showChangeForm'])->name('password.change');
    Route::post('/password/change', [PasswordController::class, 'change'])->name('password.update');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::put('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    
    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::get('/count', [NotificationController::class, 'unreadCount'])->name('count');
        Route::get('/recent', [NotificationController::class, 'recent'])->name('recent');
    });
    
    // Documents
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('index')
            ->middleware('permission:documents.view');
        Route::get('/export', [DocumentController::class, 'export'])->name('export')
            ->middleware(['permission:documents.view', 'throttle:exports']);
        Route::get('/export-pdf', [DocumentController::class, 'exportPdf'])->name('export-pdf')
            ->middleware(['permission:documents.view', 'throttle:exports']);
        
        // Bulk actions
        Route::post('/bulk-archive', [DocumentController::class, 'bulkArchive'])->name('bulk-archive')
            ->middleware('permission:documents.edit');
        Route::post('/bulk-delete', [DocumentController::class, 'bulkDelete'])->name('bulk-delete')
            ->middleware('permission:documents.delete');
        
        Route::get('/create', [DocumentController::class, 'create'])->name('create')
            ->middleware('permission:documents.create');
        Route::post('/', [DocumentController::class, 'store'])->name('store')
            ->middleware('permission:documents.create');
        Route::get('/{document}', [DocumentController::class, 'show'])->name('show')
            ->middleware('permission:documents.view');
        Route::get('/{document}/edit', [DocumentController::class, 'edit'])->name('edit')
            ->middleware('permission:documents.edit');
        Route::put('/{document}', [DocumentController::class, 'update'])->name('update')
            ->middleware('permission:documents.edit');
        Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy')
            ->middleware('permission:documents.delete');
        
        // Document actions
        Route::get('/{document}/download/{version?}', [DocumentController::class, 'download'])->name('download')
            ->middleware(['permission:documents.download', 'throttle:downloads']);
        Route::get('/{document}/print', [DocumentController::class, 'print'])->name('print')
            ->middleware(['permission:documents.print', 'throttle:downloads']);
        Route::post('/{document}/upload-version', [DocumentController::class, 'uploadVersion'])->name('upload-version')
            ->middleware('permission:documents.edit');
        Route::post('/{document}/submit-review', [DocumentController::class, 'submitForReview'])->name('submit-review')
            ->middleware('permission:documents.create');
        Route::post('/{document}/submit-approval', [DocumentController::class, 'submitForApproval'])->name('submit-approval')
            ->middleware('permission:documents.approve');
        Route::post('/{document}/approve', [DocumentController::class, 'approve'])->name('approve')
            ->middleware('permission:documents.approve');
        Route::post('/{document}/reject', [DocumentController::class, 'reject'])->name('reject')
            ->middleware('permission:documents.approve');
        Route::post('/{document}/publish', [DocumentController::class, 'publish'])->name('publish')
            ->middleware('permission:documents.publish');
        Route::post('/{document}/archive', [DocumentController::class, 'archive'])->name('archive')
            ->middleware('permission:documents.archive');
        
        // Document access management
        Route::get('/{document}/access', [DocumentController::class, 'showAccess'])->name('access')
            ->middleware('permission:documents.manage_access');
        Route::post('/{document}/access', [DocumentController::class, 'storeAccess'])->name('access.store')
            ->middleware('permission:documents.manage_access');
        Route::delete('/{document}/access/{access}', [DocumentController::class, 'revokeAccess'])->name('access.destroy')
            ->middleware('permission:documents.manage_access');
        
        // Version management
        Route::get('/{document}/compare/{version1}/{version2}', [DocumentController::class, 'compareVersions'])->name('compare-versions')
            ->middleware('permission:documents.view');
        Route::post('/{document}/restore-version/{version}', [DocumentController::class, 'restoreVersion'])->name('restore-version')
            ->middleware('role:admin');
        
        // AJAX helpers
        Route::get('/categories-by-type/{type}', [DocumentController::class, 'categoriesByType'])->name('categories-by-type');
        Route::get('/search-suggestions', [DocumentController::class, 'searchSuggestions'])->name('search-suggestions')
            ->middleware('throttle:search');
        
        // Lazy load filter options API
        Route::prefix('api/filters')->name('api.filters.')->group(function () {
            Route::get('/types', [\App\Http\Controllers\Api\FilterOptionsController::class, 'types'])->name('types');
            Route::get('/categories', [\App\Http\Controllers\Api\FilterOptionsController::class, 'categories'])->name('categories');
            Route::get('/units', [\App\Http\Controllers\Api\FilterOptionsController::class, 'units'])->name('units');
            Route::get('/status-counts', [\App\Http\Controllers\Api\FilterOptionsController::class, 'statusCounts'])->name('status-counts');
            Route::get('/all', [\App\Http\Controllers\Api\FilterOptionsController::class, 'all'])->name('all');
        });
    });
    
    // Master Data Routes
    Route::prefix('master')->name('master.')->middleware('permission:master.view')->group(function () {
        // Directorates
        Route::resource('directorates', DirectorateController::class)->middleware([
            'store' => 'permission:master.create',
            'update' => 'permission:master.edit',
            'destroy' => 'permission:master.delete',
        ]);
        
        // Units
        Route::resource('units', UnitController::class)->middleware([
            'store' => 'permission:master.create',
            'update' => 'permission:master.edit',
            'destroy' => 'permission:master.delete',
        ]);
        Route::get('/units-by-directorate/{directorate}', [UnitController::class, 'byDirectorate'])
            ->name('units.by-directorate');
        
        // Positions
        Route::resource('positions', PositionController::class)->middleware([
            'store' => 'permission:master.create',
            'update' => 'permission:master.edit',
            'destroy' => 'permission:master.delete',
        ]);
        
        // Document Types
        Route::resource('document-types', DocumentTypeController::class)->middleware([
            'store' => 'permission:master.create',
            'update' => 'permission:master.edit',
            'destroy' => 'permission:master.delete',
        ]);
        
        // Document Categories
        Route::resource('document-categories', DocumentCategoryController::class)->middleware([
            'store' => 'permission:master.create',
            'update' => 'permission:master.edit',
            'destroy' => 'permission:master.delete',
        ]);
        Route::get('/categories-by-type/{documentType}', [DocumentCategoryController::class, 'byType'])
            ->name('categories.by-type');
    });
    
    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        // Users
        Route::resource('users', UserController::class)->middleware('permission:users.view');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])
            ->name('users.reset-password')
            ->middleware('permission:users.reset_password');
        Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])
            ->name('users.toggle-active')
            ->middleware('permission:users.edit');
        
        // Roles
        Route::resource('roles', RoleController::class)->middleware('permission:admin.roles');
        
        // Audit Logs
        Route::get('/audit-logs', [AuditLogController::class, 'index'])
            ->name('audit-logs.index')
            ->middleware('permission:admin.audit_logs');
        Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])
            ->name('audit-logs.show')
            ->middleware('permission:admin.audit_logs');
        Route::get('/audit-logs/export', [AuditLogController::class, 'export'])
            ->name('audit-logs.export')
            ->middleware('permission:admin.audit_logs');
        
        // System Settings
        Route::get('/settings', [SystemSettingController::class, 'index'])
            ->name('settings.index')
            ->middleware('permission:admin.settings');
        Route::post('/settings', [SystemSettingController::class, 'update'])
            ->name('settings.update')
            ->middleware('permission:admin.settings');
    });
});
