<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\DirectorateRequest;
use App\Models\AuditLog;
use App\Models\Directorate;
use Illuminate\Http\Request;

class DirectorateController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:master.create')->only(['create', 'store']);
        $this->middleware('permission:master.edit')->only(['edit', 'update']);
        $this->middleware('permission:master.delete')->only(['destroy']);
    }

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
    public function store(DirectorateRequest $request)
    {
        $validated = $request->validated();
        if (!array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
            $validated['sort_order'] = (int) Directorate::max('sort_order') + 1;
        }

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
    public function update(DirectorateRequest $request, Directorate $directorate)
    {
        $validated = $request->validated();
        $oldValues = $directorate->only(['code', 'name', 'description', 'is_active', 'sort_order']);

        if (!array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
            $validated['sort_order'] = $directorate->sort_order;
        }

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
