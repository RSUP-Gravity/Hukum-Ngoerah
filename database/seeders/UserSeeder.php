<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin
        User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@rsngoerah.go.id',
                'password' => Hash::make('password123'),
                'role_id' => Role::where('name', 'super_admin')->first()?->id,
                'unit_id' => Unit::where('code', 'TI')->first()?->id,
                'position_id' => Position::where('code', 'KABAG')->first()?->id,
                'employee_id' => 'EMP001',
                'is_active' => true,
            ]
        );

        // Admin
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin@rsngoerah.go.id',
                'password' => Hash::make('password123'),
                'role_id' => Role::where('name', 'admin')->first()?->id,
                'unit_id' => Unit::where('code', 'TI')->first()?->id,
                'position_id' => Position::where('code', 'STAF')->first()?->id,
                'employee_id' => 'EMP002',
                'is_active' => true,
            ]
        );

        // Legal Head
        User::updateOrCreate(
            ['username' => 'kabag.hukum'],
            [
                'name' => 'Kepala Bagian Hukum',
                'email' => 'kabag.hukum@rsngoerah.go.id',
                'password' => Hash::make('password123'),
                'role_id' => Role::where('name', 'legal_head')->first()?->id,
                'unit_id' => Unit::where('code', 'HUKUM')->first()?->id,
                'position_id' => Position::where('code', 'KABAG')->first()?->id,
                'employee_id' => 'EMP003',
                'is_active' => true,
            ]
        );

        // Legal Staff
        User::updateOrCreate(
            ['username' => 'staf.hukum1'],
            [
                'name' => 'Staf Hukum 1',
                'email' => 'staf.hukum1@rsngoerah.go.id',
                'password' => Hash::make('password123'),
                'role_id' => Role::where('name', 'legal_staff')->first()?->id,
                'unit_id' => Unit::where('code', 'HUKUM')->first()?->id,
                'position_id' => Position::where('code', 'STAF')->first()?->id,
                'employee_id' => 'EMP004',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['username' => 'staf.hukum2'],
            [
                'name' => 'Staf Hukum 2',
                'email' => 'staf.hukum2@rsngoerah.go.id',
                'password' => Hash::make('password123'),
                'role_id' => Role::where('name', 'legal_staff')->first()?->id,
                'unit_id' => Unit::where('code', 'HUKUM')->first()?->id,
                'position_id' => Position::where('code', 'STAF')->first()?->id,
                'employee_id' => 'EMP005',
                'is_active' => true,
            ]
        );

        // Unit Head - SDM
        User::updateOrCreate(
            ['username' => 'kabag.sdm'],
            [
                'name' => 'Kepala Bagian SDM',
                'email' => 'kabag.sdm@rsngoerah.go.id',
                'password' => Hash::make('password123'),
                'role_id' => Role::where('name', 'unit_head')->first()?->id,
                'unit_id' => Unit::where('code', 'SDM')->first()?->id,
                'position_id' => Position::where('code', 'KABAG')->first()?->id,
                'employee_id' => 'EMP006',
                'is_active' => true,
            ]
        );

        // Unit Staff - SDM
        User::updateOrCreate(
            ['username' => 'staf.sdm'],
            [
                'name' => 'Staf SDM',
                'email' => 'staf.sdm@rsngoerah.go.id',
                'password' => Hash::make('password123'),
                'role_id' => Role::where('name', 'unit_staff')->first()?->id,
                'unit_id' => Unit::where('code', 'SDM')->first()?->id,
                'position_id' => Position::where('code', 'STAF')->first()?->id,
                'employee_id' => 'EMP007',
                'is_active' => true,
            ]
        );

        // Viewer
        User::updateOrCreate(
            ['username' => 'viewer'],
            [
                'name' => 'Viewer User',
                'email' => 'viewer@rsngoerah.go.id',
                'password' => Hash::make('password123'),
                'role_id' => Role::where('name', 'viewer')->first()?->id,
                'unit_id' => null,
                'position_id' => null,
                'employee_id' => 'EMP008',
                'is_active' => true,
            ]
        );
    }
}
