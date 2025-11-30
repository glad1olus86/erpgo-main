<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::user()->can('manage hotel')) // Using 'manage hotel' for simplicity as discussed, or we can use 'manage room' if created
        {
            $rooms = Room::where('created_by', '=', Auth::user()->creatorId())->with('hotel')->get();
            return view('room.index', compact('rooms'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if (Auth::user()->can('create hotel')) // Assuming same permission for now
        {
            $hotels = Hotel::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
            $selectedHotelId = $request->query('hotel_id'); // Get hotel_id from query parameter
            return view('room.create', compact('hotels', 'selectedHotelId'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Auth::user()->can('create hotel')) {
            $rules = [
                'hotel_id' => 'required',
                'room_number' => 'required|max:20',
                'capacity' => 'required|integer|min:1',
                'monthly_price' => 'required|numeric|min:0',
                'payment_type' => 'required|in:worker,agency,partial',
            ];
            
            // Если выбран частичный платёж, требуем сумму
            if ($request->payment_type === 'partial') {
                $rules['partial_amount'] = 'required|numeric|min:0';
            }
            
            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $room                = new Room();
            $room->hotel_id      = $request->hotel_id;
            $room->room_number   = $request->room_number;
            $room->capacity      = $request->capacity;
            $room->monthly_price = $request->monthly_price;
            $room->payment_type  = $request->payment_type;
            $room->partial_amount = $request->payment_type === 'partial' ? $request->partial_amount : null;
            $room->created_by    = Auth::user()->creatorId();
            $room->save();

            return redirect()->route('hotel.rooms', $request->hotel_id)->with('success', __('Номер успешно создан.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {
        if (Auth::user()->can('manage hotel')) {
            if ($room->created_by == Auth::user()->creatorId()) {
                $room->load(['currentAssignments.worker']);
                return view('room.show', compact('room'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Room $room)
    {
        if (Auth::user()->can('edit hotel')) {
            if ($room->created_by == Auth::user()->creatorId()) {
                $hotels = Hotel::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
                return view('room.edit', compact('room', 'hotels'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
    {
        if (Auth::user()->can('edit hotel')) {
            if ($room->created_by == Auth::user()->creatorId()) {
                $rules = [
                    'hotel_id' => 'required',
                    'room_number' => 'required|max:20',
                    'capacity' => 'required|integer|min:1',
                    'monthly_price' => 'required|numeric|min:0',
                    'payment_type' => 'required|in:worker,agency,partial',
                ];
                
                if ($request->payment_type === 'partial') {
                    $rules['partial_amount'] = 'required|numeric|min:0';
                }
                
                $validator = \Validator::make($request->all(), $rules);
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $room->hotel_id      = $request->hotel_id;
                $room->room_number   = $request->room_number;
                $room->capacity      = $request->capacity;
                $room->monthly_price = $request->monthly_price;
                $room->payment_type  = $request->payment_type;
                $room->partial_amount = $request->payment_type === 'partial' ? $request->partial_amount : null;
                $room->save();

                return redirect()->route('hotel.rooms', $request->hotel_id)->with('success', __('Номер успешно обновлён.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        if (Auth::user()->can('delete hotel')) {
            if ($room->created_by == Auth::user()->creatorId()) {
                $hotelId = $room->hotel_id;
                $room->delete();
                return redirect()->route('hotel.rooms', $hotelId)->with('success', __('Номер успешно удалён.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
