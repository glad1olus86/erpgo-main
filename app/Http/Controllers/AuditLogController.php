<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (Auth::user()->can('view audit log')) {
            $query = AuditLog::forCurrentUser()->with('user');

            // Фильтр по дате
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->byDateRange($request->start_date, $request->end_date);
            }

            // Фильтр по пользователю
            if ($request->filled('user_id')) {
                $query->byUser($request->user_id);
            }

            // Фильтр по типу события
            if ($request->filled('event_type')) {
                $query->byEventType($request->event_type);
            }

            $auditLogs = $query->latest()->paginate(20);
            $users = User::where('created_by', '=', Auth::user()->creatorId())->get()->pluck('name', 'id');

            // Список всех возможных типов событий для фильтра
            $eventTypes = [
                'worker.created' => __('Создание работника'),
                'worker.updated' => __('Обновление работника'),
                'worker.deleted' => __('Удаление работника'),
                'worker.checked_in' => __('Заселение'),
                'worker.checked_out' => __('Выселение'),
                'worker.hired' => __('Трудоустройство'),
                'worker.dismissed' => __('Увольнение'),
                'room.created' => __('Создание комнаты'),
                'room.updated' => __('Обновление комнаты'),
                'room.deleted' => __('Удаление комнаты'),
                'work_place.created' => __('Создание рабочего места'),
                'work_place.updated' => __('Обновление рабочего места'),
                'work_place.deleted' => __('Удаление рабочего места'),
                'hotel.created' => __('Создание отеля'),
                'hotel.updated' => __('Обновление отеля'),
                'hotel.deleted' => __('Удаление отеля'),
            ];

            return view('audit_log.index', compact('auditLogs', 'users', 'eventTypes'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function calendarView(Request $request)
    {
        if (Auth::user()->can('view audit log')) {
            return view('audit_log.calendar');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Get calendar data (API).
     */
    public function calendar($year, $month)
    {
        if (Auth::user()->can('view audit log')) {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            $logs = AuditLog::forCurrentUser()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get()
                ->groupBy(function ($log) {
                    return $log->created_at->day;
                });

            $days = [];
            foreach ($logs as $day => $dayLogs) {
                $days[$day] = [
                    'total' => $dayLogs->count(),
                    'events' => $dayLogs->groupBy('event_type')
                        ->map->count()
                        ->toArray()
                ];
            }

            return response()->json([
                'year' => $year,
                'month' => $month,
                'days' => $days
            ]);
        } else {
            return response()->json(['error' => 'Permission denied'], 403);
        }
    }

    /**
     * Get details for a specific day.
     */
    public function dayDetails($date)
    {
        if (Auth::user()->can('view audit log')) {
            $parsedDate = Carbon::parse($date);

            $logs = AuditLog::forCurrentUser()
                ->whereDate('created_at', $parsedDate)
                ->with('user')
                ->latest()
                ->get()
                ->groupBy(function ($log) {
                    return $log->user_name;
                });

            return view('audit_log.day_details', compact('logs', 'parsedDate'));
        } else {
            return response()->json(['error' => 'Permission denied'], 403);
        }
    }
}
