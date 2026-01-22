<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Directorate;
use Illuminate\Http\Request;

class DirectorateController extends Controller
{
    /**
     * Display a listing of directorates
     */
    public function index(Request $request)
    {
        $query = Directorate::withCount('units');
        
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

        $directorates = $query->sorted()->paginate(20);

        return view('master.directorates.index', compact('directorates'));
    }

    /**
     * Show the form for creating a new directorate
     */
    public function create()
    {
        return view('master.directorates.create');
    }

    /**
     * Store a newly created directorate
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:directorates'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ], [
            'code.required' => 'Kode direktorat wajib diisi.',
            'code.unique' => 'Kode direktorat sudah digunakan.',
            'name.required' => 'Nama direktorat wajib diisi.',
        ]);

        $validated['sort_order'] = Directorate::max('sort_order') + 1;

        $directorate = Directorate::create($validated);

        AuditLog::log(
            'created',
            AuditLog::MODULE_MASTER_DATA,
            'Directorate',
            $directorate->id,
            $directorate->name,
            null,
            $validated,
            'Direktorat baru ditambahkan.'
        );

        return redirect()->route('master.directorates.index')
            ->with('success', "Direktorat {$directorate->name} berhasil ditambahkan.");
    }

    /**
     * Display the specified directorate
     */
    public function show(Directorate $directorate)
    {
        $directorate->load(['units' => function ($q) {
            $q->withCount('users')->sorted();
        }]);

        return view('master.directorates.show', compact('directorate'));
    }

    /**
     * Show the form for editing the specified directorate
     */
    public function edit(Directorate $directorate)
    {
        return view('master.directorates.edit', compact('directorate'));
    }

    /**
     * Update the specified directorate
     */
    public function update(Request $request, Directorate $directorate)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', "unique:directorates,code,{$directorate->id}"],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ], [
            'code.required' => 'Kode direktorat wajib diisi.',
            'code.unique' => 'Kode direktorat sudah digunakan.',
            'name.required' => 'Nama direktorat wajib diisi.',
        ]);

        $oldValues = $directorate->only(['code', 'name', 'description', 'is_active']);

        $directorate->update($validated);

        AuditLog::log(
            'updated',
            AuditLog::MODULE_MASTER_DATA,
            'Directorate',
            $directorate->id,
            $directorate->name,
            $oldValues,
            $validated,
            'Direktorat diperbarui.'
        );

        return redirect()->route('master.directorates.index')
            ->with('success', "Direktorat {$directorate->name} berhasil diperbarui.");
    }

    /**
     * Remove the specified directorate
     */
    public function destroy(Directorate $directorate)
    {
        // Check if directorate has units
        if ($directorate->units()->exists()) {
            return back()->with('error', 'Direktorat tidak dapat dihapus karena masih memiliki unit.');
        }

        $name = $directorate->name;
        
        AuditLog::log(
            'deleted',
            AuditLog::MODULE_MASTER_DATA,
            'Directorate',
            $directorate->id,
            $name,
            $directorate->toArray(),
            null,
            'Direktorat dihapus.'
        );

        $directorate->delete();

        return redirect()->route('master.directorates.index')
            ->with('success', "Direktorat {$name} berhasil dihapus.");
    }
}
