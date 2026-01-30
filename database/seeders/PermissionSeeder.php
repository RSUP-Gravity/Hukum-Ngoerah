<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Documents module
            ['name' => 'documents.view', 'display_name' => 'Lihat Dokumen', 'module' => 'documents'],
            ['name' => 'documents.view_all', 'display_name' => 'Lihat Semua Dokumen', 'module' => 'documents'],
            ['name' => 'documents.create', 'display_name' => 'Buat Dokumen', 'module' => 'documents'],
            ['name' => 'documents.edit', 'display_name' => 'Edit Dokumen', 'module' => 'documents'],
            ['name' => 'documents.edit_all', 'display_name' => 'Edit Semua Dokumen', 'module' => 'documents'],
            ['name' => 'documents.delete', 'display_name' => 'Hapus Dokumen', 'module' => 'documents'],
            ['name' => 'documents.delete_all', 'display_name' => 'Hapus Semua Dokumen', 'module' => 'documents'],
            ['name' => 'documents.approve', 'display_name' => 'Approve Dokumen', 'module' => 'documents'],
            ['name' => 'documents.publish', 'display_name' => 'Publish Dokumen', 'module' => 'documents'],
            ['name' => 'documents.archive', 'display_name' => 'Arsip Dokumen', 'module' => 'documents'],
            ['name' => 'documents.download', 'display_name' => 'Download Dokumen', 'module' => 'documents'],
            ['name' => 'documents.print', 'display_name' => 'Print Dokumen', 'module' => 'documents'],
            ['name' => 'documents.share', 'display_name' => 'Share Dokumen', 'module' => 'documents'],
            ['name' => 'documents.manage_access', 'display_name' => 'Kelola Akses Dokumen', 'module' => 'documents'],

            // Users module
            ['name' => 'users.view', 'display_name' => 'Lihat Pengguna', 'module' => 'users'],
            ['name' => 'users.create', 'display_name' => 'Buat Pengguna', 'module' => 'users'],
            ['name' => 'users.edit', 'display_name' => 'Edit Pengguna', 'module' => 'users'],
            ['name' => 'users.delete', 'display_name' => 'Hapus Pengguna', 'module' => 'users'],
            ['name' => 'users.assign_role', 'display_name' => 'Assign Role', 'module' => 'users'],
            ['name' => 'users.reset_password', 'display_name' => 'Reset Password', 'module' => 'users'],

            // Master data module
            ['name' => 'master.view', 'display_name' => 'Lihat Data Master', 'module' => 'master_data'],
            ['name' => 'master.create', 'display_name' => 'Buat Data Master', 'module' => 'master_data'],
            ['name' => 'master.edit', 'display_name' => 'Edit Data Master', 'module' => 'master_data'],
            ['name' => 'master.delete', 'display_name' => 'Hapus Data Master', 'module' => 'master_data'],

            // Admin module
            ['name' => 'admin.settings', 'display_name' => 'Kelola Pengaturan', 'module' => 'admin'],
            ['name' => 'admin.audit_logs', 'display_name' => 'Lihat Audit Log', 'module' => 'admin'],
            ['name' => 'admin.system_info', 'display_name' => 'Lihat Info Sistem', 'module' => 'admin'],
            ['name' => 'admin.backup', 'display_name' => 'Backup Database', 'module' => 'admin'],
            ['name' => 'admin.roles', 'display_name' => 'Kelola Role', 'module' => 'admin'],
            ['name' => 'admin.permissions', 'display_name' => 'Kelola Permission', 'module' => 'admin'],
            ['name' => 'admin.user_analytics', 'display_name' => 'User Analytic', 'module' => 'admin'],

            // Reports module
            ['name' => 'reports.view', 'display_name' => 'Lihat Laporan', 'module' => 'reports'],
            ['name' => 'reports.export', 'display_name' => 'Export Laporan', 'module' => 'reports'],
            ['name' => 'reports.dashboard', 'display_name' => 'Dashboard Analytics', 'module' => 'reports'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Assign permissions to roles
        $this->assignPermissions();
    }

    private function assignPermissions(): void
    {
        // Super Admin - all permissions
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->permissions()->sync(Permission::pluck('id'));
        }

        // Admin - most permissions except some admin
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $adminPermissions = Permission::whereNotIn('name', [
                'admin.backup',
                'admin.system_info',
            ])->pluck('id');
            $admin->permissions()->sync($adminPermissions);
        }

        // Legal Head
        $legalHead = Role::where('name', 'legal_head')->first();
        if ($legalHead) {
            $permissions = Permission::whereIn('name', [
                'documents.view',
                'documents.view_all',
                'documents.create',
                'documents.edit',
                'documents.edit_all',
                'documents.delete',
                'documents.approve',
                'documents.publish',
                'documents.archive',
                'documents.download',
                'documents.print',
                'documents.share',
                'documents.manage_access',
                'master.view',
                'reports.view',
                'reports.export',
                'reports.dashboard',
            ])->pluck('id');
            $legalHead->permissions()->sync($permissions);
        }

        // Legal Staff
        $legalStaff = Role::where('name', 'legal_staff')->first();
        if ($legalStaff) {
            $permissions = Permission::whereIn('name', [
                'documents.view',
                'documents.view_all',
                'documents.create',
                'documents.edit',
                'documents.delete',
                'documents.download',
                'documents.print',
                'documents.share',
                'master.view',
                'reports.view',
            ])->pluck('id');
            $legalStaff->permissions()->sync($permissions);
        }

        // Unit Head
        $unitHead = Role::where('name', 'unit_head')->first();
        if ($unitHead) {
            $permissions = Permission::whereIn('name', [
                'documents.view',
                'documents.create',
                'documents.edit',
                'documents.approve',
                'documents.download',
                'documents.print',
                'master.view',
                'reports.view',
            ])->pluck('id');
            $unitHead->permissions()->sync($permissions);
        }

        // Unit Staff
        $unitStaff = Role::where('name', 'unit_staff')->first();
        if ($unitStaff) {
            $permissions = Permission::whereIn('name', [
                'documents.view',
                'documents.create',
                'documents.edit',
                'documents.download',
                'documents.print',
                'master.view',
            ])->pluck('id');
            $unitStaff->permissions()->sync($permissions);
        }

        // Viewer
        $viewer = Role::where('name', 'viewer')->first();
        if ($viewer) {
            $permissions = Permission::whereIn('name', [
                'documents.view',
                'documents.download',
            ])->pluck('id');
            $viewer->permissions()->sync($permissions);
        }
    }
}
