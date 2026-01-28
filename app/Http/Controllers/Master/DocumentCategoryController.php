<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\DocumentCategoryRequest;
use App\Models\AuditLog;
use App\Models\DocumentCategory;
use App\Models\DocumentType;
use Illuminate\Http\Request;

class DocumentCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:master.create')->only(['create', 'store']);
        $this->middleware('permission:master.edit')->only(['edit', 'update']);
        $this->middleware('permission:master.delete')->only(['destroy']);
    }

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
    public function store(DocumentCategoryRequest $request)
    {
        $validated = $request->validated();
        if (!array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
            $validated['sort_order'] = (int) DocumentCategory::where('document_type_id', $validated['document_type_id'])
                ->max('sort_order') + 1;
        }

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
    public function update(DocumentCategoryRequest $request, DocumentCategory $documentCategory)
    {
        $validated = $request->validated();
        $oldValues = $documentCategory->only(['document_type_id', 'code', 'name', 'description', 'is_active', 'sort_order']);
        $typeChanged = $documentCategory->document_type_id !== (int) $validated['document_type_id'];

        if (!array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
            if ($typeChanged) {
                $validated['sort_order'] = (int) DocumentCategory::where('document_type_id', $validated['document_type_id'])
                    ->max('sort_order') + 1;
            } else {
                $validated['sort_order'] = $documentCategory->sort_order;
            }
        }

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
