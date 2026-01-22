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
        
        // Sort
        $sortField = $request->input('sort', 'updated_at');
        $sortDir = $request->input('dir', 'desc');
        $query->orderBy($sortField, $sortDir);

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
        
        return view('documents.create', compact('types', 'categories', 'units'));
    }

    /**
     * Store a newly created document
     */
    public function store(Request $request)
    {
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
            'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:51200'], // 50MB max
        ], [
            'title.required' => 'Judul dokumen wajib diisi.',
            'document_type_id.required' => 'Jenis dokumen wajib dipilih.',
            'document_date.required' => 'Tanggal dokumen wajib diisi.',
            'confidentiality.required' => 'Tingkat kerahasiaan wajib dipilih.',
            'file.required' => 'File dokumen wajib diunggah.',
            'file.mimes' => 'File harus berformat PDF, DOC, atau DOCX.',
            'file.max' => 'Ukuran file maksimal 50MB.',
        ]);

        try {
            DB::beginTransaction();

            $user = auth()->user();
            
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
                'status' => Document::STATUS_DRAFT,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            // Handle file upload
            if ($request->hasFile('file')) {
                $this->uploadDocumentFile($document, $request->file('file'), 1);
            }

            // Record history
            DocumentHistory::create([
                'document_id' => $document->id,
                'action' => DocumentHistory::ACTION_CREATED,
                'performed_by' => $user->id,
                'description' => 'Dokumen dibuat.',
            ]);

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

            return redirect()->route('documents.show', $document)
                ->with('success', 'Dokumen berhasil ditambahkan.');
                
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
        if (!$user->isAdmin() && !$document->isAccessibleBy($user)) {
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

        return view('documents.show', compact('document'));
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
            'action' => DocumentHistory::ACTION_SUBMITTED,
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
                'related_model' => 'Document',
                'related_id' => $document->id,
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
        if (!$user->hasRole(['legal_head', 'legal_staff', 'admin', 'super_admin'])) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengajukan approval.');
        }

        $oldStatus = $document->status;

        $document->update([
            'status' => Document::STATUS_PENDING_APPROVAL,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'updated_by' => $user->id,
        ]);

        // Create approval chain if provided
        if ($request->filled('approvers')) {
            $sequence = 1;
            foreach ($request->approvers as $approverId) {
                DocumentApproval::create([
                    'document_id' => $document->id,
                    'approver_id' => $approverId,
                    'sequence' => $sequence++,
                    'status' => 'pending',
                ]);
            }
        }

        // Record history
        DocumentHistory::create([
            'document_id' => $document->id,
            'action' => DocumentHistory::ACTION_SENT_FOR_APPROVAL,
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
                'related_model' => 'Document',
                'related_id' => $document->id,
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
                'notes' => $request->notes,
                'approved_at' => now(),
            ]);

            // Record history
            DocumentHistory::create([
                'document_id' => $document->id,
                'action' => DocumentHistory::ACTION_APPROVED,
                'performed_by' => $user->id,
                'new_values' => ['approval_notes' => $request->notes],
                'description' => 'Dokumen disetujui.',
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
                    'related_model' => 'Document',
                    'related_id' => $document->id,
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
                        'related_model' => 'Document',
                        'related_id' => $document->id,
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
                    'notes' => $request->rejection_reason,
                    'approved_at' => now(),
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

            // Notify document creator
            Notification::create([
                'user_id' => $document->created_by,
                'title' => 'Dokumen Ditolak',
                'message' => "Dokumen '{$document->title}' ditolak. Alasan: {$request->rejection_reason}",
                'type' => Notification::TYPE_APPROVAL_REJECTED,
                'priority' => Notification::PRIORITY_HIGH,
                'related_model' => 'Document',
                'related_id' => $document->id,
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

        // Record download history
        DocumentHistory::create([
            'document_id' => $document->id,
            'action' => DocumentHistory::ACTION_DOWNLOADED,
            'performed_by' => $user->id,
            'new_values' => ['version' => $version->version_number],
            'description' => "Dokumen versi {$version->version_number} diunduh.",
        ]);

        $filePath = Storage::disk('public')->path($version->file_path);
        $filename = $version->original_filename ?? "{$document->document_number}.{$version->file_extension}";

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
            'change_notes' => ['nullable', 'string'],
        ], [
            'file.required' => 'File wajib diunggah.',
            'file.mimes' => 'File harus berformat PDF, DOC, atau DOCX.',
        ]);

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $newVersion = $document->current_version + 1;

            $this->uploadDocumentFile($document, $request->file('file'), $newVersion, $request->change_notes);
            
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
                    'change_notes' => $request->change_notes,
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
    protected function uploadDocumentFile(Document $document, $file, int $versionNumber, ?string $changeNotes = null): DocumentVersion
    {
        $originalFilename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();
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

        return DocumentVersion::create([
            'document_id' => $document->id,
            'version_number' => $versionNumber,
            'file_path' => $path,
            'original_filename' => $originalFilename,
            'file_size' => $fileSize,
            'file_extension' => $extension,
            'mime_type' => $mimeType,
            'file_hash' => $hash,
            'change_notes' => $changeNotes,
            'uploaded_by' => auth()->id(),
        ]);
    }
}
