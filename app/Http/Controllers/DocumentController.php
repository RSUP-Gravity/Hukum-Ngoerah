<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Document;
use App\Models\DocumentAccess;
use App\Models\DocumentApproval;
use App\Models\DocumentCategory;
use App\Models\DocumentHistory;
use App\Models\DocumentType;
use App\Models\DocumentVersion;
use App\Models\Notification;
use App\Models\Unit;
use App\Models\User;
use App\Services\FileUploadSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Document::with(['type', 'category', 'unit', 'creator']);
        
        // Apply access control unless user is admin
        if (!$user->isAdmin()) {
            $query->accessibleBy($user);
        }
        
        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        
        // Filter by type
        if ($request->filled('type_id')) {
            $query->where('document_type_id', $request->type_id);
        }
        
        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('document_category_id', $request->category_id);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }
        
        // Filter by unit
        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('document_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('document_date', '<=', $request->date_to);
        }
        
        // Filter expired
        if ($request->boolean('expired')) {
            $query->expired();
        }
        
        // Filter expiring soon
        if ($request->filled('expiring_days')) {
            $query->expiringSoon($request->expiring_days);
        }
        
        // Multi-column sort support
        // Format: sort=column1,column2 dir=asc,desc
        $sortFields = explode(',', $request->input('sort', 'updated_at'));
        $sortDirs = explode(',', $request->input('dir', 'desc'));
        
        foreach ($sortFields as $index => $field) {
            $field = trim($field);
            $dir = isset($sortDirs[$index]) ? trim($sortDirs[$index]) : 'asc';
            // Validate sort direction
            $dir = in_array(strtolower($dir), ['asc', 'desc']) ? strtolower($dir) : 'asc';
            // Validate field name (only allow known fields)
            $allowedFields = ['document_number', 'title', 'document_date', 'updated_at', 'created_at', 'expiry_date', 'status'];
            if (in_array($field, $allowedFields)) {
                $query->orderBy($field, $dir);
            }
        }

        $documents = $query->paginate(20);
        
        $types = DocumentType::active()->sorted()->get();
        $categories = DocumentCategory::active()->sorted()->get();
        $units = Unit::active()->sorted()->get();
        $statuses = Document::STATUSES;

        return view('documents.index', compact(
            'documents', 'types', 'categories', 'units', 'statuses'
        ));
    }

    /**
     * Show the form for creating a new document
     */
    public function create()
    {
        $types = DocumentType::active()->sorted()->get();
        $categories = DocumentCategory::active()->sorted()->get();
        $units = Unit::with('directorate')->active()->sorted()->get();
        $user = auth()->user();
        $isStaff = $user?->hasAnyRole(['legal_staff', 'unit_staff']);
        $approverCandidates = collect();

        if ($isStaff) {
            $approverCandidates = User::active()
                ->whereHas('role.permissions', function ($query) {
                    $query->where('name', 'documents.approve');
                })
                ->with(['role:id,name,display_name', 'position:id,name', 'unit:id,name'])
                ->orderBy('name')
                ->get(['id', 'name', 'role_id', 'position_id', 'unit_id'])
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'role' => $user->role?->display_name ?? $user->role?->name ?? '-',
                        'position' => $user->position?->name ?? '-',
                        'unit' => $user->unit?->name ?? '-',
                    ];
                })
                ->values();
        }
        
        return view('documents.create', compact('types', 'categories', 'units', 'approverCandidates', 'isStaff'));
    }

    /**
     * Store a newly created document
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $isStaff = $user->hasAnyRole(['legal_staff', 'unit_staff']);
        $submitForApproval = $isStaff && $request->boolean('submit_for_approval');

        $rules = [
            'title' => ['required', 'string', 'max:500'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'document_type_id' => ['required', 'exists:document_types,id'],
            'document_category_id' => ['nullable', 'exists:document_categories,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'document_date' => ['required', 'date'],
            'effective_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after:effective_date'],
            'parties' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'confidentiality' => ['required', Rule::in(array_keys(Document::CONFIDENTIALITIES))],
            'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:51200'], // 50MB max
        ];

        $messages = [
            'title.required' => 'Judul dokumen wajib diisi.',
            'document_type_id.required' => 'Jenis dokumen wajib dipilih.',
            'document_date.required' => 'Tanggal dokumen wajib diisi.',
            'confidentiality.required' => 'Tingkat kerahasiaan wajib dipilih.',
            'file.required' => 'File dokumen wajib diunggah.',
            'file.mimes' => 'File harus berformat PDF, DOC, atau DOCX.',
            'file.max' => 'Ukuran file maksimal 50MB.',
        ];

        if ($submitForApproval) {
            $rules['approvers'] = ['required', 'array', 'min:1'];
            $rules['approvers.*'] = ['required', 'integer', 'distinct', 'exists:users,id'];
            $messages['approvers.required'] = 'Pilih minimal 1 approver.';
            $messages['approvers.min'] = 'Pilih minimal 1 approver.';
            $messages['approvers.*.exists'] = 'Approver tidak valid.';
        }

        $validated = $request->validate($rules, $messages);

        $approverIds = [];
        if ($submitForApproval) {
            $approverIds = array_values($validated['approvers'] ?? []);

            $validApproverIds = User::active()
                ->whereIn('id', $approverIds)
                ->whereHas('role.permissions', function ($query) {
                    $query->where('name', 'documents.approve');
                })
                ->pluck('id')
                ->all();

            if (count($validApproverIds) !== count($approverIds)) {
                return back()->withInput()->with('error', 'Approver harus memiliki izin approve.');
            }
        }

        try {
            DB::beginTransaction();
            $documentStatus = $submitForApproval
                ? Document::STATUS_PENDING_APPROVAL
                : Document::STATUS_DRAFT;
            
            // Generate document number if not provided
            if (empty($validated['document_number'])) {
                $type = DocumentType::find($validated['document_type_id']);
                $validated['document_number'] = $this->generateDocumentNumber($type);
            }

            // Create document
            $document = Document::create([
                'title' => $validated['title'],
                'document_number' => $validated['document_number'],
                'document_type_id' => $validated['document_type_id'],
                'document_category_id' => $validated['document_category_id'],
                'unit_id' => $validated['unit_id'] ?? $user->unit_id,
                'document_date' => $validated['document_date'],
                'effective_date' => $validated['effective_date'],
                'expiry_date' => $validated['expiry_date'],
                'parties' => $validated['parties'],
                'description' => $validated['description'],
                'notes' => $validated['notes'],
                'confidentiality' => $validated['confidentiality'],
                'status' => $documentStatus,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            // Handle file upload with security validation
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                
                // Validate file security
                $securityService = new FileUploadSecurityService();
                $securityResult = $securityService->validate($file, ['pdf', 'doc', 'docx']);
                
                if (!$securityResult['valid']) {
                    throw new \Exception($securityResult['error']);
                }
                
                $this->uploadDocumentFile($document, $file, 1);
            }

            // Record history
            DocumentHistory::create([
                'document_id' => $document->id,
                'action' => DocumentHistory::ACTION_CREATED,
                'performed_by' => $user->id,
                'description' => 'Dokumen dibuat.',
            ]);

            if ($submitForApproval) {
                $sequence = 1;
                foreach ($approverIds as $approverId) {
                    DocumentApproval::create([
                        'document_id' => $document->id,
                        'approver_id' => $approverId,
                        'sequence' => $sequence++,
                        'status' => 'pending',
                    ]);
                }

                DocumentHistory::create([
                    'document_id' => $document->id,
                    'action' => DocumentHistory::ACTION_SUBMITTED_APPROVAL,
                    'performed_by' => $user->id,
                    'old_values' => ['status' => Document::STATUS_DRAFT],
                    'new_values' => ['status' => Document::STATUS_PENDING_APPROVAL],
                    'description' => 'Dokumen diajukan untuk approval.',
                ]);

                $firstApproval = $document->approvals()->orderBy('sequence')->first();
                if ($firstApproval) {
                    Notification::create([
                        'user_id' => $firstApproval->approver_id,
                        'title' => 'Dokumen Menunggu Approval',
                        'message' => "Dokumen '{$document->title}' membutuhkan approval Anda.",
                        'type' => Notification::TYPE_APPROVAL_REQUIRED,
                        'priority' => Notification::PRIORITY_HIGH,
                        'entity_type' => 'Document',
                        'entity_id' => $document->id,
                        'action_url' => route('documents.show', $document),
                    ]);
                }

                AuditLog::log(
                    'status_changed',
                    AuditLog::MODULE_DOCUMENTS,
                    'Document',
                    $document->id,
                    $document->title,
                    ['status' => Document::STATUS_DRAFT],
                    ['status' => Document::STATUS_PENDING_APPROVAL],
                    'Dokumen diajukan untuk approval.'
                );
            }

            // Audit log
            AuditLog::log(
                'created',
                AuditLog::MODULE_DOCUMENTS,
                'Document',
                $document->id,
                $document->title,
                null,
                $validated,
                'Dokumen baru dibuat.'
            );

            DB::commit();

            $successMessage = $submitForApproval
                ? 'Dokumen berhasil diajukan untuk approval.'
                : 'Dokumen berhasil ditambahkan.';

            return redirect()->route('documents.show', $document)
                ->with('success', $successMessage);
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified document
     */
    public function show(Document $document)
    {
        $user = auth()->user();
        
        // Check access
        if (!$document->hasAccess($user, DocumentAccess::PERM_VIEW)) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
        }

        $document->load([
            'type', 'category', 'unit.directorate', 'creator', 'updater',
            'versions' => function ($q) {
                $q->latest('version_number');
            },
            'history' => function ($q) {
                $q->with('performer')->latest();
            },
            'approvals' => function ($q) {
                $q->with('approver')->orderBy('sequence');
            },
            'accessPermissions' => function ($q) {
                $q->with('user');
            },
        ]);

        // Record view history
        if (!$document->history()->where('action', DocumentHistory::ACTION_VIEWED)->where('performed_by', $user->id)->whereDate('created_at', today())->exists()) {
            DocumentHistory::create([
                'document_id' => $document->id,
                'action' => DocumentHistory::ACTION_VIEWED,
                'performed_by' => $user->id,
                'description' => 'Dokumen dilihat.',
            ]);
        }

        // Get related documents (same type or same mitra/unit)
        $relatedDocuments = Document::where('id', '!=', $document->id)
            ->where(function ($query) use ($document) {
                $query->where('document_type_id', $document->document_type_id);
                if ($document->unit_id) {
                    $query->orWhere('unit_id', $document->unit_id);
                }
            })
            ->whereIn('status', [Document::STATUS_PUBLISHED, Document::STATUS_APPROVED])
            ->with(['type', 'unit'])
            ->latest('created_at')
            ->limit(5)
            ->get();

        $approverCandidates = User::active()
            ->whereHas('role.permissions', function ($query) {
                $query->where('name', 'documents.approve');
            })
            ->with(['role:id,name,display_name', 'position:id,name', 'unit:id,name'])
            ->orderBy('name')
            ->get(['id', 'name', 'role_id', 'position_id', 'unit_id'])
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role?->display_name ?? $user->role?->name ?? '-',
                    'position' => $user->position?->name ?? '-',
                    'unit' => $user->unit?->name ?? '-',
                ];
            })
            ->values();

        return view('documents.show', compact('document', 'relatedDocuments', 'approverCandidates'));
    }

    /**
     * Show the form for editing the specified document
     */
    public function edit(Document $document)
    {
        // Check if document is editable
        if (!$document->isEditable()) {
            return back()->with('error', 'Dokumen tidak dapat diedit karena status saat ini.');
        }

        $types = DocumentType::active()->sorted()->get();
        $categories = DocumentCategory::active()->sorted()->get();
        $units = Unit::with('directorate')->active()->sorted()->get();

        return view('documents.edit', compact('document', 'types', 'categories', 'units'));
    }

    /**
     * Update the specified document
     */
    public function update(Request $request, Document $document)
    {
        // Check if document is editable
        if (!$document->isEditable()) {
            return back()->with('error', 'Dokumen tidak dapat diedit karena status saat ini.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:500'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'document_type_id' => ['required', 'exists:document_types,id'],
            'document_category_id' => ['nullable', 'exists:document_categories,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'document_date' => ['required', 'date'],
            'effective_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after:effective_date'],
            'parties' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'confidentiality' => ['required', Rule::in(array_keys(Document::CONFIDENTIALITIES))],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:51200'],
        ], [
            'title.required' => 'Judul dokumen wajib diisi.',
            'document_type_id.required' => 'Jenis dokumen wajib dipilih.',
            'document_date.required' => 'Tanggal dokumen wajib diisi.',
            'confidentiality.required' => 'Tingkat kerahasiaan wajib dipilih.',
        ]);

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $oldValues = $document->only([
                'title', 'document_number', 'document_type_id', 'document_category_id',
                'unit_id', 'document_date', 'effective_date', 'expiry_date',
                'parties', 'description', 'notes', 'confidentiality'
            ]);

            unset($validated['file']);
            $validated['updated_by'] = $user->id;

            $document->update($validated);

            // Handle new file upload
            if ($request->hasFile('file')) {
                $newVersion = $document->current_version + 1;
                $this->uploadDocumentFile($document, $request->file('file'), $newVersion);
                $document->update(['current_version' => $newVersion]);
            }

            // Record history
            DocumentHistory::create([
                'document_id' => $document->id,
                'action' => DocumentHistory::ACTION_UPDATED,
                'performed_by' => $user->id,
                'old_values' => $oldValues,
                'new_values' => $validated,
                'description' => 'Dokumen diperbarui.',
            ]);

            // Audit log
            AuditLog::log(
                'updated',
                AuditLog::MODULE_DOCUMENTS,
                'Document',
                $document->id,
                $document->title,
                $oldValues,
                $validated,
                'Dokumen diperbarui.'
            );

            DB::commit();

            return redirect()->route('documents.show', $document)
                ->with('success', 'Dokumen berhasil diperbarui.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified document
     */
    public function destroy(Document $document)
    {
        // Only drafts can be deleted, others should be archived
        if ($document->status !== Document::STATUS_DRAFT) {
            return back()->with('error', 'Hanya dokumen draft yang dapat dihapus. Gunakan arsip untuk dokumen lainnya.');
        }

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $title = $document->title;

            // Delete all versions (files)
            foreach ($document->versions as $version) {
                if ($version->file_path && Storage::disk('public')->exists($version->file_path)) {
                    Storage::disk('public')->delete($version->file_path);
                }
            }

            // Audit log before delete
            AuditLog::log(
                'deleted',
                AuditLog::MODULE_DOCUMENTS,
                'Document',
                $document->id,
                $title,
                $document->toArray(),
                null,
                'Dokumen dihapus.'
            );

            // Delete document (cascade will handle related records)
            $document->delete();

            DB::commit();

            return redirect()->route('documents.index')
                ->with('success', "Dokumen '{$title}' berhasil dihapus.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Submit document for review
     */
    public function submitForReview(Document $document)
    {
        if (!$document->canSubmitForReview()) {
            return back()->with('error', 'Dokumen tidak dapat diajukan untuk review.');
        }

        $user = auth()->user();
        $oldStatus = $document->status;

        $document->update([
            'status' => Document::STATUS_PENDING_REVIEW,
            'updated_by' => $user->id,
        ]);

        // Record history
        DocumentHistory::create([
            'document_id' => $document->id,
            'action' => DocumentHistory::ACTION_SUBMITTED_REVIEW,
            'performed_by' => $user->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => Document::STATUS_PENDING_REVIEW],
            'description' => 'Dokumen diajukan untuk review.',
        ]);

        // Notify reviewers (legal staff or admin)
        $reviewers = User::whereHas('role', function ($q) {
            $q->whereIn('name', ['legal_staff', 'legal_head', 'admin', 'super_admin']);
        })->active()->get();

        foreach ($reviewers as $reviewer) {
            Notification::create([
                'user_id' => $reviewer->id,
                'title' => 'Dokumen Baru untuk Review',
                'message' => "Dokumen '{$document->title}' membutuhkan review Anda.",
                'type' => Notification::TYPE_DOCUMENT_SUBMITTED,
                'priority' => Notification::PRIORITY_NORMAL,
                'entity_type' => 'Document',
                'entity_id' => $document->id,
                'action_url' => route('documents.show', $document),
            ]);
        }

        // Audit log
        AuditLog::log(
            'status_changed',
            AuditLog::MODULE_DOCUMENTS,
            'Document',
            $document->id,
            $document->title,
            ['status' => $oldStatus],
            ['status' => Document::STATUS_PENDING_REVIEW],
            'Dokumen diajukan untuk review.'
        );

        return back()->with('success', 'Dokumen berhasil diajukan untuk review.');
    }

    /**
     * Submit document for approval (after review)
     */
    public function submitForApproval(Request $request, Document $document)
    {
        if ($document->status !== Document::STATUS_PENDING_REVIEW) {
            return back()->with('error', 'Dokumen harus dalam status review terlebih dahulu.');
        }

        $user = auth()->user();
        
        // Only legal staff can submit for approval
        if (!$user->hasAnyRole(['legal_head', 'legal_staff', 'admin', 'super_admin'])) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengajukan approval.');
        }

        $validated = $request->validate([
            'approvers' => ['required', 'array', 'min:1'],
            'approvers.*' => ['required', 'integer', 'distinct', 'exists:users,id'],
        ], [
            'approvers.required' => 'Pilih minimal 1 approver.',
            'approvers.min' => 'Pilih minimal 1 approver.',
            'approvers.*.exists' => 'Approver tidak valid.',
        ]);

        $approverIds = array_values($validated['approvers'] ?? []);

        $validApproverIds = User::active()
            ->whereIn('id', $approverIds)
            ->whereHas('role.permissions', function ($query) {
                $query->where('name', 'documents.approve');
            })
            ->pluck('id')
            ->all();

        if (count($validApproverIds) !== count($approverIds)) {
            return back()->withInput()->with('error', 'Approver harus memiliki izin approve.');
        }

        $oldStatus = $document->status;

        $document->update([
            'status' => Document::STATUS_PENDING_APPROVAL,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'updated_by' => $user->id,
        ]);

        // Create approval chain if provided
        $sequence = 1;
        foreach ($approverIds as $approverId) {
            DocumentApproval::create([
                'document_id' => $document->id,
                'approver_id' => $approverId,
                'sequence' => $sequence++,
                'status' => 'pending',
            ]);
        }

        // Record history
        DocumentHistory::create([
            'document_id' => $document->id,
            'action' => DocumentHistory::ACTION_SUBMITTED_APPROVAL,
            'performed_by' => $user->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => Document::STATUS_PENDING_APPROVAL],
            'description' => 'Dokumen diajukan untuk approval.',
        ]);

        // Notify first approver
        $firstApproval = $document->approvals()->orderBy('sequence')->first();
        if ($firstApproval) {
            Notification::create([
                'user_id' => $firstApproval->approver_id,
                'title' => 'Dokumen Menunggu Approval',
                'message' => "Dokumen '{$document->title}' membutuhkan approval Anda.",
                'type' => Notification::TYPE_APPROVAL_REQUIRED,
                'priority' => Notification::PRIORITY_HIGH,
                'entity_type' => 'Document',
                'entity_id' => $document->id,
                'action_url' => route('documents.show', $document),
            ]);
        }

        AuditLog::log(
            'status_changed',
            AuditLog::MODULE_DOCUMENTS,
            'Document',
            $document->id,
            $document->title,
            ['status' => $oldStatus],
            ['status' => Document::STATUS_PENDING_APPROVAL],
            'Dokumen diajukan untuk approval.'
        );

        return back()->with('success', 'Dokumen berhasil diajukan untuk approval.');
    }

    /**
     * Approve document
     */
    public function approve(Request $request, Document $document)
    {
        $user = auth()->user();

        // Find pending approval for this user
        $approval = $document->approvals()
            ->where('approver_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$approval) {
            return back()->with('error', 'Anda tidak memiliki approval yang tertunda untuk dokumen ini.');
        }

        $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        try {
            DB::beginTransaction();

            // Update approval
            $approval->update([
                'status' => 'approved',
                'comments' => $request->notes,
                'responded_at' => now(),
            ]);

            // Record history
            DocumentHistory::create([
                'document_id' => $document->id,
                'action' => DocumentHistory::ACTION_APPROVED,
                'performed_by' => $user->id,
                'new_values' => ['approval_notes' => $request->notes],
                'description' => 'Dokumen disetujui.',
            ]);

            Notification::where('user_id', $user->id)
                ->where('type', Notification::TYPE_APPROVAL_REQUIRED)
                ->where('entity_type', 'Document')
                ->where('entity_id', $document->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);

            // Check if all approvals are complete
            $pendingApprovals = $document->approvals()->where('status', 'pending')->count();
            
            if ($pendingApprovals === 0) {
                // All approved - update document status
                $document->update([
                    'status' => Document::STATUS_APPROVED,
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                    'updated_by' => $user->id,
                ]);

                DocumentHistory::create([
                    'document_id' => $document->id,
                    'action' => DocumentHistory::ACTION_STATUS_CHANGED,
                    'performed_by' => $user->id,
                    'old_values' => ['status' => Document::STATUS_PENDING_APPROVAL],
                    'new_values' => ['status' => Document::STATUS_APPROVED],
                    'description' => 'Semua approval selesai. Dokumen disetujui.',
                ]);

                // Notify document creator
                Notification::create([
                    'user_id' => $document->created_by,
                    'title' => 'Dokumen Disetujui',
                    'message' => "Dokumen '{$document->title}' telah disetujui.",
                    'type' => Notification::TYPE_APPROVAL_APPROVED,
                    'priority' => Notification::PRIORITY_NORMAL,
                    'entity_type' => 'Document',
                    'entity_id' => $document->id,
                    'action_url' => route('documents.show', $document),
                ]);
            } else {
                // Notify next approver
                $nextApproval = $document->approvals()
                    ->where('status', 'pending')
                    ->orderBy('sequence')
                    ->first();
                    
                if ($nextApproval) {
                    Notification::create([
                        'user_id' => $nextApproval->approver_id,
                        'title' => 'Dokumen Menunggu Approval',
                        'message' => "Dokumen '{$document->title}' membutuhkan approval Anda.",
                        'type' => Notification::TYPE_APPROVAL_REQUIRED,
                        'priority' => Notification::PRIORITY_HIGH,
                        'entity_type' => 'Document',
                        'entity_id' => $document->id,
                        'action_url' => route('documents.show', $document),
                    ]);
                }
            }

            AuditLog::log(
                'approved',
                AuditLog::MODULE_DOCUMENTS,
                'Document',
                $document->id,
                $document->title,
                null,
                ['approved_by' => $user->id],
                'Dokumen disetujui.'
            );

            DB::commit();

            return back()->with('success', 'Dokumen berhasil disetujui.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Reject document
     */
    public function reject(Request $request, Document $document)
    {
        $user = auth()->user();

        $request->validate([
            'rejection_reason' => ['required', 'string'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $oldStatus = $document->status;

        try {
            DB::beginTransaction();

            // Update any pending approvals to rejected
            $document->approvals()
                ->where('approver_id', $user->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'rejected',
                    'comments' => $request->rejection_reason,
                    'responded_at' => now(),
                ]);

            // Update document status
            $document->update([
                'status' => Document::STATUS_REJECTED,
                'rejection_reason' => $request->rejection_reason,
                'updated_by' => $user->id,
            ]);

            // Record history
            DocumentHistory::create([
                'document_id' => $document->id,
                'action' => DocumentHistory::ACTION_REJECTED,
                'performed_by' => $user->id,
                'old_values' => ['status' => $oldStatus],
                'new_values' => [
                    'status' => Document::STATUS_REJECTED,
                    'rejection_reason' => $request->rejection_reason,
                ],
                'description' => 'Dokumen ditolak: ' . $request->rejection_reason,
            ]);

            Notification::where('user_id', $user->id)
                ->where('type', Notification::TYPE_APPROVAL_REQUIRED)
                ->where('entity_type', 'Document')
                ->where('entity_id', $document->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);

            // Notify document creator
            Notification::create([
                'user_id' => $document->created_by,
                'title' => 'Dokumen Ditolak',
                'message' => "Dokumen '{$document->title}' ditolak. Alasan: {$request->rejection_reason}",
                'type' => Notification::TYPE_APPROVAL_REJECTED,
                'priority' => Notification::PRIORITY_HIGH,
                'entity_type' => 'Document',
                'entity_id' => $document->id,
                'action_url' => route('documents.show', $document),
            ]);

            AuditLog::log(
                'rejected',
                AuditLog::MODULE_DOCUMENTS,
                'Document',
                $document->id,
                $document->title,
                ['status' => $oldStatus],
                ['status' => Document::STATUS_REJECTED, 'rejection_reason' => $request->rejection_reason],
                'Dokumen ditolak.'
            );

            DB::commit();

            return back()->with('success', 'Dokumen berhasil ditolak.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Publish document
     */
    public function publish(Document $document)
    {
        if ($document->status !== Document::STATUS_APPROVED) {
            return back()->with('error', 'Hanya dokumen yang sudah disetujui yang dapat dipublikasikan.');
        }

        $user = auth()->user();
        $oldStatus = $document->status;

        $document->update([
            'status' => Document::STATUS_PUBLISHED,
            'published_at' => now(),
            'updated_by' => $user->id,
        ]);

        DocumentHistory::create([
            'document_id' => $document->id,
            'action' => DocumentHistory::ACTION_PUBLISHED,
            'performed_by' => $user->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => Document::STATUS_PUBLISHED],
            'description' => 'Dokumen dipublikasikan.',
        ]);

        AuditLog::log(
            'published',
            AuditLog::MODULE_DOCUMENTS,
            'Document',
            $document->id,
            $document->title,
            ['status' => $oldStatus],
            ['status' => Document::STATUS_PUBLISHED],
            'Dokumen dipublikasikan.'
        );

        return back()->with('success', 'Dokumen berhasil dipublikasikan.');
    }

    /**
     * Archive document
     */
    public function archive(Document $document)
    {
        if (!in_array($document->status, [Document::STATUS_PUBLISHED, Document::STATUS_EXPIRED])) {
            return back()->with('error', 'Dokumen tidak dapat diarsipkan.');
        }

        $user = auth()->user();
        $oldStatus = $document->status;

        $document->update([
            'status' => Document::STATUS_ARCHIVED,
            'updated_by' => $user->id,
        ]);

        DocumentHistory::create([
            'document_id' => $document->id,
            'action' => DocumentHistory::ACTION_ARCHIVED,
            'performed_by' => $user->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => Document::STATUS_ARCHIVED],
            'description' => 'Dokumen diarsipkan.',
        ]);

        AuditLog::log(
            'archived',
            AuditLog::MODULE_DOCUMENTS,
            'Document',
            $document->id,
            $document->title,
            ['status' => $oldStatus],
            ['status' => Document::STATUS_ARCHIVED],
            'Dokumen diarsipkan.'
        );

        return back()->with('success', 'Dokumen berhasil diarsipkan.');
    }

    /**
     * Download document file
     */
    public function download(Document $document, ?DocumentVersion $version = null)
    {
        $user = auth()->user();
        
        // Check access
        if (!$user->isAdmin() && !$document->hasAccess($user, 'download')) {
            abort(403, 'Anda tidak memiliki akses untuk mengunduh dokumen ini.');
        }

        $version = $version ?? $document->currentVersion;

        if (!$version || !$version->file_path || !Storage::disk('public')->exists($version->file_path)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        // Increment download count
        $document->incrementDownloadCount();

        // Record download history
        DocumentHistory::create([
            'document_id' => $document->id,
            'action' => DocumentHistory::ACTION_DOWNLOADED,
            'performed_by' => $user->id,
            'new_values' => ['version' => $version->version_number],
            'description' => "Dokumen versi {$version->version_number} diunduh.",
        ]);

        $filePath = Storage::disk('public')->path($version->file_path);
        $filename = $version->file_name;
        if (!$filename) {
            $extension = $version->file_type ?: pathinfo($version->file_path, PATHINFO_EXTENSION);
            $extension = $extension ? ".{$extension}" : '';
            $filename = "{$document->document_number}{$extension}";
        }

        // Admin downloads original file, others get watermarked version
        if ($user->isAdmin() || $user->hasRole('super_admin')) {
            return Storage::disk('public')->download($version->file_path, $filename);
        }

        // Apply watermark for non-admin users
        try {
            $watermarkService = new \App\Services\PdfWatermarkService();
            $watermarkedPath = $watermarkService->apply($filePath);

            return response()->download($watermarkedPath, $filename)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            // Fallback to original if watermark fails
            \Log::error('Watermark failed: ' . $e->getMessage());
            return Storage::disk('public')->download($version->file_path, $filename);
        }
    }

    /**
     * Print document (preview)
     */
    public function print(Document $document)
    {
        $user = auth()->user();
        
        // Check access
        if (!$user->isAdmin() && !$document->hasAccess($user, 'view')) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
        }

        // Record print history
        DocumentHistory::create([
            'document_id' => $document->id,
            'action' => DocumentHistory::ACTION_PRINTED,
            'performed_by' => $user->id,
            'description' => 'Dokumen dicetak.',
        ]);

        $document->load(['documentType', 'documentCategory', 'unit', 'creator', 'latestApproval.approver']);

        return view('documents.print', compact('document'));
    }

    /**
     * Show document access management page
     */
    public function showAccess(Document $document)
    {
        $document->load('accessPermissions.user', 'accessPermissions.grantedBy', 'accessPermissions.unit', 'accessPermissions.role');
        
        $accesses = $document->accessPermissions;
        $users = User::active()
            ->whereNotIn('id', $accesses->whereNotNull('user_id')->pluck('user_id'))
            ->orderBy('name')
            ->get();
        $units = \App\Models\Unit::where('is_active', true)->orderBy('name')->get();
        $roles = \App\Models\Role::where('is_active', true)->orderBy('display_name')->get();
        
        return view('documents.access', compact('document', 'accesses', 'users', 'units', 'roles'));
    }

    /**
     * Store document access
     */
    public function storeAccess(Request $request, Document $document)
    {
        $accessType = $request->input('access_type', 'user');
        
        $rules = [
            'access_type' => ['required', 'in:user,unit,role'],
            'access_level' => ['required', 'in:view,download,edit,full'],
            'expires_at' => ['nullable', 'date', 'after:today'],
        ];
        
        if ($accessType === 'user') {
            $rules['user_id'] = ['required', 'exists:users,id'];
        } elseif ($accessType === 'unit') {
            $rules['unit_id'] = ['required', 'exists:units,id'];
        } elseif ($accessType === 'role') {
            $rules['role_id'] = ['required', 'exists:roles,id'];
        }
        
        $request->validate($rules);

        $accessData = [
            'document_id' => $document->id,
            'permission' => $request->access_level,
            'granted_by' => auth()->id(),
            'valid_until' => $request->expires_at,
        ];
        
        if ($accessType === 'user') {
            $accessData['user_id'] = $request->user_id;
        } elseif ($accessType === 'unit') {
            $accessData['unit_id'] = $request->unit_id;
        } elseif ($accessType === 'role') {
            $accessData['role_id'] = $request->role_id;
        }
        
        DocumentAccess::create($accessData);

        DocumentHistory::create([
            'document_id' => $document->id,
            'action' => DocumentHistory::ACTION_ACCESS_GRANTED,
            'performed_by' => auth()->id(),
            'new_values' => $accessData,
            'description' => 'Akses dokumen diberikan.',
        ]);

        return back()->with('success', 'Akses berhasil diberikan.');
    }

    /**
     * Revoke document access
     */
    public function revokeAccess(Document $document, DocumentAccess $access)
    {
        $userId = $access->user_id;
        
        $access->delete();

        DocumentHistory::create([
            'document_id' => $document->id,
            'action' => DocumentHistory::ACTION_ACCESS_REVOKED,
            'performed_by' => auth()->id(),
            'old_values' => ['user_id' => $userId],
            'description' => 'Akses dokumen dicabut.',
        ]);

        return back()->with('success', 'Akses berhasil dicabut.');
    }

    /**
     * Upload new version
     */
    public function uploadVersion(Request $request, Document $document)
    {
        if (!$document->isEditable()) {
            return back()->with('error', 'Dokumen tidak dapat diperbarui karena status saat ini.');
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:51200'],
            'change_summary' => ['nullable', 'string'],
        ], [
            'file.required' => 'File wajib diunggah.',
            'file.mimes' => 'File harus berformat PDF, DOC, atau DOCX.',
        ]);

        try {
            DB::beginTransaction();

            // Validate file security
            $file = $request->file('file');
            $securityService = new FileUploadSecurityService();
            $securityResult = $securityService->validate($file, ['pdf', 'doc', 'docx']);
            
            if (!$securityResult['valid']) {
                throw new \Exception($securityResult['error']);
            }

            $user = auth()->user();
            $newVersion = $document->current_version + 1;

            $changeSummary = $request->input('change_summary') ?? $request->input('change_notes');
            $this->uploadDocumentFile($document, $file, $newVersion, $changeSummary);
            
            $document->update([
                'current_version' => $newVersion,
                'updated_by' => $user->id,
            ]);

            DocumentHistory::create([
                'document_id' => $document->id,
                'action' => DocumentHistory::ACTION_VERSION_ADDED,
                'performed_by' => $user->id,
                'new_values' => [
                    'version' => $newVersion,
                    'change_summary' => $changeSummary,
                ],
                'description' => "Versi {$newVersion} diunggah.",
            ]);

            DB::commit();

            return back()->with('success', "Versi {$newVersion} berhasil diunggah.");
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get categories by type (AJAX)
     */
    public function categoriesByType(DocumentType $type)
    {
        $categories = $type->categories()->active()->sorted()->get(['id', 'name']);
        
        return response()->json($categories);
    }

    /**
     * Get search suggestions (AJAX)
     */
    public function searchSuggestions(Request $request)
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $user = auth()->user();
        
        // Build base query
        $documentsQuery = Document::query()
            ->select('id', 'title', 'document_number', 'document_type_id', 'status')
            ->with('type:id,name');

        // Non-admin users can only see published/approved or their own documents
        if (!$user->isAdmin()) {
            $documentsQuery->where(function ($q) use ($user) {
                $q->whereIn('status', [Document::STATUS_PUBLISHED, Document::STATUS_APPROVED])
                  ->orWhere('created_by', $user->id);
            });
        }

        // Search by title or document number
        $documents = $documentsQuery
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('document_number', 'like', "%{$query}%");
            })
            ->orderByRaw("CASE WHEN title LIKE ? THEN 0 WHEN document_number LIKE ? THEN 1 ELSE 2 END", ["{$query}%", "{$query}%"])
            ->limit(10)
            ->get();

        $suggestions = $documents->map(function ($doc) {
            return [
                'id' => $doc->id,
                'title' => $doc->title,
                'document_number' => $doc->document_number,
                'type' => $doc->type->name ?? '-',
                'status' => $doc->status,
                'url' => route('documents.show', $doc),
            ];
        });

        return response()->json($suggestions);
    }

    /**
     * Export documents to Excel
     */
    public function export(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'document_type_id' => $request->input('type_id'),
            'document_category_id' => $request->input('category_id'),
            'directorate_id' => $request->input('directorate_id'),
            'unit_id' => $request->input('unit_id'),
            'status' => $request->input('status'),
            'is_distributed' => $request->input('is_distributed'),
            'effective_date_from' => $request->input('effective_date_from'),
            'effective_date_to' => $request->input('effective_date_to'),
            'expiry_date_from' => $request->input('expiry_date_from'),
            'expiry_date_to' => $request->input('expiry_date_to'),
            'sort_by' => $request->input('sort_by', 'expiry_date'),
            'sort_dir' => $request->input('sort_dir', 'asc'),
        ];

        // Log export activity
        AuditLog::log(
            'documents_exported',
            AuditLog::MODULE_DOCUMENTS,
            'Document',
            null,
            'Batch Export',
            null,
            ['filters' => array_filter($filters)],
            'Dokumen diekspor ke Excel.'
        );

        $filename = 'dokumen_hukum_' . date('Y-m-d_His') . '.xlsx';

        return (new \App\Exports\DocumentsExport($filters))->download($filename);
    }

    /**
     * Export documents to PDF
     */
    public function exportPdf(Request $request)
    {
        $query = Document::with(['documentType', 'documentCategory', 'directorate', 'unit']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('document_number', 'like', "%{$search}%")
                    ->orWhere('keywords', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type_id')) {
            $query->where('document_type_id', $request->input('type_id'));
        }

        if ($request->filled('category_id')) {
            $query->where('document_category_id', $request->input('category_id'));
        }

        if ($request->filled('directorate_id')) {
            $query->where('directorate_id', $request->input('directorate_id'));
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->input('unit_id'));
        }

        if ($request->filled('status')) {
            $query->byStatus($request->input('status'));
        }

        // Date range filters
        if ($request->filled('effective_date_from')) {
            $query->where('effective_date', '>=', $request->input('effective_date_from'));
        }

        if ($request->filled('effective_date_to')) {
            $query->where('effective_date', '<=', $request->input('effective_date_to'));
        }

        if ($request->filled('expiry_date_from')) {
            $query->where('expiry_date', '>=', $request->input('expiry_date_from'));
        }

        if ($request->filled('expiry_date_to')) {
            $query->where('expiry_date', '<=', $request->input('expiry_date_to'));
        }

        // Sort
        $sortBy = $request->input('sort_by', 'expiry_date');
        $sortDir = $request->input('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        // Get documents (limit to 500 for PDF)
        $documents = $query->limit(500)->get();

        // Build active filters array for display
        $activeFilters = [];
        if ($request->filled('search')) {
            $activeFilters['Pencarian'] = $request->input('search');
        }
        if ($request->filled('type_id')) {
            $type = DocumentType::find($request->input('type_id'));
            $activeFilters['Jenis'] = $type?->name ?? '-';
        }
        if ($request->filled('directorate_id')) {
            $dir = Directorate::find($request->input('directorate_id'));
            $activeFilters['Direktorat'] = $dir?->name ?? '-';
        }
        if ($request->filled('status')) {
            $activeFilters['Status'] = ucfirst($request->input('status'));
        }

        // Calculate summary
        $now = now();
        $summary = [
            'total' => $documents->count(),
            'active' => $documents->filter(fn($d) => $d->expiry_date && $d->expiry_date->gt($now->copy()->addMonths(6)))->count(),
            'perpetual' => $documents->filter(fn($d) => !$d->expiry_date)->count(),
            'expiring_soon' => $documents->filter(fn($d) => $d->expiry_date && $d->expiry_date->lte($now->copy()->addMonths(6)) && $d->expiry_date->gte($now))->count(),
            'expired' => $documents->filter(fn($d) => $d->expiry_date && $d->expiry_date->lt($now))->count(),
        ];

        // Log export activity
        AuditLog::log(
            'documents_exported_pdf',
            AuditLog::MODULE_DOCUMENTS,
            'Document',
            null,
            'Batch Export PDF',
            null,
            ['filters' => array_filter($request->only(['search', 'type_id', 'status', 'directorate_id']))],
            'Dokumen diekspor ke PDF.'
        );

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('documents.pdf-export', [
            'documents' => $documents,
            'activeFilters' => $activeFilters,
            'summary' => $summary,
        ]);

        // Set landscape orientation
        $pdf->setPaper('A4', 'landscape');

        $filename = 'dokumen_hukum_' . date('Y-m-d_His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Generate document number
     */
    protected function generateDocumentNumber(DocumentType $type): string
    {
        $year = date('Y');
        $month = date('m');
        
        // Count documents of this type this year
        $count = Document::where('document_type_id', $type->id)
            ->whereYear('created_at', $year)
            ->count() + 1;
        
        $prefix = $type->prefix ?? strtoupper(substr($type->code, 0, 3));
        
        return sprintf('%s/%04d/%s/%s', $prefix, $count, $month, $year);
    }

    /**
     * Upload document file
     */
    protected function uploadDocumentFile(Document $document, $file, int $versionNumber, ?string $changeSummary = null): DocumentVersion
    {
        $originalFilename = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $fileSize = $file->getSize();
        
        // Generate unique filename
        $filename = sprintf(
            '%s_v%d_%s.%s',
            Str::slug($document->document_number ?? $document->id),
            $versionNumber,
            Str::random(8),
            $extension
        );
        
        // Store file
        $path = $file->storeAs('documents/' . date('Y/m'), $filename, 'public');
        
        // Calculate file hash
        $hash = hash_file('sha256', $file->getRealPath());

        $version = DocumentVersion::create([
            'document_id' => $document->id,
            'version_number' => $versionNumber,
            'file_path' => $path,
            'file_name' => $originalFilename,
            'file_type' => $extension,
            'file_size' => $fileSize,
            'file_hash' => $hash,
            'change_summary' => $changeSummary,
            'change_type' => $versionNumber === 1 ? DocumentVersion::CHANGE_INITIAL : DocumentVersion::CHANGE_MINOR,
            'is_current' => true,
            'uploaded_by' => auth()->id(),
        ]);

        DocumentVersion::where('document_id', $document->id)
            ->where('id', '!=', $version->id)
            ->update(['is_current' => false]);

        return $version;
    }

    /**
     * Bulk archive documents
     */
    public function bulkArchive(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:documents,id',
        ]);

        $ids = $request->input('ids');
        $user = auth()->user();

        $archived = 0;
        foreach ($ids as $id) {
            $document = Document::find($id);
            
            if ($document && $document->status !== Document::STATUS_ARCHIVED) {
                $document->update([
                    'status' => Document::STATUS_ARCHIVED,
                    'updated_by' => $user->id,
                ]);

                DocumentHistory::create([
                    'document_id' => $document->id,
                    'action' => DocumentHistory::ACTION_STATUS_CHANGED,
                    'performed_by' => $user->id,
                    'old_values' => ['status' => $document->getOriginal('status')],
                    'new_values' => ['status' => Document::STATUS_ARCHIVED],
                    'description' => 'Dokumen diarsipkan (bulk action).',
                ]);

                $archived++;
            }
        }

        AuditLog::log(
            'documents_bulk_archived',
            AuditLog::MODULE_DOCUMENTS,
            'Document',
            null,
            'Bulk Archive',
            null,
            ['count' => $archived, 'ids' => $ids],
            "{$archived} dokumen berhasil diarsipkan."
        );

        return response()->json([
            'success' => true,
            'message' => "{$archived} dokumen berhasil diarsipkan.",
        ]);
    }

    /**
     * Bulk delete documents (soft delete)
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:documents,id',
        ]);

        $ids = $request->input('ids');
        $user = auth()->user();

        $deleted = 0;
        foreach ($ids as $id) {
            $document = Document::find($id);
            
            if ($document) {
                // Only allow deletion of drafts by non-admin
                if (!$user->isAdmin() && $document->status !== Document::STATUS_DRAFT) {
                    continue;
                }

                DocumentHistory::create([
                    'document_id' => $document->id,
                    'action' => DocumentHistory::ACTION_DELETED,
                    'performed_by' => $user->id,
                    'description' => 'Dokumen dihapus (bulk action).',
                ]);

                $document->delete();
                $deleted++;
            }
        }

        AuditLog::log(
            'documents_bulk_deleted',
            AuditLog::MODULE_DOCUMENTS,
            'Document',
            null,
            'Bulk Delete',
            null,
            ['count' => $deleted, 'ids' => $ids],
            "{$deleted} dokumen berhasil dihapus."
        );

        return response()->json([
            'success' => true,
            'message' => "{$deleted} dokumen berhasil dihapus.",
        ]);
    }

    /**
     * Compare two versions of a document
     */
    public function compareVersions(Document $document, DocumentVersion $version1, DocumentVersion $version2)
    {
        $user = auth()->user();
        
        // Check access
        if (!$user->isAdmin() && !$document->isAccessibleBy($user)) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
        }

        // Ensure versions belong to this document
        if ($version1->document_id !== $document->id || $version2->document_id !== $document->id) {
            abort(404, 'Versi tidak ditemukan.');
        }

        // Order versions (older first)
        if ($version1->version_number > $version2->version_number) {
            [$version1, $version2] = [$version2, $version1];
        }

        // Load relations
        $version1->load('uploader');
        $version2->load('uploader');
        $document->load('type', 'category');

        // Get document history between versions
        $history = DocumentHistory::where('document_id', $document->id)
            ->whereBetween('created_at', [$version1->created_at, $version2->created_at])
            ->with('performer')
            ->orderBy('created_at')
            ->get();

        // Calculate metadata differences
        $metadataDiff = $this->calculateVersionDiff($version1, $version2);

        return view('documents.compare-versions', compact(
            'document', 'version1', 'version2', 'history', 'metadataDiff'
        ));
    }

    /**
     * Calculate differences between two versions
     */
    protected function calculateVersionDiff(DocumentVersion $v1, DocumentVersion $v2): array
    {
        $diff = [];

        // File changes
        if ($v1->file_size !== $v2->file_size) {
            $diff['file_size'] = [
                'old' => $v1->file_size_formatted,
                'new' => $v2->file_size_formatted,
                'change' => $v2->file_size > $v1->file_size ? 'increased' : 'decreased',
            ];
        }

        if ($v1->file_hash !== $v2->file_hash) {
            $diff['file_content'] = [
                'changed' => true,
                'message' => 'Konten file berubah',
            ];
        }

        if ($v1->file_name !== $v2->file_name) {
            $diff['filename'] = [
                'old' => $v1->file_name,
                'new' => $v2->file_name,
            ];
        }

        return $diff;
    }

    /**
     * Restore a previous version (Admin only)
     */
    public function restoreVersion(Request $request, Document $document, DocumentVersion $version)
    {
        $user = auth()->user();

        // Admin only
        if (!$user->isAdmin()) {
            abort(403, 'Hanya admin yang dapat mengembalikan versi.');
        }

        // Ensure version belongs to this document
        if ($version->document_id !== $document->id) {
            abort(404, 'Versi tidak ditemukan.');
        }

        // Cannot restore current version
        if ($version->is_current) {
            return back()->with('error', 'Versi ini sudah merupakan versi aktif.');
        }

        // Check if file exists
        if (!$version->fileExists()) {
            return back()->with('error', 'File versi ini tidak ditemukan.');
        }

        // Create new version from old version
        $newVersionNumber = $document->current_version + 1;
        
        $newVersion = DocumentVersion::create([
            'document_id' => $document->id,
            'version_number' => $newVersionNumber,
            'file_path' => $version->file_path,
            'file_name' => $version->file_name,
            'file_size' => $version->file_size,
            'file_type' => $version->file_type,
            'file_hash' => $version->file_hash,
            'change_summary' => "Dipulihkan dari versi {$version->version_number}",
            'change_type' => DocumentVersion::CHANGE_CORRECTION,
            'uploaded_by' => $user->id,
            'is_current' => true,
        ]);

        // Mark all other versions as not current
        DocumentVersion::where('document_id', $document->id)
            ->where('id', '!=', $newVersion->id)
            ->update(['is_current' => false]);

        // Update document
        $document->update([
            'current_version' => $newVersionNumber,
            'updated_by' => $user->id,
        ]);

        // Log history
        DocumentHistory::create([
            'document_id' => $document->id,
            'action' => DocumentHistory::ACTION_VERSION_RESTORED,
            'performed_by' => $user->id,
            'old_values' => ['version' => $document->current_version - 1],
            'new_values' => ['version' => $newVersionNumber, 'restored_from' => $version->version_number],
            'description' => "Versi {$version->version_number} dipulihkan sebagai versi {$newVersionNumber}.",
        ]);

        // Audit log
        AuditLog::log(
            'document_version_restored',
            AuditLog::MODULE_DOCUMENTS,
            'Document',
            $document->id,
            'Version Restored',
            null,
            [
                'restored_from_version' => $version->version_number,
                'new_version' => $newVersionNumber,
            ],
            "Versi {$version->version_number} dipulihkan sebagai versi {$newVersionNumber}."
        );

        return back()->with('success', "Versi {$version->version_number} berhasil dipulihkan sebagai versi {$newVersionNumber}.");
    }
}
