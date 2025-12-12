<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RoomAssignmentController extends Controller
{
    /**
     * Assign a worker to a hotel room (check-in).
     */
    public function assignWorker(Request $request, Worker $worker)
    {
        if (Auth::user()->can('manage worker')) {
            if ($worker->created_by == Auth::user()->creatorId()) {
                $validator = Validator::make(
                    $request->all(),
                    [
                        'hotel_id' => 'required|exists:hotels,id',
                        'room_id' => 'required|exists:rooms,id',
                    ]
                );

                if ($validator->fails()) {
                    return redirect()->back()->with('error', $validator->errors()->first());
                }

                // Check if worker already has an active assignment
                if ($worker->currentAssignment) {
                    return redirect()->back()->with('error', __('Worker is already housed. Check them out first.'));
                }

                // Check if room belongs to selected hotel
                $room = Room::find($request->room_id);
                if ($room->hotel_id != $request->hotel_id) {
                    return redirect()->back()->with('error', __('Room does not belong to the selected hotel.'));
                }

                // Check if room has available spots
                if ($room->isFull()) {
                    return redirect()->back()->with('error', __('Room is fully occupied.'));
                }

                // Create the assignment
                $assignment = new RoomAssignment();
                $assignment->worker_id = $worker->id;
                $assignment->room_id = $request->room_id;
                $assignment->hotel_id = $request->hotel_id;
                $assignment->check_in_date = now();
                $assignment->created_by = Auth::user()->creatorId();
                $assignment->save();

                // Check if redirect to mobile
                if ($request->input('redirect_to') === 'mobile') {
                    return redirect()->route('mobile.workers.show', $worker->id)->with('success', __('Worker successfully checked in.'));
                }
                return redirect()->route('worker.show', $worker->id)->with('success', __('Worker successfully checked in.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Unassign a worker from their current room (check-out).
     */
    public function unassignWorker(Request $request, Worker $worker)
    {
        if (Auth::user()->can('manage worker')) {
            if ($worker->created_by == Auth::user()->creatorId()) {
                $assignment = $worker->currentAssignment;

                if (!$assignment) {
                    return redirect()->back()->with('error', __('Worker is not housed.'));
                }

                // Set check-out date to mark as inactive
                $assignment->check_out_date = now();
                $assignment->save();

                // Check if redirect to mobile
                if ($request->input('redirect_to') === 'mobile') {
                    return redirect()->route('mobile.workers.show', $worker->id)->with('success', __('Worker successfully checked out.'));
                }
                return redirect()->back()->with('success', __('Worker successfully checked out.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Bulk checkout workers from a room.
     */
    public function bulkCheckout(Request $request, Room $room)
    {
        if (Auth::user()->can('manage worker')) {
            if ($room->created_by == Auth::user()->creatorId()) {
                $validator = Validator::make($request->all(), [
                    'worker_ids' => 'required|string',
                ]);

                if ($validator->fails()) {
                    return redirect()->back()->with('error', $validator->errors()->first());
                }

                $workerIds = array_filter(explode(',', $request->worker_ids));
                $checkedOut = 0;

                foreach ($workerIds as $workerId) {
                    $worker = Worker::where('id', $workerId)
                        ->where('created_by', Auth::user()->creatorId())
                        ->first();

                    if (!$worker) continue;

                    $assignment = $worker->currentAssignment;
                    if ($assignment && $assignment->room_id == $room->id) {
                        $assignment->check_out_date = now();
                        $assignment->save();
                        $checkedOut++;
                    }
                }

                return redirect()->back()->with('success', __('Workers checked out: :count', ['count' => $checkedOut]));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Get available rooms for a specific hotel (API endpoint).
     */
    public function getAvailableRooms(Hotel $hotel)
    {
        if (Auth::user()->can('manage hotel')) {
            if ($hotel->created_by == Auth::user()->creatorId()) {
                $rooms = $hotel->rooms()->with('currentAssignments')->get()->map(function ($room) {
                    return [
                        'id' => $room->id,
                        'room_number' => $room->room_number,
                        'capacity' => $room->capacity,
                        'occupied' => $room->currentAssignments->count(),
                        'available' => $room->availableSpots(),
                        'is_full' => $room->isFull(),
                        'occupancy_status' => $room->occupancyStatus(),
                    ];
                });

                return response()->json($rooms);
            } else {
                return response()->json(['error' => 'Permission denied'], 403);
            }
        } else {
            return response()->json(['error' => 'Permission denied'], 403);
        }
    }

    /**
     * Assign a worker to a room (from room modal).
     */
    public function assignWorkerToRoom(Request $request, Room $room)
    {
        if (Auth::user()->can('manage worker')) {
            if ($room->created_by == Auth::user()->creatorId()) {
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

                // Check if worker already has an active assignment
                if ($worker->currentAssignment) {
                    return redirect()->back()->with('error', __('Worker is already housed. Check them out first.'));
                }

                // Check if room has available spots
                if ($room->isFull()) {
                    return redirect()->back()->with('error', __('Room is fully occupied.'));
                }

                // Create the assignment
                $assignment = new RoomAssignment();
                $assignment->worker_id = $worker->id;
                $assignment->room_id = $room->id;
                $assignment->hotel_id = $room->hotel_id;
                $assignment->check_in_date = now();
                $assignment->created_by = Auth::user()->creatorId();
                $assignment->save();

                return redirect()->back()->with('success', __('Worker successfully checked in.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for assigning a worker to a room.
     */
    public function assignForm(Room $room)
    {
        if (Auth::user()->can('manage worker')) {
            if ($room->created_by == Auth::user()->creatorId()) {
                return view('room.assign_form', compact('room'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 403);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    /**
     * Bulk assign workers to a room.
     */
    public function assignWorkersBulk(Request $request, Room $room)
    {
        if (Auth::user()->can('manage worker')) {
            if ($room->created_by == Auth::user()->creatorId()) {
                $validator = Validator::make($request->all(), [
                    'worker_ids' => 'required|string',
                ]);

                if ($validator->fails()) {
                    return redirect()->back()->with('error', $validator->errors()->first());
                }

                $workerIds = array_filter(explode(',', $request->worker_ids));
                $availableSpots = $room->availableSpots();
                $assigned = 0;

                foreach ($workerIds as $workerId) {
                    if ($assigned >= $availableSpots) break;

                    $worker = Worker::where('id', $workerId)
                        ->where('created_by', Auth::user()->creatorId())
                        ->first();

                    if (!$worker) continue;
                    if ($worker->currentAssignment) continue;

                    $assignment = new RoomAssignment();
                    $assignment->worker_id = $worker->id;
                    $assignment->room_id = $room->id;
                    $assignment->hotel_id = $room->hotel_id;
                    $assignment->check_in_date = now();
                    $assignment->created_by = Auth::user()->creatorId();
                    $assignment->save();
                    $assigned++;
                }

                return redirect()->back()->with('success', __('Workers checked in: :count', ['count' => $assigned]));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
