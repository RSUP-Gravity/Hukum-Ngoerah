<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    /**
     * Display a listing of positions
     */
    public function index(Request $request)
    {
        $query = Position::withCount('users');
        
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
        
        // Filter by can_approve
        if ($request->has('can_approve')) {
            $query->where('can_approve_documents', $request->boolean('can_approve'));
        }

        $positions = $query->byLevel()->paginate(20);

        return view('master.positions.index', compact('positions'));
    }

    /**
     * Show the form for creating a new position
     */
    public function create()
    {
        return view('master.positions.create');
    }

    /**
     * Store a newly created position
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:positions'],
            'name' => ['required', 'string', 'max:150'],
            'level' => ['required', 'integer', 'min:0', 'max:100'],
            'can_approve_documents' => ['boolean'],
            'is_active' => ['boolean'],
        ], [
            'code.required' => 'Kode jabatan wajib diisi.',
            'code.unique' => 'Kode jabatan sudah digunakan.',
            'name.required' => 'Nama jabatan wajib diisi.',
            'level.required' => 'Level jabatan wajib diisi.',
        ]);

        $validated['sort_order'] = Position::max('sort_order') + 1;

        $position = Position::create($validated);

        AuditLog::log(
            'created',
            AuditLog::MODULE_MASTER_DATA,
            'Position',
            $position->id,
            $position->name,
            null,
            $validated,
            'Jabatan baru ditambahkan.'
        );

        return redirect()->route('master.positions.index')
            ->with('success', "Jabatan {$position->name} berhasil ditambahkan.");
    }

    /**
     * Display the specified position
     */
    public function show(Position $position)
    {
        $position->load(['users' => function ($q) {
            $q->with('unit')->active()->orderBy('name')->limit(20);
        }]);

        return view('master.positions.show', compact('position'));
    }

    /**
     * Show the form for editing the specified position
     */
    public function edit(Position $position)
    {
        return view('master.positions.edit', compact('position'));
    }

    /**
     * Update the specified position
     */
    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', "unique:positions,code,{$position->id}"],
            'name' => ['required', 'string', 'max:150'],
            'level' => ['required', 'integer', 'min:0', 'max:100'],
            'can_approve_documents' => ['boolean'],
            'is_active' => ['boolean'],
        ], [
            'code.required' => 'Kode jabatan wajib diisi.',
            'code.unique' => 'Kode jabatan sudah digunakan.',
            'name.required' => 'Nama jabatan wajib diisi.',
            'level.required' => 'Level jabatan wajib diisi.',
        ]);

        $oldValues = $position->only(['code', 'name', 'level', 'can_approve_documents', 'is_active']);

        $position->update($validated);

        AuditLog::log(
            'updated',
            AuditLog::MODULE_MASTER_DATA,
            'Position',
            $position->id,
            $position->name,
            $oldValues,
            $validated,
            'Jabatan diperbarui.'
        );

        return redirect()->route('master.positions.index')
            ->with('success', "Jabatan {$position->name} berhasil diperbarui.");
    }

    /**
     * Remove the specified position
     */
    public function destroy(Position $position)
    {
        // Check if position has users
        if ($position->users()->exists()) {
            return back()->with('error', 'Jabatan tidak dapat dihapus karena masih memiliki pengguna.');
        }

        $name = $position->name;
        
        AuditLog::log(
            'deleted',
            AuditLog::MODULE_MASTER_DATA,
            'Position',
            $position->id,
            $name,
            $position->toArray(),
            null,
            'Jabatan dihapus.'
        );

        $position->delete();

        return redirect()->route('master.positions.index')
            ->with('success', "Jabatan {$name} berhasil dihapus.");
    }
}
