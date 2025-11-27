<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use App\Models\WorkPlace;
use App\Models\WorkAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WorkAssignmentController extends Controller
{
    /**
     * Show the form for assigning a worker to a work place.
     */
    public function assignForm(WorkPlace $workPlace)
    {
        if (Auth::user()->can('manage work place')) {
            if ($workPlace->created_by == Auth::user()->creatorId()) {
                return view('work_place.assign_form', compact('workPlace'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 403);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    /**
     * Assign a worker to a work place.
     */
    public function assignWorker(Request $request, WorkPlace $workPlace)
    {
        if (Auth::user()->can('manage work place')) {
            if ($workPlace->created_by == Auth::user()->creatorId()) {
                $validator = Validator::make(
                    $request->all(),
                    [
                        'worker_id' => 'required|exists:workers,id',
                    ]
                );

                if ($validator->fails()) {
                    return redirect()->back()->with('error', $validator->errors()->first());
                }

                $worker = Worker::find($request->worker_id);

                // Check if worker belongs to the same company
                if ($worker->created_by != Auth::user()->creatorId()) {
                    return redirect()->back()->with('error', __('Permission denied.'));
                }

                // Check if worker already has an active work assignment
                if ($worker->currentWorkAssignment) {
                    return redirect()->back()->with('error', __('Работник уже устроен на работу. Сначала уволите его.'));
                }

                // Create the assignment
                $assignment = new WorkAssignment();
                $assignment->worker_id = $worker->id;
                $assignment->work_place_id = $workPlace->id;
                $assignment->started_at = now();
                $assignment->created_by = Auth::user()->creatorId();
                $assignment->save();

                return redirect()->back()->with('success', __('Работник успешно устроен на работу.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Dismiss a worker from their work place (fire them).
     */
    public function dismissWorker(Worker $worker)
    {
        if (Auth::user()->can('manage work place')) {
            if ($worker->created_by == Auth::user()->creatorId()) {
                $assignment = $worker->currentWorkAssignment;

                if (!$assignment) {
                    return redirect()->back()->with('error', __('Работник не устроен на работу.'));
                }

                // Set the end date to dismiss the worker
                $assignment->ended_at = now();
                $assignment->save();

                return redirect()->back()->with('success', __('Работник успешно уволен.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
