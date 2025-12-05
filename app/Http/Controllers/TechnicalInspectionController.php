<?php

namespace App\Http\Controllers;

use App\Models\TechnicalInspection;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TechnicalInspectionController extends Controller
{
    public function store(Request $request, Vehicle $vehicle)
    {
        if (!Auth::user()->can('technical_inspection_manage')) {
            return redirect()->back()->with('error', __('Недостаточно прав'));
        }

        if ($vehicle->created_by !== Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Автомобиль не найден'));
        }

        $request->validate([
            'inspection_date' => 'required|date',
            'next_inspection_date' => 'required|date|after:inspection_date',
            'mileage' => 'nullable|integer|min:0',
            'cost' => 'nullable|numeric|min:0',
            'service_station' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        TechnicalInspection::create([
            'vehicle_id' => $vehicle->id,
            'inspection_date' => $request->inspection_date,
            'next_inspection_date' => $request->next_inspection_date,
            'mileage' => $request->mileage,
            'cost' => $request->cost,
            'service_station' => $request->service_station,
            'description' => $request->description,
            'created_by' => Auth::user()->creatorId(),
        ]);

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', __('Запись техосмотра добавлена'));
    }

    public function update(Request $request, TechnicalInspection $inspection)
    {
        if (!Auth::user()->can('technical_inspection_manage')) {
            return redirect()->back()->with('error', __('Недостаточно прав'));
        }

        if ($inspection->created_by !== Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Запись не найдена'));
        }

        $request->validate([
            'inspection_date' => 'required|date',
            'next_inspection_date' => 'required|date|after:inspection_date',
            'mileage' => 'nullable|integer|min:0',
            'cost' => 'nullable|numeric|min:0',
            'service_station' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        $inspection->update([
            'inspection_date' => $request->inspection_date,
            'next_inspection_date' => $request->next_inspection_date,
            'mileage' => $request->mileage,
            'cost' => $request->cost,
            'service_station' => $request->service_station,
            'description' => $request->description,
        ]);

        return redirect()->route('vehicles.show', $inspection->vehicle_id)
            ->with('success', __('Запись техосмотра обновлена'));
    }

    public function destroy(TechnicalInspection $inspection)
    {
        if (!Auth::user()->can('technical_inspection_manage')) {
            return redirect()->back()->with('error', __('Недостаточно прав'));
        }

        if ($inspection->created_by !== Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Запись не найдена'));
        }

        $vehicleId = $inspection->vehicle_id;
        $inspection->delete();

        return redirect()->route('vehicles.show', $vehicleId)
            ->with('success', __('Запись техосмотра удалена'));
    }
}
