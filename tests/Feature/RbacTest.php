<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $staffUser;
    protected User $executiveUser;
    protected Role $adminRole;
    protected Role $staffRole;
    protected Role $executiveRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        $this->createPermissions();

        // Create roles
        $this->adminRole = Role::factory()->create([
            'name' => 'admin',
            'slug' => 'admin',
        ]);

        $this->staffRole = Role::factory()->create([
            'name' => 'Staff',
            'slug' => 'staff',
        ]);

        $this->executiveRole = Role::factory()->create([
            'name' => 'Executive',
            'slug' => 'executive',
        ]);

        // Assign permissions to roles
        $this->assignPermissionsToRoles();

        // Create users
        $this->adminUser = User::factory()->create([
            'role_id' => $this->adminRole->id,
        ]);

        $this->staffUser = User::factory()->create([
            'role_id' => $this->staffRole->id,
        ]);

        $this->executiveUser = User::factory()->create([
            'role_id' => $this->executiveRole->id,
        ]);
    }

    protected function createPermissions(): void
    {
        $permissions = [
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
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.reset_password',
            'master.view',
            'master.create',
            'master.edit',
            'master.delete',
            'admin.roles',
            'admin.audit_logs',
            'admin.settings',
        ];

        foreach ($permissions as $permission) {
            Permission::factory()->create([
                'name' => ucwords(str_replace(['.', '_'], ' ', $permission)),
                'slug' => $permission,
            ]);
        }
    }

    protected function assignPermissionsToRoles(): void
    {
        // Admin gets all permissions
        $allPermissions = Permission::all();
        $this->adminRole->permissions()->attach($allPermissions->pluck('id'));

        // Staff gets limited permissions
        $staffPermissions = Permission::whereIn('slug', [
            'documents.view',
            'documents.create',
            'documents.edit',
            'documents.download',
            'documents.print',
        ])->get();
        $this->staffRole->permissions()->attach($staffPermissions->pluck('id'));

        // Executive gets view-only permissions
        $executivePermissions = Permission::whereIn('slug', [
            'documents.view',
            'documents.download',
            'documents.print',
        ])->get();
        $this->executiveRole->permissions()->attach($executivePermissions->pluck('id'));
    }

    /** @test */
    public function admin_can_access_user_management(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.users.index'));

        $response->assertOk();
    }

    /** @test */
    public function staff_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->staffUser)
            ->get(route('admin.users.index'));

        $response->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function executive_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->executiveUser)
            ->get(route('admin.users.index'));

        $response->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function admin_can_create_documents(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('documents.create'));

        $response->assertOk();
    }

    /** @test */
    public function staff_can_create_documents(): void
    {
        $response = $this->actingAs($this->staffUser)
            ->get(route('documents.create'));

        $response->assertOk();
    }

    /** @test */
    public function executive_cannot_create_documents(): void
    {
        $response = $this->actingAs($this->executiveUser)
            ->get(route('documents.create'));

        $response->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function admin_can_access_role_management(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.roles.index'));

        $response->assertOk();
    }

    /** @test */
    public function staff_cannot_access_role_management(): void
    {
        $response = $this->actingAs($this->staffUser)
            ->get(route('admin.roles.index'));

        $response->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function admin_can_view_audit_logs(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.audit-logs.index'));

        $response->assertOk();
    }

    /** @test */
    public function staff_cannot_view_audit_logs(): void
    {
        $response = $this->actingAs($this->staffUser)
            ->get(route('admin.audit-logs.index'));

        $response->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function admin_can_access_system_settings(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.settings.index'));

        $response->assertOk();
    }

    /** @test */
    public function staff_cannot_access_system_settings(): void
    {
        $response = $this->actingAs($this->staffUser)
            ->get(route('admin.settings.index'));

        $response->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function all_users_can_view_document_list(): void
    {
        // All roles should be able to view documents
        $this->actingAs($this->adminUser)
            ->get(route('documents.index'))
            ->assertOk();

        $this->actingAs($this->staffUser)
            ->get(route('documents.index'))
            ->assertOk();

        $this->actingAs($this->executiveUser)
            ->get(route('documents.index'))
            ->assertOk();
    }

    /** @test */
    public function staff_can_edit_documents(): void
    {
        // Create a document
        $unit = Unit::factory()->create();
        $type = DocumentType::factory()->create();
        
        $document = Document::factory()->create([
            'unit_id' => $unit->id,
            'document_type_id' => $type->id,
            'created_by' => $this->staffUser->id,
            'updated_by' => $this->staffUser->id,
        ]);

        $response = $this->actingAs($this->staffUser)
            ->get(route('documents.edit', $document));

        $response->assertOk();
    }

    /** @test */
    public function executive_cannot_edit_documents(): void
    {
        // Create a document
        $unit = Unit::factory()->create();
        $type = DocumentType::factory()->create();
        
        $document = Document::factory()->create([
            'unit_id' => $unit->id,
            'document_type_id' => $type->id,
            'created_by' => $this->adminUser->id,
            'updated_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->executiveUser)
            ->get(route('documents.edit', $document));

        $response->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function admin_can_delete_documents(): void
    {
        // Create a document
        $unit = Unit::factory()->create();
        $type = DocumentType::factory()->create();
        
        $document = Document::factory()->create([
            'unit_id' => $unit->id,
            'document_type_id' => $type->id,
            'created_by' => $this->adminUser->id,
            'updated_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('documents.destroy', $document));

        $response->assertRedirect();
        $this->assertSoftDeleted('documents', ['id' => $document->id]);
    }

    /** @test */
    public function staff_cannot_delete_documents(): void
    {
        // Create a document
        $unit = Unit::factory()->create();
        $type = DocumentType::factory()->create();
        
        $document = Document::factory()->create([
            'unit_id' => $unit->id,
            'document_type_id' => $type->id,
            'created_by' => $this->adminUser->id,
            'updated_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->staffUser)
            ->delete(route('documents.destroy', $document));

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('documents', ['id' => $document->id, 'deleted_at' => null]);
    }

    /** @test */
    public function admin_can_access_master_data(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('master.directorates.index'));

        $response->assertOk();
    }

    /** @test */
    public function staff_cannot_create_master_data(): void
    {
        $response = $this->actingAs($this->staffUser)
            ->get(route('master.directorates.create'));

        $response->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function super_admin_bypasses_all_checks(): void
    {
        // Create super admin role
        $superAdminRole = Role::factory()->create([
            'name' => 'Super Admin',
            'slug' => 'super_admin',
        ]);

        $superAdmin = User::factory()->create([
            'role_id' => $superAdminRole->id,
        ]);

        // Super admin should be able to access everything
        $this->actingAs($superAdmin)
            ->get(route('admin.users.index'))
            ->assertOk();

        $this->actingAs($superAdmin)
            ->get(route('admin.roles.index'))
            ->assertOk();

        $this->actingAs($superAdmin)
            ->get(route('admin.settings.index'))
            ->assertOk();

        $this->actingAs($superAdmin)
            ->get(route('admin.audit-logs.index'))
            ->assertOk();
    }

    /** @test */
    public function user_has_permission_returns_correct_value(): void
    {
        $this->assertTrue($this->adminUser->hasPermission('documents.view'));
        $this->assertTrue($this->adminUser->hasPermission('users.view'));
        $this->assertTrue($this->adminUser->hasPermission('admin.roles'));

        $this->assertTrue($this->staffUser->hasPermission('documents.view'));
        $this->assertTrue($this->staffUser->hasPermission('documents.create'));
        $this->assertFalse($this->staffUser->hasPermission('users.view'));
        $this->assertFalse($this->staffUser->hasPermission('admin.roles'));

        $this->assertTrue($this->executiveUser->hasPermission('documents.view'));
        $this->assertFalse($this->executiveUser->hasPermission('documents.create'));
        $this->assertFalse($this->executiveUser->hasPermission('documents.edit'));
    }

    /** @test */
    public function user_has_role_returns_correct_value(): void
    {
        $this->assertTrue($this->adminUser->hasRole('admin'));
        $this->assertFalse($this->adminUser->hasRole('staff'));

        $this->assertTrue($this->staffUser->hasRole('staff'));
        $this->assertFalse($this->staffUser->hasRole('admin'));

        $this->assertTrue($this->executiveUser->hasRole('executive'));
        $this->assertFalse($this->executiveUser->hasRole('admin'));
    }

    /** @test */
    public function user_has_any_role_works_correctly(): void
    {
        $this->assertTrue($this->adminUser->hasAnyRole(['admin', 'staff']));
        $this->assertTrue($this->staffUser->hasAnyRole(['admin', 'staff']));
        $this->assertFalse($this->executiveUser->hasAnyRole(['admin', 'staff']));
    }

    /** @test */
    public function inactive_user_cannot_access_protected_routes(): void
    {
        // Deactivate staff user
        $this->staffUser->update(['is_active' => false]);

        $response = $this->actingAs($this->staffUser)
            ->get(route('documents.index'));

        // Should be redirected or blocked
        $response->assertRedirect();
    }
}
