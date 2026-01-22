<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrator',
                'description' => 'Akses penuh ke seluruh sistem',
                'level' => 100,
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Mengelola pengguna dan data master',
                'level' => 90,
            ],
            [
                'name' => 'legal_head',
                'display_name' => 'Kepala Bagian Hukum',
                'description' => 'Approval final dan pengawasan dokumen hukum',
                'level' => 80,
            ],
            [
                'name' => 'legal_staff',
                'display_name' => 'Staf Hukum',
                'description' => 'Membuat dan mengelola dokumen hukum',
                'level' => 60,
            ],
            [
                'name' => 'unit_head',
                'display_name' => 'Kepala Unit',
                'description' => 'Approval dokumen tingkat unit',
                'level' => 70,
            ],
            [
                'name' => 'unit_staff',
                'display_name' => 'Staf Unit',
                'description' => 'Mengajukan dan melihat dokumen unit',
                'level' => 40,
            ],
            [
                'name' => 'viewer',
                'display_name' => 'Viewer',
                'description' => 'Hanya dapat melihat dokumen yang dipublikasi',
                'level' => 10,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}
