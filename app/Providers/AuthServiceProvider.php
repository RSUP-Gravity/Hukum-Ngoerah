<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Define permission-based gates
        Gate::before(function (User $user, string $ability) {
            // Super admin bypasses all permission checks
            if ($user->role && $user->role->name === 'super_admin') {
                return true;
            }
        });

        // Define gates for each permission
        $permissions = [
            // Dashboard
            'dashboard.view',
            
            // Documents
            'documents.view',
            'documents.create',
            'documents.edit',
            'documents.delete',
            'documents.download',
            'documents.print',
            'documents.approve',
            'documents.publish',
            'documents.archive',
            'documents.manage_access',
            
            // Master Data
            'master.view',
            'master.create',
            'master.edit',
            'master.delete',
            
            // Users
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.reset_password',
            
            // Admin
            'admin.roles',
            'admin.audit_logs',
            'admin.settings',
            'admin.user_analytics',
            
            // Reports
            'reports.view',
            'reports.export',
        ];

        foreach ($permissions as $permission) {
            Gate::define($permission, function (User $user) use ($permission) {
                return $user->hasPermission($permission);
            });
        }

        Gate::define('manage-users', function (User $user) {
            return $user->hasAnyPermission([
                'users.view',
                'users.create',
                'users.edit',
                'users.delete',
                'users.reset_password',
            ]);
        });

        Gate::define('manage users', function (User $user) {
            return $user->hasAnyPermission([
                'users.view',
                'users.create',
                'users.edit',
                'users.delete',
                'users.reset_password',
            ]);
        });

        Gate::define('manage-master-data', function (User $user) {
            return $user->hasAnyPermission([
                'master.view',
                'master.create',
                'master.edit',
                'master.delete',
            ]);
        });

        Gate::define('manage master data', function (User $user) {
            return $user->hasAnyPermission([
                'master.view',
                'master.create',
                'master.edit',
                'master.delete',
            ]);
        });
    }
}
