<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\WorkPlace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PositionController extends Controller
{
    /**
     * Display positions for a work place
     */
    public function index(WorkPlace $workPlace)
    {
        if (!Auth::user()->can('manage work place')) {
            return redirect()->back()->with('error', __('Недостаточно прав'));
        }

        // Multi-tenancy check
        if ($workPlace->created_by !== Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Рабочее место не найдено'));
        }

        $positions = $workPlace->positions()
            ->withCount(['currentAssignments as workers_count'])
            ->orderBy('name')
            ->get();

        return view('work_place.positions', compact('workPlace', 'positions'));
    }

    /**
     * Store a new position
     */
    public function store(Request $request, WorkPlace $workPlace)
    {
        if (!Auth::user()->can('manage work place')) {
            return redirect()->back()->with('error', __('Недостаточно прав'));
        }

        // Multi-tenancy check
        if ($workPlace->created_by !== Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Рабочее место не найдено'));
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Position::create([
            'work_place_id' => $workPlace->id,
            'name' => $request->name,
            'created_by' => Auth::user()->creatorId(),
        ]);

        return redirect()->back()->with('success', __('Должность создана'));
    }

    /**
     * Delete a position
     */
    public function destroy(Position $position)
    {
        if (!Auth::user()->can('manage work place')) {
            return redirect()->back()->with('error', __('Недостаточно прав'));
        }

        // Multi-tenancy check
        if ($position->created_by !== Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Должность не найдена'));
        }

        $position->delete();

        return redirect()->back()->with('success', __('Должность удалена'));
    }
}
