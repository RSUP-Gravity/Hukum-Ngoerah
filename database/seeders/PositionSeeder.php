<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            ['code' => 'DIRUT', 'name' => 'Direktur Utama', 'level' => 100, 'can_approve_documents' => true],
            ['code' => 'DIREKTUR', 'name' => 'Direktur', 'level' => 90, 'can_approve_documents' => true],
            ['code' => 'KABAG', 'name' => 'Kepala Bagian', 'level' => 80, 'can_approve_documents' => true],
            ['code' => 'KABID', 'name' => 'Kepala Bidang', 'level' => 80, 'can_approve_documents' => true],
            ['code' => 'KAINSTAL', 'name' => 'Kepala Instalasi', 'level' => 70, 'can_approve_documents' => true],
            ['code' => 'KASUBAG', 'name' => 'Kepala Sub Bagian', 'level' => 60, 'can_approve_documents' => true],
            ['code' => 'KASIE', 'name' => 'Kepala Seksi', 'level' => 60, 'can_approve_documents' => true],
            ['code' => 'KOORDINATOR', 'name' => 'Koordinator', 'level' => 50, 'can_approve_documents' => true],
            ['code' => 'STAF', 'name' => 'Staf', 'level' => 30, 'can_approve_documents' => false],
            ['code' => 'PELAKSANA', 'name' => 'Pelaksana', 'level' => 20, 'can_approve_documents' => false],
        ];

        foreach ($positions as $index => $position) {
            Position::updateOrCreate(
                ['code' => $position['code']],
                array_merge($position, ['sort_order' => $index + 1])
            );
        }
    }
}
