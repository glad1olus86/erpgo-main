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
                    return redirect()->back()->with('error', __('Работник уже заселён. Сначала выселите его.'));
                }

                // Check if room belongs to selected hotel
                $room = Room::find($request->room_id);
                if ($room->hotel_id != $request->hotel_id) {
                    return redirect()->back()->with('error', __('Комната не принадлежит выбранному отелю.'));
                }

                // Check if room has available spots
                if ($room->isFull()) {
                    return redirect()->back()->with('error', __('Комната полностью заполнена.'));
                }

                // Create the assignment
                $assignment = new RoomAssignment();
                $assignment->worker_id = $worker->id;
                $assignment->room_id = $request->room_id;
                $assignment->hotel_id = $request->hotel_id;
                $assignment->check_in_date = now();
                $assignment->created_by = Auth::user()->creatorId();
                $assignment->save();

                return redirect()->route('worker.show', $worker->id)->with('success', __('Работник успешно заселён.'));
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
    public function unassignWorker(Worker $worker)
    {
        if (Auth::user()->can('manage worker')) {
            if ($worker->created_by == Auth::user()->creatorId()) {
                $assignment = $worker->currentAssignment;

                if (!$assignment) {
                    return redirect()->back()->with('error', __('Работник не заселён.'));
                }

                // Set check-out date to mark as inactive
                $assignment->check_out_date = now();
                $assignment->save();

                return redirect()->back()->with('success', __('Работник успешно выселен.'));
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
}
