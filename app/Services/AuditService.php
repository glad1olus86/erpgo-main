<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Универсальный метод для логирования события
     */
    public static function log($eventType, $description, $subject = null, $oldValues = null, $newValues = null)
    {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'event_type' => $eventType,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_by' => Auth::user() ? Auth::user()->creatorId() : 1,
        ]);
    }

    /**
     * Логирование создания работника
     */
    public static function logWorkerCreated($worker)
    {
        return self::log(
            'worker.created',
            "Создан работник: {$worker->first_name} {$worker->last_name}",
            $worker,
            null,
            [
                'name' => "{$worker->first_name} {$worker->last_name}",
                'dob' => $worker->dob,
                'gender' => $worker->gender,
                'nationality' => $worker->nationality,
            ]
        );
    }

    /**
     * Логирование обновления работника
     */
    public static function logWorkerUpdated($worker, $oldValues)
    {
        $changes = [];
        foreach ($oldValues as $key => $oldValue) {
            if ($worker->{$key} != $oldValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $worker->{$key}
                ];
            }
        }

        if (!empty($changes)) {
            return self::log(
                'worker.updated',
                "Обновлены данные работника: {$worker->first_name} {$worker->last_name}",
                $worker,
                $oldValues,
                $worker->only(array_keys($oldValues))
            );
        }
    }

    /**
     * Логирование удаления работника
     */
    public static function logWorkerDeleted($worker)
    {
        return self::log(
            'worker.deleted',
            "Удален работник: {$worker->first_name} {$worker->last_name}",
            $worker,
            [
                'name' => "{$worker->first_name} {$worker->last_name}",
                'dob' => $worker->dob,
            ],
            null
        );
    }

    /**
     * Логирование заселения работника
     */
    public static function logWorkerCheckedIn($assignment)
    {
        $worker = $assignment->worker;
        $room = $assignment->room;
        $hotel = $assignment->hotel;

        return self::log(
            'worker.checked_in',
            "Заселен: {$worker->first_name} {$worker->last_name} → Отель \"{$hotel->name}\", Комната {$room->room_number}",
            $worker,
            null,
            [
                'hotel' => $hotel->name,
                'room' => $room->room_number,
                'check_in_date' => $assignment->check_in_date,
            ]
        );
    }

    /**
     * Логирование выселения работника
     */
    public static function logWorkerCheckedOut($assignment)
    {
        $worker = $assignment->worker;
        $room = $assignment->room;
        $hotel = $assignment->hotel;

        return self::log(
            'worker.checked_out',
            "Выселен: {$worker->first_name} {$worker->last_name} ← Отель \"{$hotel->name}\", Комната {$room->room_number}",
            $worker,
            [
                'hotel' => $hotel->name,
                'room' => $room->room_number,
                'check_in_date' => $assignment->check_in_date,
            ],
            [
                'check_out_date' => $assignment->check_out_date,
            ]
        );
    }

    /**
     * Логирование устройства на работу
     */
    public static function logWorkerHired($workAssignment)
    {
        $worker = $workAssignment->worker;
        $workPlace = $workAssignment->workPlace;

        return self::log(
            'worker.hired',
            "Устроен на работу: {$worker->first_name} {$worker->last_name} → {$workPlace->name}",
            $worker,
            null,
            [
                'work_place' => $workPlace->name,
                'started_at' => $workAssignment->started_at,
            ]
        );
    }

    /**
     * Логирование увольнения
     */
    public static function logWorkerDismissed($workAssignment)
    {
        $worker = $workAssignment->worker;
        $workPlace = $workAssignment->workPlace;

        return self::log(
            'worker.dismissed',
            "Уволен: {$worker->first_name} {$worker->last_name} ← {$workPlace->name}",
            $worker,
            [
                'work_place' => $workPlace->name,
                'started_at' => $workAssignment->started_at,
            ],
            [
                'ended_at' => $workAssignment->ended_at,
            ]
        );
    }

    /**
     * Логирование создания комнаты
     */
    public static function logRoomCreated($room)
    {
        $hotel = $room->hotel;

        return self::log(
            'room.created',
            "Создана комната: {$room->room_number} в отеле \"{$hotel->name}\"",
            $room,
            null,
            [
                'room_number' => $room->room_number,
                'hotel' => $hotel->name,
                'capacity' => $room->capacity,
            ]
        );
    }

    /**
     * Логирование обновления комнаты
     */
    public static function logRoomUpdated($room, $oldValues)
    {
        $hotel = $room->hotel;

        return self::log(
            'room.updated',
            "Обновлена комната: {$room->room_number} в отеле \"{$hotel->name}\"",
            $room,
            $oldValues,
            $room->only(array_keys($oldValues))
        );
    }

    /**
     * Логирование удаления комнаты
     */
    public static function logRoomDeleted($room)
    {
        return self::log(
            'room.deleted',
            "Удалена комната: {$room->room_number}",
            $room,
            ['room_number' => $room->room_number, 'capacity' => $room->capacity],
            null
        );
    }

    /**
     * Логирование создания рабочего места
     */
    public static function logWorkPlaceCreated($workPlace)
    {
        return self::log(
            'work_place.created',
            "Создано рабочее место: {$workPlace->name}",
            $workPlace,
            null,
            [
                'name' => $workPlace->name,
                'address' => $workPlace->address,
            ]
        );
    }

    /**
     * Логирование обновления рабочего места
     */
    public static function logWorkPlaceUpdated($workPlace, $oldValues)
    {
        return self::log(
            'work_place.updated',
            "Обновлено рабочее место: {$workPlace->name}",
            $workPlace,
            $oldValues,
            $workPlace->only(array_keys($oldValues))
        );
    }

    /**
     * Логирование удаления рабочего места
     */
    public static function logWorkPlaceDeleted($workPlace)
    {
        return self::log(
            'work_place.deleted',
            "Удалено рабочее место: {$workPlace->name}",
            $workPlace,
            ['name' => $workPlace->name, 'address' => $workPlace->address],
            null
        );
    }

    /**
     * Логирование создания отеля
     */
    public static function logHotelCreated($hotel)
    {
        return self::log(
            'hotel.created',
            "Создан отель: {$hotel->name}",
            $hotel,
            null,
            [
                'name' => $hotel->name,
                'address' => $hotel->address,
            ]
        );
    }

    /**
     * Логирование обновления отеля
     */
    public static function logHotelUpdated($hotel, $oldValues)
    {
        return self::log(
            'hotel.updated',
            "Обновлен отель: {$hotel->name}",
            $hotel,
            $oldValues,
            $hotel->only(array_keys($oldValues))
        );
    }

    /**
     * Логирование удаления отеля
     */
    public static function logHotelDeleted($hotel)
    {
        return self::log(
            'hotel.deleted',
            "Удален отель: {$hotel->name}",
            $hotel,
            ['name' => $hotel->name, 'address' => $hotel->address],
            null
        );
    }
}
