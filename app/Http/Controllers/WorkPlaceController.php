<?php

namespace App\Http\Controllers;

use App\Models\WorkPlace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WorkPlaceController extends Controller
{
    /**
     * Display a listing of the work places.
     */
    public function index()
    {
        if (Auth::user()->can('manage work place')) {
            $workPlaces = WorkPlace::where('created_by', '=', Auth::user()->creatorId())
                ->with(['currentAssignments.worker'])
                ->get();

            return view('work_place.index', compact('workPlaces'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new work place.
     */
    public function create()
    {
        if (Auth::user()->can('create work place')) {
            return view('work_place.create');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created work place in storage.
     */
    public function store(Request $request)
    {
        if (Auth::user()->can('create work place')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|max:100',
                    'address' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $workPlace = new WorkPlace();
            $workPlace->name = $request->name;
            $workPlace->address = $request->address;
            $workPlace->phone = $request->phone;
            $workPlace->email = $request->email;
            $workPlace->created_by = Auth::user()->creatorId();
            $workPlace->save();

            return redirect()->route('work-place.index')->with('success', __('Рабочее место успешно создано.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for editing the specified work place.
     */
    public function edit(WorkPlace $workPlace)
    {
        if (Auth::user()->can('edit work place')) {
            if ($workPlace->created_by == Auth::user()->creatorId()) {
                return view('work_place.edit', compact('workPlace'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified work place in storage.
     */
    public function update(Request $request, WorkPlace $workPlace)
    {
        if (Auth::user()->can('edit work place')) {
            if ($workPlace->created_by == Auth::user()->creatorId()) {
                $validator = Validator::make(
                    $request->all(),
                    [
                        'name' => 'required|max:100',
                        'address' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $workPlace->name = $request->name;
                $workPlace->address = $request->address;
                $workPlace->phone = $request->phone;
                $workPlace->email = $request->email;
                $workPlace->save();

                return redirect()->route('work-place.index')->with('success', __('Рабочее место успешно обновлено.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified work place from storage.
     */
    public function destroy(WorkPlace $workPlace)
    {
        if (Auth::user()->can('delete work place')) {
            if ($workPlace->created_by == Auth::user()->creatorId()) {
                $workPlace->delete();
                return redirect()->route('work-place.index')->with('success', __('Рабочее место успешно удалено.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show workers assigned to this work place (for modal).
     */
    public function showWorkers(WorkPlace $workPlace)
    {
        if (Auth::user()->can('manage work place')) {
            if ($workPlace->created_by == Auth::user()->creatorId()) {
                $workPlace->load(['currentAssignments.worker']);
                return view('work_place.show', compact('workPlace'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 403);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }
}
