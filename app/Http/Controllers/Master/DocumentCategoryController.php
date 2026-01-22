<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DocumentCategory;
use App\Models\DocumentType;
use Illuminate\Http\Request;

class DocumentCategoryController extends Controller
{
    /**
     * Display a listing of document categories
     */
    public function index(Request $request)
    {
        $query = DocumentCategory::with('documentType')->withCount('documents');
        
        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }
        
        // Filter by document type
        if ($request->filled('document_type_id')) {
            $query->where('document_type_id', $request->document_type_id);
        }
        
        // Filter by status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $categories = $query->sorted()->paginate(20);
        $documentTypes = DocumentType::active()->sorted()->get();

        return view('master.document-categories.index', compact('categories', 'documentTypes'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        $documentTypes = DocumentType::active()->sorted()->get();
        
        return view('master.document-categories.create', compact('documentTypes'));
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_type_id' => ['required', 'exists:document_types,id'],
            'code' => ['required', 'string', 'max:20', 'unique:document_categories'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ], [
            'document_type_id.required' => 'Jenis dokumen wajib dipilih.',
            'code.required' => 'Kode kategori wajib diisi.',
            'code.unique' => 'Kode kategori sudah digunakan.',
            'name.required' => 'Nama kategori wajib diisi.',
        ]);

        $validated['sort_order'] = DocumentCategory::where('document_type_id', $validated['document_type_id'])->max('sort_order') + 1;

        $category = DocumentCategory::create($validated);

        AuditLog::log(
            'created',
            AuditLog::MODULE_MASTER_DATA,
            'DocumentCategory',
            $category->id,
            $category->name,
            null,
            $validated,
            'Kategori dokumen baru ditambahkan.'
        );

        return redirect()->route('master.document-categories.index')
            ->with('success', "Kategori {$category->name} berhasil ditambahkan.");
    }

    /**
     * Display the specified category
     */
    public function show(DocumentCategory $documentCategory)
    {
        $documentCategory->load('documentType');

        return view('master.document-categories.show', compact('documentCategory'));
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit(DocumentCategory $documentCategory)
    {
        $documentTypes = DocumentType::active()->sorted()->get();
        
        return view('master.document-categories.edit', compact('documentCategory', 'documentTypes'));
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, DocumentCategory $documentCategory)
    {
        $validated = $request->validate([
            'document_type_id' => ['required', 'exists:document_types,id'],
            'code' => ['required', 'string', 'max:20', "unique:document_categories,code,{$documentCategory->id}"],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ], [
            'document_type_id.required' => 'Jenis dokumen wajib dipilih.',
            'code.required' => 'Kode kategori wajib diisi.',
            'code.unique' => 'Kode kategori sudah digunakan.',
            'name.required' => 'Nama kategori wajib diisi.',
        ]);

        $oldValues = $documentCategory->only(['document_type_id', 'code', 'name', 'description', 'is_active']);

        $documentCategory->update($validated);

        AuditLog::log(
            'updated',
            AuditLog::MODULE_MASTER_DATA,
            'DocumentCategory',
            $documentCategory->id,
            $documentCategory->name,
            $oldValues,
            $validated,
            'Kategori dokumen diperbarui.'
        );

        return redirect()->route('master.document-categories.index')
            ->with('success', "Kategori {$documentCategory->name} berhasil diperbarui.");
    }

    /**
     * Remove the specified category
     */
    public function destroy(DocumentCategory $documentCategory)
    {
        // Check if category has documents
        if ($documentCategory->documents()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih memiliki dokumen.');
        }

        $name = $documentCategory->name;
        
        AuditLog::log(
            'deleted',
            AuditLog::MODULE_MASTER_DATA,
            'DocumentCategory',
            $documentCategory->id,
            $name,
            $documentCategory->toArray(),
            null,
            'Kategori dokumen dihapus.'
        );

        $documentCategory->delete();

        return redirect()->route('master.document-categories.index')
            ->with('success', "Kategori {$name} berhasil dihapus.");
    }

    /**
     * Get categories by document type (AJAX)
     */
    public function byType(DocumentType $documentType)
    {
        $categories = $documentType->activeCategories;
        
        return response()->json($categories);
    }
}
