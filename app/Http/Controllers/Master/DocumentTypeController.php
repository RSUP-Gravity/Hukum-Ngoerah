<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DocumentType;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:document_types'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'prefix' => ['required', 'string', 'max:10'],
            'requires_approval' => ['boolean'],
            'has_expiry' => ['boolean'],
            'default_retention_days' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ], [
            'code.required' => 'Kode jenis dokumen wajib diisi.',
            'code.unique' => 'Kode jenis dokumen sudah digunakan.',
            'name.required' => 'Nama jenis dokumen wajib diisi.',
            'prefix.required' => 'Prefix nomor dokumen wajib diisi.',
        ]);

        $validated['sort_order'] = DocumentType::max('sort_order') + 1;

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
    public function update(Request $request, DocumentType $documentType)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', "unique:document_types,code,{$documentType->id}"],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'prefix' => ['required', 'string', 'max:10'],
            'requires_approval' => ['boolean'],
            'has_expiry' => ['boolean'],
            'default_retention_days' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ], [
            'code.required' => 'Kode jenis dokumen wajib diisi.',
            'code.unique' => 'Kode jenis dokumen sudah digunakan.',
            'name.required' => 'Nama jenis dokumen wajib diisi.',
            'prefix.required' => 'Prefix nomor dokumen wajib diisi.',
        ]);

        $oldValues = $documentType->only([
            'code', 'name', 'description', 'prefix', 
            'requires_approval', 'has_expiry', 'default_retention_days', 'is_active'
        ]);

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
