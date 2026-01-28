<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\PositionRequest;
use App\Models\AuditLog;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:master.create')->only(['create', 'store']);
        $this->middleware('permission:master.edit')->only(['edit', 'update']);
        $this->middleware('permission:master.delete')->only(['destroy']);
    }

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
    public function store(PositionRequest $request)
    {
        $validated = $request->validated();
        if (!array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
            $validated['sort_order'] = (int) Position::max('sort_order') + 1;
        }

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
    public function update(PositionRequest $request, Position $position)
    {
        $validated = $request->validated();
        $oldValues = $position->only(['code', 'name', 'level', 'can_approve_documents', 'is_active', 'sort_order']);

        if (!array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
            $validated['sort_order'] = $position->sort_order;
        }

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
