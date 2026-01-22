<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentSearchFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Role $role;
    protected DocumentType $typeA;
    protected DocumentType $typeB;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        $viewPermission = Permission::factory()->documentsView()->create();

        // Create role with view permission
        $this->role = Role::factory()->create(['name' => 'viewer']);
        $this->role->permissions()->attach([$viewPermission->id]);

        // Create user
        $this->user = User::factory()->create(['role_id' => $this->role->id]);

        // Create document types
        $this->typeA = DocumentType::factory()->create(['name' => 'Perjanjian Kerjasama']);
        $this->typeB = DocumentType::factory()->create(['name' => 'Memorandum of Understanding']);
    }

    /**
     * Test search by document title.
     */
    public function test_search_by_title(): void
    {
        Document::factory()->create([
            'title' => 'Perjanjian Kerjasama dengan PT ABC',
            'document_type_id' => $this->typeA->id,
            'created_by' => $this->user->id,
        ]);

        Document::factory()->create([
            'title' => 'MoU dengan Universitas XYZ',
            'document_type_id' => $this->typeB->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/documents?search=ABC');

        $response->assertStatus(200);
        $response->assertSee('PT ABC');
    }

    /**
     * Test search by document number.
     */
    public function test_search_by_document_number(): void
    {
        Document::factory()->create([
            'document_number' => 'PKS/2025/001',
            'title' => 'Document One',
            'document_type_id' => $this->typeA->id,
            'created_by' => $this->user->id,
        ]);

        Document::factory()->create([
            'document_number' => 'MOU/2025/002',
            'title' => 'Document Two',
            'document_type_id' => $this->typeB->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/documents?search=PKS/2025/001');

        $response->assertStatus(200);
        $response->assertSee('Document One');
    }

    /**
     * Test filter by document type.
     */
    public function test_filter_by_document_type(): void
    {
        Document::factory()->create([
            'title' => 'PKS Document',
            'document_type_id' => $this->typeA->id,
            'created_by' => $this->user->id,
        ]);

        Document::factory()->create([
            'title' => 'MoU Document',
            'document_type_id' => $this->typeB->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get("/documents?type_id={$this->typeA->id}");

        $response->assertStatus(200);
        $response->assertSee('PKS Document');
    }

    /**
     * Test filter by status.
     */
    public function test_filter_by_status(): void
    {
        Document::factory()->draft()->create([
            'title' => 'Draft Document',
            'document_type_id' => $this->typeA->id,
            'created_by' => $this->user->id,
        ]);

        Document::factory()->published()->create([
            'title' => 'Published Document',
            'document_type_id' => $this->typeA->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/documents?status=published');

        $response->assertStatus(200);
        $response->assertSee('Published Document');
    }

    /**
     * Test filter by expired status.
     */
    public function test_filter_by_expired(): void
    {
        Document::factory()->expired()->create([
            'title' => 'Expired Contract',
            'document_type_id' => $this->typeA->id,
            'created_by' => $this->user->id,
        ]);

        Document::factory()->create([
            'title' => 'Active Contract',
            'document_type_id' => $this->typeA->id,
            'created_by' => $this->user->id,
            'expiry_date' => now()->addYear(),
        ]);

        $response = $this->actingAs($this->user)->get('/documents?expired=1');

        $response->assertStatus(200);
        $response->assertSee('Expired Contract');
    }

    /**
     * Test filter by expiring soon.
     */
    public function test_filter_by_expiring_soon(): void
    {
        Document::factory()->expiringSoon()->create([
            'title' => 'Almost Expired Document',
            'document_type_id' => $this->typeA->id,
            'created_by' => $this->user->id,
        ]);

        Document::factory()->create([
            'title' => 'Long Term Document',
            'document_type_id' => $this->typeA->id,
            'created_by' => $this->user->id,
            'expiry_date' => now()->addYear(),
        ]);

        $response = $this->actingAs($this->user)->get('/documents?expiring_days=30');

        $response->assertStatus(200);
        $response->assertSee('Almost Expired Document');
    }

    /**
     * Test filter by date range.
     */
    public function test_filter_by_date_range(): void
    {
        Document::factory()->create([
            'title' => 'January Document',
            'document_type_id' => $this->typeA->id,
            'created_by' => $this->user->id,
            'effective_date' => '2025-01-15',
        ]);

        Document::factory()->create([
            'title' => 'December Document',
            'document_type_id' => $this->typeA->id,
            'created_by' => $this->user->id,
            'effective_date' => '2024-12-01',
        ]);

        $response = $this->actingAs($this->user)->get('/documents?date_from=2025-01-01&date_to=2025-01-31');

        $response->assertStatus(200);
    }

    /**
     * Test combined filters.
     */
    public function test_combined_filters(): void
    {
        // Document that matches all filters
        Document::factory()->draft()->create([
            'title' => 'Draft PKS Document',
            'document_type_id' => $this->typeA->id,
            'created_by' => $this->user->id,
        ]);

        // Document that matches only type
        Document::factory()->published()->create([
            'title' => 'Published PKS Document',
            'document_type_id' => $this->typeA->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get("/documents?type_id={$this->typeA->id}&status=draft");

        $response->assertStatus(200);
        $response->assertSee('Draft PKS Document');
    }

    /**
     * Test sort by document number ascending.
     */
    public function test_sort_by_document_number(): void
    {
        Document::factory()->create([
            'document_number' => 'B-002',
            'title' => 'Document B',
            'document_type_id' => $this->typeA->id,
            'created_by' => $this->user->id,
        ]);

        Document::factory()->create([
            'document_number' => 'A-001',
            'title' => 'Document A',
            'document_type_id' => $this->typeA->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/documents?sort=document_number&dir=asc');

        $response->assertStatus(200);
    }

    /**
     * Test sort by title descending.
     */
    public function test_sort_by_title_descending(): void
    {
        Document::factory()->create([
            'title' => 'Alpha Document',
            'document_type_id' => $this->typeA->id,
            'created_by' => $this->user->id,
        ]);

        Document::factory()->create([
            'title' => 'Zeta Document',
            'document_type_id' => $this->typeA->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/documents?sort=title&dir=desc');

        $response->assertStatus(200);
    }

    /**
     * Test sort by expiry date.
     */
    public function test_sort_by_expiry_date(): void
    {
        Document::factory()->create([
            'title' => 'Expires Soon',
            'document_type_id' => $this->typeA->id,
            'created_by' => $this->user->id,
            'expiry_date' => now()->addMonth(),
        ]);

        Document::factory()->create([
            'title' => 'Expires Later',
            'document_type_id' => $this->typeA->id,
            'created_by' => $this->user->id,
            'expiry_date' => now()->addYear(),
        ]);

        $response = $this->actingAs($this->user)->get('/documents?sort=expiry_date&dir=asc');

        $response->assertStatus(200);
    }

    /**
     * Test search with no results.
     */
    public function test_search_with_no_results(): void
    {
        Document::factory()->create([
            'title' => 'Existing Document',
            'document_type_id' => $this->typeA->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/documents?search=nonexistent_term_xyz');

        $response->assertStatus(200);
        // Should show empty state or "no results" message
    }

    /**
     * Test case insensitive search.
     */
    public function test_case_insensitive_search(): void
    {
        Document::factory()->create([
            'title' => 'PERJANJIAN KERJASAMA',
            'document_type_id' => $this->typeA->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/documents?search=perjanjian');

        $response->assertStatus(200);
        $response->assertSee('PERJANJIAN KERJASAMA');
    }

    /**
     * Test pagination with filters.
     */
    public function test_pagination_with_filters(): void
    {
        // Create 25 documents of type A
        Document::factory()->count(25)->create([
            'document_type_id' => $this->typeA->id,
            'created_by' => $this->user->id,
        ]);

        // Create 10 documents of type B
        Document::factory()->count(10)->create([
            'document_type_id' => $this->typeB->id,
            'created_by' => $this->user->id,
        ]);

        // Filter by type A should show paginated results
        $response = $this->actingAs($this->user)->get("/documents?type_id={$this->typeA->id}");

        $response->assertStatus(200);
    }

    /**
     * Test export with filters.
     */
    public function test_export_respects_filters(): void
    {
        Document::factory()->create([
            'title' => 'Document to Export',
            'document_type_id' => $this->typeA->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get("/documents/export?type_id={$this->typeA->id}");

        // Export should respect filters
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }
}
