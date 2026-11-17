<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::latest()->get();
        return view('admin.shifts.index', compact('shifts'));
    }

    public function create()
    {
        return view('admin.shifts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date_format:H:i,H:i:s'],
            'end_time' => ['required', 'date_format:H:i,H:i:s'],
            'late_after' => ['required', 'date_format:H:i,H:i:s'],
        ]);

        Shift::create($validated);

        return redirect()->route('admin.shifts.index')
            ->with('success', 'Shift created successfully.');
    }

    public function edit(Shift $shift)
    {
        return view('admin.shifts.edit', compact('shift'));
    }

    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date_format:H:i,H:i:s'],
            'end_time' => ['required', 'date_format:H:i,H:i:s'],
            'late_after' => ['required', 'date_format:H:i,H:i:s'],
        ]);

        $shift->update($validated);

        return redirect()->route('admin.shifts.index')
            ->with('success', 'Shift updated successfully.');
    }

    public function destroy(Shift $shift)
    {
        if ($shift->employees()->exists()) {
            return back()->withErrors(['shift' => 'This shift is assigned to employees. Reassign them before deleting it.']);
        }
        $shift->delete();

        return redirect()->route('admin.shifts.index')
            ->with('success', 'Shift deleted successfully.');
    }
}
