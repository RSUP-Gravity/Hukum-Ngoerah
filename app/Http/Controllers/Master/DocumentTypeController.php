<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\DocumentTypeRequest;
use App\Models\AuditLog;
use App\Models\DocumentType;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:master.create')->only(['create', 'store']);
        $this->middleware('permission:master.edit')->only(['edit', 'update']);
        $this->middleware('permission:master.delete')->only(['destroy']);
    }

    /**
     * Display a listing of document types
     */
    public function index(Request $request)
    {
        $query = DocumentType::withCount(['documents', 'categories']);
        
        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }
        
        // Filter by status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $documentTypes = $query->sorted()->paginate(20);

        return view('master.document-types.index', compact('documentTypes'));
    }

    /**
     * Show the form for creating a new document type
     */
    public function create()
    {
        return view('master.document-types.create');
    }

    /**
     * Store a newly created document type
     */
    public function store(DocumentTypeRequest $request)
    {
        $validated = $request->validated();
        if (!array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
            $validated['sort_order'] = (int) DocumentType::max('sort_order') + 1;
        }

        $documentType = DocumentType::create($validated);

        AuditLog::log(
            'created',
            AuditLog::MODULE_MASTER_DATA,
            'DocumentType',
            $documentType->id,
            $documentType->name,
            null,
            $validated,
            'Jenis dokumen baru ditambahkan.'
        );

        return redirect()->route('master.document-types.index')
            ->with('success', "Jenis dokumen {$documentType->name} berhasil ditambahkan.");
    }

    /**
     * Display the specified document type
     */
    public function show(DocumentType $documentType)
    {
        $documentType->load(['categories' => function ($q) {
            $q->withCount('documents')->sorted();
        }]);

        return view('master.document-types.show', compact('documentType'));
    }

    /**
     * Show the form for editing the specified document type
     */
    public function edit(DocumentType $documentType)
    {
        return view('master.document-types.edit', compact('documentType'));
    }

    /**
     * Update the specified document type
     */
    public function update(DocumentTypeRequest $request, DocumentType $documentType)
    {
        $validated = $request->validated();
        $oldValues = $documentType->only([
            'code', 'name', 'description', 'prefix',
            'requires_approval', 'has_expiry', 'default_retention_days', 'is_active', 'sort_order'
        ]);

        if (!array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
            $validated['sort_order'] = $documentType->sort_order;
        }

        $documentType->update($validated);

        AuditLog::log(
            'updated',
            AuditLog::MODULE_MASTER_DATA,
            'DocumentType',
            $documentType->id,
            $documentType->name,
            $oldValues,
            $validated,
            'Jenis dokumen diperbarui.'
        );

        return redirect()->route('master.document-types.index')
            ->with('success', "Jenis dokumen {$documentType->name} berhasil diperbarui.");
    }

    /**
     * Remove the specified document type
     */
    public function destroy(DocumentType $documentType)
    {
        if ($documentType->categories()->exists()) {
            return back()->with('error', 'Jenis dokumen tidak dapat dihapus karena masih memiliki kategori.');
        }

        // Check if document type has documents
        if ($documentType->documents()->exists()) {
            return back()->with('error', 'Jenis dokumen tidak dapat dihapus karena masih memiliki dokumen.');
        }

        $name = $documentType->name;
        
        AuditLog::log(
            'deleted',
            AuditLog::MODULE_MASTER_DATA,
            'DocumentType',
            $documentType->id,
            $name,
            $documentType->toArray(),
            null,
            'Jenis dokumen dihapus.'
        );

        $documentType->delete();

        return redirect()->route('master.document-types.index')
            ->with('success', "Jenis dokumen {$name} berhasil dihapus.");
    }
}
