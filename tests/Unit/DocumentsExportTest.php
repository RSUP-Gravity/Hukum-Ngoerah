<?php

namespace Tests\Unit;

use App\Exports\DocumentsExport;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\DocumentCategory;
use App\Models\Directorate;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class DocumentsExportTest extends TestCase
{
    use RefreshDatabase;

    protected DocumentType $documentType;
    protected Directorate $directorate;
    protected Unit $unit;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create dependencies
        $this->user = User::factory()->create();
        
        $this->documentType = DocumentType::factory()->create([
            'name' => 'Peraturan',
            'code' => 'PERDIR',
            'prefix' => 'PD',
        ]);
        
        $this->directorate = Directorate::factory()->create([
            'name' => 'Direktorat Umum',
            'code' => 'DU',
        ]);
        
        $this->unit = Unit::factory()->create([
            'directorate_id' => $this->directorate->id,
            'name' => 'Unit Hukum',
            'code' => 'UH',
        ]);
    }

    /**
     * Test export can be instantiated with empty filters
     */
    public function test_export_can_be_instantiated_with_empty_filters(): void
    {
        $export = new DocumentsExport([]);
        
        $this->assertInstanceOf(DocumentsExport::class, $export);
    }

    /**
     * Test export can be instantiated with filters
     */
    public function test_export_can_be_instantiated_with_filters(): void
    {
        $export = new DocumentsExport([
            'search' => 'test',
            'document_type_id' => 1,
            'status' => 'active',
        ]);
        
        $this->assertInstanceOf(DocumentsExport::class, $export);
    }

    /**
     * Test headings returns correct column headers
     */
    public function test_headings_returns_correct_columns(): void
    {
        $export = new DocumentsExport([]);
        $headings = $export->headings();
        
        $this->assertIsArray($headings);
        $this->assertContains('No', $headings);
        $this->assertContains('Nomor Dokumen', $headings);
        $this->assertContains('Judul Dokumen', $headings);
        $this->assertContains('Jenis Dokumen', $headings);
        $this->assertContains('Status', $headings);
        $this->assertContains('Tanggal Berakhir', $headings);
    }

    /**
     * Test query returns Builder instance
     */
    public function test_query_returns_builder(): void
    {
        $export = new DocumentsExport([]);
        $query = $export->query();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $query);
    }

    /**
     * Test map transforms document correctly
     */
    public function test_map_transforms_document_correctly(): void
    {
        $document = Document::factory()->create([
            'document_number' => 'DOC-001',
            'title' => 'Test Document',
            'document_type_id' => $this->documentType->id,
            'directorate_id' => $this->directorate->id,
            'unit_id' => $this->unit->id,
            'created_by' => $this->user->id,
            'expiry_date' => now()->addDays(60),
        ]);
        
        $export = new DocumentsExport([]);
        $mapped = $export->map($document);
        
        $this->assertIsArray($mapped);
        $this->assertEquals(1, $mapped[0]); // Row number
        $this->assertEquals('DOC-001', $mapped[1]); // Document number
        $this->assertEquals('Test Document', $mapped[2]); // Title
        $this->assertEquals('Peraturan', $mapped[3]); // Document type name
    }

    /**
     * Test filter by search term
     */
    public function test_query_filters_by_search_term(): void
    {
        Document::factory()->create([
            'title' => 'Peraturan Direktur',
            'document_number' => 'PD-001',
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->user->id,
        ]);
        
        Document::factory()->create([
            'title' => 'Surat Keputusan',
            'document_number' => 'SK-001',
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->user->id,
        ]);
        
        $export = new DocumentsExport(['search' => 'Peraturan']);
        $results = $export->query()->get();
        
        $this->assertCount(1, $results);
        $this->assertEquals('Peraturan Direktur', $results->first()->title);
    }

    /**
     * Test filter by document type
     */
    public function test_query_filters_by_document_type(): void
    {
        $anotherType = DocumentType::factory()->create(['name' => 'SOP']);
        
        Document::factory()->create([
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->user->id,
        ]);
        
        Document::factory()->create([
            'document_type_id' => $anotherType->id,
            'created_by' => $this->user->id,
        ]);
        
        $export = new DocumentsExport(['document_type_id' => $this->documentType->id]);
        $results = $export->query()->get();
        
        $this->assertCount(1, $results);
        $this->assertEquals($this->documentType->id, $results->first()->document_type_id);
    }

    /**
     * Test filter by directorate
     */
    public function test_query_filters_by_directorate(): void
    {
        $anotherDirectorate = Directorate::factory()->create(['name' => 'Direktorat Lain']);
        
        Document::factory()->create([
            'directorate_id' => $this->directorate->id,
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->user->id,
        ]);
        
        Document::factory()->create([
            'directorate_id' => $anotherDirectorate->id,
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->user->id,
        ]);
        
        $export = new DocumentsExport(['directorate_id' => $this->directorate->id]);
        $results = $export->query()->get();
        
        $this->assertCount(1, $results);
    }

    /**
     * Test filter by unit
     */
    public function test_query_filters_by_unit(): void
    {
        $anotherUnit = Unit::factory()->create([
            'directorate_id' => $this->directorate->id,
            'name' => 'Unit Lain',
        ]);
        
        Document::factory()->create([
            'unit_id' => $this->unit->id,
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->user->id,
        ]);
        
        Document::factory()->create([
            'unit_id' => $anotherUnit->id,
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->user->id,
        ]);
        
        $export = new DocumentsExport(['unit_id' => $this->unit->id]);
        $results = $export->query()->get();
        
        $this->assertCount(1, $results);
    }

    /**
     * Test filter by expired status
     */
    public function test_query_filters_by_expired_status(): void
    {
        Document::factory()->create([
            'expiry_date' => now()->subDays(10), // Expired
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->user->id,
        ]);
        
        Document::factory()->create([
            'expiry_date' => now()->addDays(60), // Active
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->user->id,
        ]);
        
        $export = new DocumentsExport(['status' => 'expired']);
        $results = $export->query()->get();
        
        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->expiry_date->isPast());
    }

    /**
     * Test filter by critical status (expiring within 30 days)
     */
    public function test_query_filters_by_critical_status(): void
    {
        Document::factory()->create([
            'expiry_date' => now()->addDays(15), // Critical
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->user->id,
        ]);
        
        Document::factory()->create([
            'expiry_date' => now()->addDays(60), // Not critical
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->user->id,
        ]);
        
        $export = new DocumentsExport(['status' => 'critical']);
        $results = $export->query()->get();
        
        $this->assertCount(1, $results);
    }

    /**
     * Test filter by perpetual status (no expiry date)
     */
    public function test_query_filters_by_perpetual_status(): void
    {
        Document::factory()->create([
            'expiry_date' => null, // Perpetual
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->user->id,
        ]);
        
        Document::factory()->create([
            'expiry_date' => now()->addDays(60), // Has expiry
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->user->id,
        ]);
        
        $export = new DocumentsExport(['status' => 'perpetual']);
        $results = $export->query()->get();
        
        $this->assertCount(1, $results);
        $this->assertNull($results->first()->expiry_date);
    }

    /**
     * Test filter by date range
     */
    public function test_query_filters_by_expiry_date_range(): void
    {
        Document::factory()->create([
            'expiry_date' => now()->addDays(30),
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->user->id,
        ]);
        
        Document::factory()->create([
            'expiry_date' => now()->addDays(90),
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->user->id,
        ]);
        
        $export = new DocumentsExport([
            'expiry_date_from' => now()->addDays(20)->toDateString(),
            'expiry_date_to' => now()->addDays(40)->toDateString(),
        ]);
        $results = $export->query()->get();
        
        $this->assertCount(1, $results);
    }

    /**
     * Test title returns correct sheet name
     */
    public function test_title_returns_sheet_name(): void
    {
        $export = new DocumentsExport([]);
        $title = $export->title();
        
        $this->assertIsString($title);
        $this->assertNotEmpty($title);
    }

    /**
     * Test column widths are defined
     */
    public function test_column_widths_are_defined(): void
    {
        $export = new DocumentsExport([]);
        $widths = $export->columnWidths();
        
        $this->assertIsArray($widths);
        $this->assertArrayHasKey('A', $widths); // No column
        $this->assertArrayHasKey('B', $widths); // Document number column
        $this->assertArrayHasKey('C', $widths); // Title column
    }

    /**
     * Test styles method returns worksheet styles
     */
    public function test_styles_returns_worksheet_config(): void
    {
        $export = new DocumentsExport([]);
        
        // Create a mock worksheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        
        $styles = $export->styles($worksheet);
        
        $this->assertIsArray($styles);
        $this->assertArrayHasKey(1, $styles); // Header row styles
    }

    /**
     * Test export with combined filters
     */
    public function test_query_with_combined_filters(): void
    {
        Document::factory()->create([
            'title' => 'Matching Document',
            'document_type_id' => $this->documentType->id,
            'directorate_id' => $this->directorate->id,
            'expiry_date' => now()->addDays(60),
            'created_by' => $this->user->id,
        ]);
        
        Document::factory()->create([
            'title' => 'Non-matching Type',
            'document_type_id' => DocumentType::factory()->create()->id,
            'directorate_id' => $this->directorate->id,
            'expiry_date' => now()->addDays(60),
            'created_by' => $this->user->id,
        ]);
        
        $export = new DocumentsExport([
            'search' => 'Matching',
            'document_type_id' => $this->documentType->id,
            'directorate_id' => $this->directorate->id,
        ]);
        $results = $export->query()->get();
        
        $this->assertCount(1, $results);
        $this->assertEquals('Matching Document', $results->first()->title);
    }

    /**
     * Test sorting by expiry date ascending
     */
    public function test_query_sorts_by_expiry_date_asc(): void
    {
        Document::factory()->create([
            'expiry_date' => now()->addDays(60),
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->user->id,
        ]);
        
        Document::factory()->create([
            'expiry_date' => now()->addDays(30),
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->user->id,
        ]);
        
        $export = new DocumentsExport([
            'sort_by' => 'expiry_date',
            'sort_dir' => 'asc',
        ]);
        $results = $export->query()->get();
        
        $this->assertCount(2, $results);
        $this->assertTrue(
            $results->first()->expiry_date->lt($results->last()->expiry_date)
        );
    }

    /**
     * Test sorting by expiry date descending
     */
    public function test_query_sorts_by_expiry_date_desc(): void
    {
        Document::factory()->create([
            'expiry_date' => now()->addDays(30),
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->user->id,
        ]);
        
        Document::factory()->create([
            'expiry_date' => now()->addDays(60),
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->user->id,
        ]);
        
        $export = new DocumentsExport([
            'sort_by' => 'expiry_date',
            'sort_dir' => 'desc',
        ]);
        $results = $export->query()->get();
        
        $this->assertCount(2, $results);
        $this->assertTrue(
            $results->first()->expiry_date->gt($results->last()->expiry_date)
        );
    }

    /**
     * Test row number increments correctly
     */
    public function test_map_increments_row_number(): void
    {
        $doc1 = Document::factory()->create([
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->user->id,
        ]);
        
        $doc2 = Document::factory()->create([
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->user->id,
        ]);
        
        $export = new DocumentsExport([]);
        
        $mapped1 = $export->map($doc1);
        $mapped2 = $export->map($doc2);
        
        $this->assertEquals(1, $mapped1[0]);
        $this->assertEquals(2, $mapped2[0]);
    }

    /**
     * Test export can be generated as Excel
     */
    public function test_export_can_generate_excel(): void
    {
        Document::factory()->count(3)->create([
            'document_type_id' => $this->documentType->id,
            'created_by' => $this->user->id,
        ]);
        
        Excel::fake();
        
        Excel::download(new DocumentsExport([]), 'documents.xlsx');
        
        Excel::assertDownloaded('documents.xlsx');
    }
}
