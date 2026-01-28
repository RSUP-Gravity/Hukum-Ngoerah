<?php

namespace Tests\Feature;

use App\Models\Directorate;
use App\Models\DocumentCategory;
use App\Models\DocumentType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MasterDataBackendTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $this->seedMasterPermissions();
    }

    private function seedMasterPermissions(): void
    {
        $permissions = [
            'master.view' => ['display_name' => 'Lihat Data Master'],
            'master.create' => ['display_name' => 'Buat Data Master'],
            'master.edit' => ['display_name' => 'Edit Data Master'],
            'master.delete' => ['display_name' => 'Hapus Data Master'],
        ];

        foreach ($permissions as $name => $meta) {
            Permission::factory()->create([
                'name' => $name,
                'display_name' => $meta['display_name'],
                'module' => 'master_data',
            ]);
        }
    }

    private function createRoleWithPermissions(array $permissionNames): Role
    {
        $role = Role::factory()->create([
            'name' => 'role_' . Str::random(6),
        ]);

        $permissionIds = Permission::whereIn('name', $permissionNames)->pluck('id');
        $role->permissions()->attach($permissionIds);

        return $role;
    }

    private function createUserWithPermissions(array $permissionNames): User
    {
        $role = $this->createRoleWithPermissions($permissionNames);

        return User::factory()->create([
            'role_id' => $role->id,
        ]);
    }

    /** @test */
    public function user_with_master_view_can_access_master_index(): void
    {
        $user = $this->createUserWithPermissions(['master.view']);

        $response = $this->actingAs($user)
            ->get(route('master.directorates.index'));

        $response->assertOk();
    }

    /** @test */
    public function user_without_master_create_cannot_store_master_data(): void
    {
        $user = $this->createUserWithPermissions(['master.view']);

        $response = $this->actingAs($user)
            ->post(route('master.directorates.store'), []);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function user_with_master_create_can_store_directorate(): void
    {
        $user = $this->createUserWithPermissions(['master.view', 'master.create']);

        $payload = [
            'code' => 'DRT',
            'name' => 'Direktorat Test',
            'description' => 'Deskripsi',
            'sort_order' => 2,
            'is_active' => 1,
        ];

        $response = $this->actingAs($user)
            ->post(route('master.directorates.store'), $payload);

        $response->assertRedirect(route('master.directorates.index'));
        $this->assertDatabaseHas('directorates', [
            'code' => 'DRT',
            'name' => 'Direktorat Test',
            'sort_order' => 2,
        ]);
    }

    /** @test */
    public function unit_sort_order_is_assigned_when_directorate_changes_without_input(): void
    {
        $user = $this->createUserWithPermissions(['master.view', 'master.edit']);

        $oldDirectorate = Directorate::factory()->create();
        $newDirectorate = Directorate::factory()->create();

        Unit::factory()->forDirectorate($newDirectorate)->create(['sort_order' => 3]);

        $unit = Unit::factory()->forDirectorate($oldDirectorate)->create([
            'sort_order' => 1,
        ]);

        $payload = [
            'directorate_id' => $newDirectorate->id,
            'code' => $unit->code,
            'name' => $unit->name,
            'description' => $unit->description,
            'sort_order' => '',
            'is_active' => 1,
        ];

        $response = $this->actingAs($user)
            ->put(route('master.units.update', $unit), $payload);

        $response->assertRedirect(route('master.units.index'));
        $unit->refresh();

        $this->assertSame($newDirectorate->id, $unit->directorate_id);
        $this->assertSame(4, $unit->sort_order);
    }

    /** @test */
    public function document_type_delete_is_blocked_when_categories_exist(): void
    {
        $user = $this->createUserWithPermissions(['master.view', 'master.delete']);

        $documentType = DocumentType::factory()->create([
            'code' => 'DT',
            'name' => 'Dokumen Test',
            'prefix' => 'DT',
        ]);

        DocumentCategory::create([
            'document_type_id' => $documentType->id,
            'code' => 'CAT',
            'name' => 'Kategori Test',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->from(route('master.document-types.index'))
            ->delete(route('master.document-types.destroy', $documentType));

        $response->assertRedirect(route('master.document-types.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('document_types', [
            'id' => $documentType->id,
            'deleted_at' => null,
        ]);
    }
}
