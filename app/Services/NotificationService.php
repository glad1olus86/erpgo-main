<?php

namespace App\Services;

use App\Models\Hotel;
use App\Models\Worker;
use App\Models\SystemNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    protected $userId;
    protected $settings;

    public function __construct()
    {
        if (Auth::check()) {
            $this->userId = Auth::user()->creatorId();
            $this->loadSettings();
        }
    }

    protected function loadSettings()
    {
        $this->settings = DB::table('settings')
            ->where('created_by', 1)
            ->whereIn('name', [
                'notifications_enabled',
                'notification_poll_interval',
                'notification_create_interval',
                'notification_hotel_occupancy_threshold',
                'notification_unemployed_days'
            ])
            ->pluck('value', 'name')
            ->toArray();
    }

    protected function getSetting($key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Check if we should create new notifications (based on create interval)
     */
    public function shouldCreateNotifications(): bool
    {
        if ($this->getSetting('notifications_enabled', 'on') !== 'on') {
            \Log::info('Notifications disabled');
            return false;
        }

        $cacheKey = 'notification_last_create_' . $this->userId;
        $interval = (int) $this->getSetting('notification_create_interval', 60); // minutes
        $lastCreate = Cache::get($cacheKey);

        \Log::info('Notification check', [
            'cacheKey' => $cacheKey,
            'interval' => $interval,
            'lastCreate' => $lastCreate,
            'now' => now()->toDateTimeString(),
            'diffInMinutes' => $lastCreate ? now()->diffInMinutes($lastCreate) : 'no cache'
        ]);

        if (!$lastCreate) {
            Cache::put($cacheKey, now(), 60 * 24);
            \Log::info('No cache - creating notifications');
            return true;
        }

        $minutesPassed = now()->diffInMinutes($lastCreate, absolute: true);
        
        if ($minutesPassed >= $interval) {
            Cache::put($cacheKey, now(), 60 * 24);
            \Log::info('Interval passed - creating notifications', ['minutesPassed' => $minutesPassed]);
            return true;
        }

        \Log::info('Interval not passed yet', ['minutesPassed' => $minutesPassed, 'needMinutes' => $interval]);
        return false;
    }

    /**
     * Run all notification checks
     */
    public function runChecks(): array
    {
        $newNotifications = [];

        // Only create new notifications if interval passed
        if ($this->shouldCreateNotifications()) {
            $newNotifications = array_merge(
                $this->checkHotelOccupancy(),
                $this->checkUnemployedWorkers()
            );
        }

        return $newNotifications;
    }

    /**
     * Check hotel occupancy and create notifications
     */
    public function checkHotelOccupancy(): array
    {
        $threshold = (int) $this->getSetting('notification_hotel_occupancy_threshold', 50);
        $notifications = [];

        $hotels = Hotel::where('created_by', $this->userId)->with('rooms')->get();

        foreach ($hotels as $hotel) {
            $totalCapacity = 0;
            $totalOccupied = 0;

            foreach ($hotel->rooms as $room) {
                $totalCapacity += $room->capacity;
                $totalOccupied += $room->currentAssignments()->count();
            }

            if ($totalCapacity == 0) continue;

            $occupancyPercent = round(($totalOccupied / $totalCapacity) * 100);

            if ($occupancyPercent < $threshold) {
                // Create notification (no duplicate check - interval controls frequency)
                $notification = SystemNotification::create([
                    'type' => SystemNotification::TYPE_HOTEL_OCCUPANCY,
                    'title' => __('Низкая заполненность отеля'),
                    'message' => __('Отель ":name" заполнен на :occupied/:total мест (:percent%)', [
                        'name' => $hotel->name,
                        'occupied' => $totalOccupied,
                        'total' => $totalCapacity,
                        'percent' => $occupancyPercent
                    ]),
                    'data' => [
                        'hotel_id' => $hotel->id,
                        'occupied' => $totalOccupied,
                        'total' => $totalCapacity,
                        'percent' => $occupancyPercent,
                    ],
                    'link' => route('hotel.rooms', $hotel->id),
                    'created_by' => $this->userId,
                ]);
                $notifications[] = $notification;
            }
        }

        return $notifications;
    }

    /**
     * Check for workers living in hotel but not employed
     */
    public function checkUnemployedWorkers(): array
    {
        $daysThreshold = (int) $this->getSetting('notification_unemployed_days', 3);
        $notifications = [];

        $workers = Worker::where('created_by', $this->userId)
            ->whereHas('currentAssignment')
            ->whereDoesntHave('currentWorkAssignment')
            ->with(['currentAssignment.hotel', 'workAssignments' => function($q) {
                $q->latest('ended_at')->limit(1);
            }])
            ->get();

        foreach ($workers as $worker) {
            $checkInDate = $worker->currentAssignment->check_in_date;
            $lastWorkEnd = $worker->workAssignments->first()?->ended_at;
            
            $daysWithoutWork = 0;
            if ($lastWorkEnd) {
                $daysWithoutWork = now()->diffInDays($lastWorkEnd);
            } else {
                $daysWithoutWork = now()->diffInDays($checkInDate);
            }

            if ($daysWithoutWork >= $daysThreshold) {
                $notification = SystemNotification::create([
                    'type' => SystemNotification::TYPE_WORKER_UNEMPLOYED,
                    'title' => __('Работник без трудоустройства'),
                    'message' => __(':name проживает в отеле ":hotel" уже :days дней без работы', [
                        'name' => $worker->first_name . ' ' . $worker->last_name,
                        'hotel' => $worker->currentAssignment->hotel->name ?? 'N/A',
                        'days' => $daysWithoutWork
                    ]),
                    'data' => [
                        'worker_id' => $worker->id,
                        'hotel_id' => $worker->currentAssignment->hotel_id ?? null,
                        'days_without_work' => $daysWithoutWork,
                    ],
                    'link' => route('worker.show', $worker->id),
                    'created_by' => $this->userId,
                ]);
                $notifications[] = $notification;
            }
        }

        return $notifications;
    }

    public function getPollInterval(): int
    {
        return (int) $this->getSetting('notification_poll_interval', 1) * 60 * 1000;
    }

    public static function getSettings(): array
    {
        return DB::table('settings')
            ->where('created_by', 1)
            ->whereIn('name', [
                'notifications_enabled',
                'notification_poll_interval',
                'notification_create_interval',
                'notification_hotel_occupancy_threshold',
                'notification_unemployed_days'
            ])
            ->pluck('value', 'name')
            ->toArray();
    }
}
