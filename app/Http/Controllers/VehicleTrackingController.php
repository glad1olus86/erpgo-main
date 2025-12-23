<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Services\GpsTrackingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleTrackingController extends Controller
{
    public function __construct(
        private GpsTrackingService $trackingService
    ) {}

    /**
     * Get list of trips for a vehicle on a specific date.
     * GET /vehicles/{vehicle}/trips
     */
    public function trips(Request $request, Vehicle $vehicle): JsonResponse
    {
        // Check permission
        if (!$request->user()->can('vehicle_tracking_view')) {
            return response()->json([
                'error' => __('You do not have permission to view tracking data'),
                'code' => 'ACCESS_DENIED',
            ], 403);
        }

        // Check vehicle belongs to user's company
        if ($vehicle->created_by !== $request->user()->creatorId()) {
            return response()->json([
                'error' => __('You do not have access to this vehicle'),
                'code' => 'ACCESS_DENIED',
            ], 403);
        }

        $request->validate([
            'date' => 'nullable|date',
        ]);

        $date = $request->input('date')
            ? Carbon::parse($request->input('date'))
            : Carbon::today();

        $trips = $this->trackingService->getTripsForDate($vehicle, $date);

        return response()->json([
            'date' => $date->toDateString(),
            'trips' => $trips->map(function ($trip) {
                return [
                    'id' => $trip->id,
                    'started_at' => $trip->started_at->toIso8601String(),
                    'ended_at' => $trip->ended_at?->toIso8601String(),
                    'total_distance_km' => $trip->total_distance_km,
                    'is_active' => $trip->isActive(),
                ];
            }),
        ]);
    }

    /**
     * Get track points for a vehicle on a specific date.
     * GET /vehicles/{vehicle}/track
     */
    public function track(Request $request, Vehicle $vehicle): JsonResponse
    {
        // Check permission
        if (!$request->user()->can('vehicle_tracking_view')) {
            return response()->json([
                'error' => __('You do not have permission to view tracking data'),
                'code' => 'ACCESS_DENIED',
            ], 403);
        }

        // Check vehicle belongs to user's company
        if ($vehicle->created_by !== $request->user()->creatorId()) {
            return response()->json([
                'error' => __('You do not have access to this vehicle'),
                'code' => 'ACCESS_DENIED',
            ], 403);
        }

        $request->validate([
            'date' => 'nullable|date',
        ]);

        $date = $request->input('date')
            ? Carbon::parse($request->input('date'))
            : Carbon::today();

        // Get the latest trip for the date (or first if multiple)
        $trip = $this->trackingService->getLatestTripForDate($vehicle, $date);

        if (!$trip) {
            return response()->json([
                'date' => $date->toDateString(),
                'trip' => null,
                'points' => [],
            ]);
        }

        $points = $this->trackingService->getTrackWithGaps($trip);

        return response()->json([
            'date' => $date->toDateString(),
            'trip' => [
                'id' => $trip->id,
                'started_at' => $trip->started_at->toIso8601String(),
                'ended_at' => $trip->ended_at?->toIso8601String(),
                'total_distance_km' => $trip->total_distance_km,
                'is_active' => $trip->isActive(),
            ],
            'points' => $points,
        ]);
    }
}
