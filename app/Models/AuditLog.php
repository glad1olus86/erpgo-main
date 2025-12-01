<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_type',
        'description',
        'subject_type',
        'subject_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_by',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */

    // Пользователь, который совершил действие
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Пользователь-создатель (для multi-tenancy)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Polymorphic relationship для объекта действия
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Scopes
     */

    // Фильтр по диапазону дат
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ]);
    }

    // Фильтр по пользователю
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Фильтр по типу события
    public function scopeByEventType($query, $type)
    {
        if (is_array($type)) {
            return $query->whereIn('event_type', $type);
        }
        return $query->where('event_type', $type);
    }

    // Фильтр по типу объекта
    public function scopeBySubjectType($query, $type)
    {
        return $query->where('subject_type', $type);
    }

    // События за сегодня
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    // События за текущий месяц
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year);
    }

    // События за последние N дней
    public function scopeLastDays($query, $days = 7)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    // Для текущего создателя (multi-tenancy)
    public function scopeForCurrentUser($query)
    {
        /** @var \App\Models\User $user */
        $user = \Auth::user();
        return $query->where('created_by', $user->creatorId());
    }

    /**
     * Accessors
     */

    // Получить цвет для типа события
    public function getEventColorAttribute()
    {
        $colors = [
            // Работники
            'worker.created' => '#28a745',      // Зеленый
            'worker.updated' => '#17a2b8',      // Голубой
            'worker.deleted' => '#6c757d',      // Серый

            // Проживание
            'worker.checked_in' => '#007bff',   // Синий
            'worker.checked_out' => '#fd7e14',  // Оранжевый

            // Трудоустройство
            'worker.hired' => '#6f42c1',        // Фиолетовый
            'worker.dismissed' => '#dc3545',    // Красный

            // Комнаты
            'room.created' => '#20c997',        // Бирюзовый
            'room.updated' => '#17a2b8',        // Голубой
            'room.deleted' => '#6c757d',        // Серый

            // Рабочие места
            'work_place.created' => '#20c997',  // Бирюзовый
            'work_place.updated' => '#17a2b8',  // Голубой
            'work_place.deleted' => '#6c757d',  // Серый

            // Отели
            'hotel.created' => '#28a745',       // Зеленый
            'hotel.updated' => '#17a2b8',       // Голубой
            'hotel.deleted' => '#6c757d',       // Серый

            // Касса
            'cashbox.deposit' => '#28a745',         // Зеленый - внесение
            'cashbox.distribution' => '#007bff',    // Синий - выдача
            'cashbox.refund' => '#fd7e14',          // Оранжевый - возврат
            'cashbox.self_salary' => '#6f42c1',     // Фиолетовый - ЗП себе
            'cashbox.status_change' => '#17a2b8',   // Голубой - смена статуса
        ];

        return $colors[$this->event_type] ?? '#6c757d';
    }

    // Получить иконку для типа события
    public function getEventIconAttribute()
    {
        $icons = [
            // Работники
            'worker.created' => 'ti-user-plus',
            'worker.updated' => 'ti-user-edit',
            'worker.deleted' => 'ti-user-x',

            // Проживание
            'worker.checked_in' => 'ti-door-enter',
            'worker.checked_out' => 'ti-door-exit',

            // Трудоустройство
            'worker.hired' => 'ti-briefcase-plus',
            'worker.dismissed' => 'ti-briefcase-off',

            // Комнаты
            'room.created' => 'ti-door-plus',
            'room.updated' => 'ti-door-edit',
            'room.deleted' => 'ti-door-x',

            // Рабочие места
            'work_place.created' => 'ti-building-plus',
            'work_place.updated' => 'ti-building-edit',
            'work_place.deleted' => 'ti-building-x',

            // Отели
            'hotel.created' => 'ti-building-skyscraper',
            'hotel.updated' => 'ti-building-edit',
            'hotel.deleted' => 'ti-building-x',

            // Касса
            'cashbox.deposit' => 'ti-cash',
            'cashbox.distribution' => 'ti-send',
            'cashbox.refund' => 'ti-arrow-back',
            'cashbox.self_salary' => 'ti-wallet',
            'cashbox.status_change' => 'ti-refresh',
        ];

        return $icons[$this->event_type] ?? 'ti-info-circle';
    }

    // Форматированное описание события
    public function getFormattedDescriptionAttribute()
    {
        return $this->description;
    }

    // Получить имя пользователя
    public function getUserNameAttribute()
    {
        return $this->user ? $this->user->name : __('Система');
    }

    /**
     * Helper методы
     */

    // Создать лог события
    public static function logEvent($eventType, $description, $subject = null, $oldValues = null, $newValues = null)
    {
        return self::create([
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
}
