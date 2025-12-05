<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Worker;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    public function index()
    {
        if (!Auth::user()->can('vehicle_read')) {
            return redirect()->back()->with('error', __('Недостаточно прав'));
        }

        $vehicles = Vehicle::forCurrentUser()
            ->with('latestInspection', 'assignedPerson')
            ->orderBy('brand')
            ->paginate(15);

        return view('vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        if (!Auth::user()->can('vehicle_create')) {
            return redirect()->back()->with('error', __('Недостаточно прав'));
        }

        $workers = Worker::where('created_by', Auth::user()->creatorId())
            ->orderBy('first_name')
            ->get();
        
        $users = User::where('created_by', Auth::user()->creatorId())
            ->orWhere('id', Auth::user()->creatorId())
            ->orderBy('name')
            ->get();

        return view('vehicles.create', compact('workers', 'users'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->can('vehicle_create')) {
            return redirect()->back()->with('error', __('Недостаточно прав'));
        }

        $request->validate([
            'license_plate' => 'required|string|max:20',
            'brand' => 'required|string|max:100',
            'color' => 'nullable|string|max:50',
            'vin_code' => 'nullable|string|max:17',
            'fuel_consumption' => 'nullable|numeric|min:0|max:99.99',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'assigned_type' => 'nullable|in:worker,user',
            'assigned_id' => 'nullable|integer',
        ]);

        $data = $request->only(['license_plate', 'brand', 'color', 'vin_code', 'fuel_consumption']);
        $data['created_by'] = Auth::user()->creatorId();

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $fileName = time() . '_' . $request->photo->getClientOriginalName();
            $request->photo->move(public_path('uploads/vehicle_photos'), $fileName);
            $data['photo'] = $fileName;
        }

        // Handle assigned person
        if ($request->filled('assigned_type') && $request->filled('assigned_id')) {
            $data['assigned_type'] = $request->assigned_type === 'worker' ? Worker::class : User::class;
            $data['assigned_id'] = $request->assigned_id;
        }

        Vehicle::create($data);

        return redirect()->route('vehicles.index')
            ->with('success', __('Автомобиль успешно добавлен'));
    }

    public function show(Vehicle $vehicle)
    {
        if (!Auth::user()->can('vehicle_read')) {
            return redirect()->back()->with('error', __('Недостаточно прав'));
        }

        if ($vehicle->created_by !== Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Автомобиль не найден'));
        }

        $vehicle->load(['inspections', 'assignedPerson']);

        return view('vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle)
    {
        if (!Auth::user()->can('vehicle_edit')) {
            return redirect()->back()->with('error', __('Недостаточно прав'));
        }

        if ($vehicle->created_by !== Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Автомобиль не найден'));
        }

        $workers = Worker::where('created_by', Auth::user()->creatorId())
            ->orderBy('first_name')
            ->get();
        
        $users = User::where('created_by', Auth::user()->creatorId())
            ->orWhere('id', Auth::user()->creatorId())
            ->orderBy('name')
            ->get();

        return view('vehicles.edit', compact('vehicle', 'workers', 'users'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        if (!Auth::user()->can('vehicle_edit')) {
            return redirect()->back()->with('error', __('Недостаточно прав'));
        }

        if ($vehicle->created_by !== Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Автомобиль не найден'));
        }

        $request->validate([
            'license_plate' => 'required|string|max:20',
            'brand' => 'required|string|max:100',
            'color' => 'nullable|string|max:50',
            'vin_code' => 'nullable|string|max:17',
            'fuel_consumption' => 'nullable|numeric|min:0|max:99.99',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'assigned_type' => 'nullable|in:worker,user',
            'assigned_id' => 'nullable|integer',
        ]);

        $data = $request->only(['license_plate', 'brand', 'color', 'vin_code', 'fuel_consumption']);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($vehicle->photo && file_exists(public_path('uploads/vehicle_photos/' . $vehicle->photo))) {
                unlink(public_path('uploads/vehicle_photos/' . $vehicle->photo));
            }
            $fileName = time() . '_' . $request->photo->getClientOriginalName();
            $request->photo->move(public_path('uploads/vehicle_photos'), $fileName);
            $data['photo'] = $fileName;
        }

        // Handle assigned person
        if ($request->filled('assigned_type') && $request->filled('assigned_id')) {
            $data['assigned_type'] = $request->assigned_type === 'worker' ? Worker::class : User::class;
            $data['assigned_id'] = $request->assigned_id;
        } else {
            $data['assigned_type'] = null;
            $data['assigned_id'] = null;
        }

        $vehicle->update($data);

        return redirect()->route('vehicles.index')
            ->with('success', __('Автомобиль успешно обновлен'));
    }

    public function destroy(Vehicle $vehicle)
    {
        if (!Auth::user()->can('vehicle_delete')) {
            return redirect()->back()->with('error', __('Недостаточно прав'));
        }

        if ($vehicle->created_by !== Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Автомобиль не найден'));
        }

        // Delete photo
        if ($vehicle->photo && file_exists(public_path('uploads/vehicle_photos/' . $vehicle->photo))) {
            unlink(public_path('uploads/vehicle_photos/' . $vehicle->photo));
        }

        $vehicle->delete();

        return redirect()->route('vehicles.index')
            ->with('success', __('Автомобиль успешно удален'));
    }
}
