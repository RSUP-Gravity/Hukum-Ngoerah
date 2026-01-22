<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;
    protected Role $adminRole;
    protected Role $userRole;
    protected DocumentType $documentType;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        $permissions = [
            Permission::factory()->documentsView()->create(),
            Permission::factory()->documentsCreate()->create(),
            Permission::factory()->documentsEdit()->create(),
            Permission::factory()->documentsDelete()->create(),
            Permission::factory()->documentsDownload()->create(),
        ];

        // Create admin role with all permissions
        $this->adminRole = Role::factory()->admin()->create();
        $this->adminRole->permissions()->attach(Permission::pluck('id'));

        // Create user role with only view permission
        $this->userRole = Role::factory()->general()->create();
        $this->userRole->permissions()->attach(
            Permission::where('name', 'documents.view')->pluck('id')
        );

        // Create users
        $this->adminUser = User::factory()->create(['role_id' => $this->adminRole->id]);
        $this->regularUser = User::factory()->create(['role_id' => $this->userRole->id]);

        // Create document type
        $this->documentType = DocumentType::factory()->create();

        // Setup fake storage
        Storage::fake('local');
    }

    /**
     * Test documents index page can be rendered.
     */
    public function test_documents_index_can_be_rendered(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/documents');

        $response->assertStatus(200);
    }

    /**
     * Test guests cannot access documents.
     */
    public function test_guests_cannot_access_documents(): void
    {
        $response = $this->get('/documents');

        $response->assertRedirect('/login');
    }

    /**
     * Test document create page can be rendered.
     */
    public function test_document_create_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/documents/create');

        $response->assertStatus(200);
    }

    /**
     * Test user without create permission cannot access create page.
     */
    public function test_user_without_create_permission_cannot_access_create_page(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/documents/create');

        $response->assertStatus(403);
    }

    /**
     * Test admin can create document.
     */
    public function test_admin_can_create_document(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->adminUser)->post('/documents', [
            'document_number' => 'DOC-2025-001',
            'title' => 'Test Document',
            'description' => 'This is a test document',
            'document_type_id' => $this->documentType->id,
            'effective_date' => now()->format('Y-m-d'),
            'confidentiality' => 'internal',
            'file' => $file,
        ]);

        $this->assertDatabaseHas('documents', [
            'document_number' => 'DOC-2025-001',
            'title' => 'Test Document',
        ]);

        $response->assertRedirect();
    }

    /**
     * Test document creation validation.
     */
    public function test_document_creation_requires_title(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/documents', [
            'document_number' => 'DOC-2025-002',
            'document_type_id' => $this->documentType->id,
        ]);

        $response->assertSessionHasErrors('title');
    }

    /**
     * Test document creation requires document number.
     */
    public function test_document_creation_requires_document_number(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/documents', [
            'title' => 'Test Document',
            'document_type_id' => $this->documentType->id,
        ]);

        $response->assertSessionHasErrors('document_number');
    }

    /**
     * Test document show page can be accessed.
     */
    public function test_document_show_page_can_be_accessed(): void
    {
        $document = Document::factory()->create([
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)->get("/documents/{$document->id}");

        $response->assertStatus(200);
        $response->assertSee($document->title);
    }

    /**
     * Test document edit page can be accessed.
     */
    public function test_document_edit_page_can_be_accessed(): void
    {
        $document = Document::factory()->create([
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)->get("/documents/{$document->id}/edit");

        $response->assertStatus(200);
    }

    /**
     * Test user without edit permission cannot access edit page.
     */
    public function test_user_without_edit_permission_cannot_access_edit_page(): void
    {
        $document = Document::factory()->create([
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->regularUser)->get("/documents/{$document->id}/edit");

        $response->assertStatus(403);
    }

    /**
     * Test admin can update document.
     */
    public function test_admin_can_update_document(): void
    {
        $document = Document::factory()->create([
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)->put("/documents/{$document->id}", [
            'document_number' => $document->document_number,
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'document_type_id' => $this->documentType->id,
            'effective_date' => now()->format('Y-m-d'),
            'confidentiality' => 'internal',
        ]);

        $document->refresh();

        $this->assertEquals('Updated Title', $document->title);
        $response->assertRedirect();
    }

    /**
     * Test admin can delete document.
     */
    public function test_admin_can_delete_document(): void
    {
        $document = Document::factory()->create([
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)->delete("/documents/{$document->id}");

        $this->assertSoftDeleted('documents', ['id' => $document->id]);
        $response->assertRedirect();
    }

    /**
     * Test user without delete permission cannot delete document.
     */
    public function test_user_without_delete_permission_cannot_delete_document(): void
    {
        $document = Document::factory()->create([
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->regularUser)->delete("/documents/{$document->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('documents', ['id' => $document->id, 'deleted_at' => null]);
    }

    /**
     * Test documents can be searched.
     */
    public function test_documents_can_be_searched(): void
    {
        Document::factory()->create([
            'title' => 'Perjanjian Kerjasama',
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->adminUser->id,
        ]);

        Document::factory()->create([
            'title' => 'Memorandum of Understanding',
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)->get('/documents?search=Perjanjian');

        $response->assertStatus(200);
        $response->assertSee('Perjanjian Kerjasama');
        $response->assertDontSee('Memorandum of Understanding');
    }

    /**
     * Test documents can be filtered by type.
     */
    public function test_documents_can_be_filtered_by_type(): void
    {
        $anotherType = DocumentType::factory()->create();

        Document::factory()->create([
            'title' => 'Document Type A',
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->adminUser->id,
        ]);

        Document::factory()->create([
            'title' => 'Document Type B',
            'document_type_id' => $anotherType->id,
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)->get("/documents?type_id={$this->documentType->id}");

        $response->assertStatus(200);
        $response->assertSee('Document Type A');
    }

    /**
     * Test documents can be filtered by status.
     */
    public function test_documents_can_be_filtered_by_status(): void
    {
        Document::factory()->draft()->create([
            'title' => 'Draft Document',
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->adminUser->id,
        ]);

        Document::factory()->published()->create([
            'title' => 'Published Document',
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)->get('/documents?status=draft');

        $response->assertStatus(200);
        $response->assertSee('Draft Document');
    }

    /**
     * Test expired documents filter.
     */
    public function test_expired_documents_can_be_filtered(): void
    {
        Document::factory()->expired()->create([
            'title' => 'Expired Document',
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->adminUser->id,
        ]);

        Document::factory()->create([
            'title' => 'Active Document',
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->adminUser->id,
            'expiry_date' => now()->addYear(),
        ]);

        $response = $this->actingAs($this->adminUser)->get('/documents?expired=1');

        $response->assertStatus(200);
        $response->assertSee('Expired Document');
    }

    /**
     * Test document number must be unique.
     */
    public function test_document_number_must_be_unique(): void
    {
        Document::factory()->create([
            'document_number' => 'DOC-2025-UNIQUE',
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->adminUser->id,
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->adminUser)->post('/documents', [
            'document_number' => 'DOC-2025-UNIQUE',
            'title' => 'Another Document',
            'document_type_id' => $this->documentType->id,
            'effective_date' => now()->format('Y-m-d'),
            'confidentiality' => 'internal',
            'file' => $file,
        ]);

        $response->assertSessionHasErrors('document_number');
    }

    /**
     * Test pagination works on documents index.
     */
    public function test_documents_index_is_paginated(): void
    {
        Document::factory()->count(25)->create([
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)->get('/documents');

        $response->assertStatus(200);
    }
}
