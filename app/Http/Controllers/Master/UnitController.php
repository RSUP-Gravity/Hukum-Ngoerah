<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\UnitRequest;
use App\Models\AuditLog;
use App\Models\Directorate;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:master.create')->only(['create', 'store']);
        $this->middleware('permission:master.edit')->only(['edit', 'update']);
        $this->middleware('permission:master.delete')->only(['destroy']);
    }

    /**
     * Display a listing of units
     */
    public function index(Request $request)
    {
        $query = Unit::with('directorate')->withCount('users');
        
        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }
        
        // Filter by directorate
        if ($request->filled('directorate_id')) {
            $query->where('directorate_id', $request->directorate_id);
        }
        
        // Filter by status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $units = $query->sorted()->paginate(20);
        $directorates = Directorate::active()->sorted()->get();

        return view('master.units.index', compact('units', 'directorates'));
    }

    /**
     * Show the form for creating a new unit
     */
    public function create()
    {
        $directorates = Directorate::active()->sorted()->get();
        
        return view('master.units.create', compact('directorates'));
    }

    /**
     * Store a newly created unit
     */
    public function store(UnitRequest $request)
    {
        $validated = $request->validated();
        if (!array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
            $validated['sort_order'] = (int) Unit::where('directorate_id', $validated['directorate_id'])
                ->max('sort_order') + 1;
        }

        $unit = Unit::create($validated);

        AuditLog::log(
            'created',
            AuditLog::MODULE_MASTER_DATA,
            'Unit',
            $unit->id,
            $unit->name,
            null,
            $validated,
            'Unit baru ditambahkan.'
        );

        return redirect()->route('master.units.index')
            ->with('success', "Unit {$unit->name} berhasil ditambahkan.");
    }

    /**
     * Display the specified unit
     */
    public function show(Unit $unit)
    {
        $unit->load(['directorate', 'users' => function ($q) {
            $q->with('position')->active()->orderBy('name');
        }]);

        return view('master.units.show', compact('unit'));
    }

    /**
     * Show the form for editing the specified unit
     */
    public function edit(Unit $unit)
    {
        $directorates = Directorate::active()->sorted()->get();
        
        return view('master.units.edit', compact('unit', 'directorates'));
    }

    /**
     * Update the specified unit
     */
    public function update(UnitRequest $request, Unit $unit)
    {
        $validated = $request->validated();
        $oldValues = $unit->only(['directorate_id', 'code', 'name', 'description', 'is_active', 'sort_order']);
        $directorateChanged = $unit->directorate_id !== (int) $validated['directorate_id'];

        if (!array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
            if ($directorateChanged) {
                $validated['sort_order'] = (int) Unit::where('directorate_id', $validated['directorate_id'])
                    ->max('sort_order') + 1;
            } else {
                $validated['sort_order'] = $unit->sort_order;
            }
        }

        $unit->update($validated);

        AuditLog::log(
            'updated',
            AuditLog::MODULE_MASTER_DATA,
            'Unit',
            $unit->id,
            $unit->name,
            $oldValues,
            $validated,
            'Unit diperbarui.'
        );

        return redirect()->route('master.units.index')
            ->with('success', "Unit {$unit->name} berhasil diperbarui.");
    }

    /**
     * Remove the specified unit
     */
    public function destroy(Unit $unit)
    {
        // Check if unit has users
        if ($unit->users()->exists()) {
            return back()->with('error', 'Unit tidak dapat dihapus karena masih memiliki pengguna.');
        }

        // Check if unit has documents
        if ($unit->documents()->exists()) {
            return back()->with('error', 'Unit tidak dapat dihapus karena masih memiliki dokumen.');
        }

        $name = $unit->name;
        
        AuditLog::log(
            'deleted',
            AuditLog::MODULE_MASTER_DATA,
            'Unit',
            $unit->id,
            $name,
            $unit->toArray(),
            null,
            'Unit dihapus.'
        );

        $unit->delete();

        return redirect()->route('master.units.index')
            ->with('success', "Unit {$name} berhasil dihapus.");
    }

    /**
     * Get units by directorate (AJAX)
     */
    public function byDirectorate(Directorate $directorate)
    {
        $units = $directorate->activeUnits;
        
        return response()->json($units);
    }
}
