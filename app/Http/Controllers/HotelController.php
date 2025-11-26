<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HotelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::user()->can('manage hotel')) {
            $hotels = Hotel::where('created_by', '=', Auth::user()->creatorId())->with(['rooms.currentAssignments'])->get();

            return view('hotel.index', compact('hotels'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::user()->can('create hotel')) {
            return view('hotel.create');
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
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required|max:30',
                    'address' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $hotel             = new Hotel();
            $hotel->name       = $request->name;
            $hotel->address    = $request->address;
            $hotel->rating     = $request->rating;
            $hotel->created_by = Auth::user()->creatorId();
            $hotel->save();

            return redirect()->route('hotel.index')->with('success', __('Отель успешно создан.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display rooms for a specific hotel.
     */
    public function showRooms(Hotel $hotel)
    {
        if (Auth::user()->can('manage hotel')) {
            if ($hotel->created_by == Auth::user()->creatorId()) {
                $rooms = $hotel->rooms()->with('currentAssignments')->get();
                $hotels = Hotel::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');

                return view('hotel.rooms', compact('hotel', 'rooms', 'hotels'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Hotel $hotel)
    {
        if (Auth::user()->can('edit hotel')) {
            if ($hotel->created_by == Auth::user()->creatorId()) {
                return view('hotel.edit', compact('hotel'));
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
    public function update(Request $request, Hotel $hotel)
    {
        if (Auth::user()->can('edit hotel')) {
            if ($hotel->created_by == Auth::user()->creatorId()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name' => 'required|max:30',
                        'address' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $hotel->name    = $request->name;
                $hotel->address = $request->address;
                $hotel->rating  = $request->rating;
                $hotel->save();

                return redirect()->route('hotel.index')->with('success', __('Отель успешно изменён.'));
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
    public function destroy(Hotel $hotel)
    {
        if (Auth::user()->can('delete hotel')) {
            if ($hotel->created_by == Auth::user()->creatorId()) {
                $hotel->delete();

                return redirect()->route('hotel.index')->with('success', __('Отель успешно удалён.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
