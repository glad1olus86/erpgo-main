<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class NotificationRule extends Model
{
    protected $fillable = [
        'name',
        'entity_type',
        'conditions',
        'period_from',
        'period_to',
        'severity',
        'is_active',
        'is_grouped',
        'created_by',
    ];

    protected $casts = [
        'conditions' => 'array',
        'is_active' => 'boolean',
        'is_grouped' => 'boolean',
        'period_from' => 'integer',
        'period_to' => 'integer',
    ];

    // Entity types
    const ENTITY_WORKER = 'worker';
    const ENTITY_ROOM = 'room';
    const ENTITY_HOTEL = 'hotel';
    const ENTITY_WORK_PLACE = 'work_place';

    // Severity levels
    const SEVERITY_INFO = 'info';
    const SEVERITY_WARNING = 'warning';
    const SEVERITY_DANGER = 'danger';

    /**
     * Get available entity types
     */
    public static function getEntityTypes(): array
    {
        return [
            self::ENTITY_WORKER => __('Работник'),
            self::ENTITY_ROOM => __('Комната'),
            self::ENTITY_HOTEL => __('Отель'),
            self::ENTITY_WORK_PLACE => __('Рабочее место'),
        ];
    }

    /**
     * Get severity levels
     */
    public static function getSeverityLevels(): array
    {
        return [
            self::SEVERITY_INFO => ['label' => __('Штатное'), 'color' => 'info', 'icon' => 'ti-info-circle'],
            self::SEVERITY_WARNING => ['label' => __('Почти критичное'), 'color' => 'warning', 'icon' => 'ti-alert-triangle'],
            self::SEVERITY_DANGER => ['label' => __('Критическое'), 'color' => 'danger', 'icon' => 'ti-alert-circle'],
        ];
    }

    /**
     * Get conditions for entity type
     */
    public static function getConditionsForEntity(string $entityType): array
    {
        return match($entityType) {
            self::ENTITY_WORKER => [
                'is_employed' => ['label' => __('Трудоустроен'), 'type' => 'boolean'],
                'not_employed' => ['label' => __('Не трудоустроен'), 'type' => 'boolean'],
                'is_housed' => ['label' => __('Проживает в отеле'), 'type' => 'boolean'],
                'not_housed' => ['label' => __('Не проживает в отеле'), 'type' => 'boolean'],
                'no_assignment' => ['label' => __('Без назначения (ни работы, ни жилья)'), 'type' => 'boolean'],
            ],
            self::ENTITY_ROOM => [
                'is_full' => ['label' => __('Полностью заполнена'), 'type' => 'boolean'],
                'is_empty' => ['label' => __('Пустая'), 'type' => 'boolean'],
                'is_partial' => ['label' => __('Частично заполнена'), 'type' => 'boolean'],
                'occupancy_above' => ['label' => __('Заполненность выше %'), 'type' => 'number', 'suffix' => '%'],
                'occupancy_below' => ['label' => __('Заполненность ниже %'), 'type' => 'number', 'suffix' => '%'],
            ],
            self::ENTITY_HOTEL => [
                'occupancy_above' => ['label' => __('Заполненность выше %'), 'type' => 'number', 'suffix' => '%'],
                'occupancy_below' => ['label' => __('Заполненность ниже %'), 'type' => 'number', 'suffix' => '%'],
                'has_empty_rooms' => ['label' => __('Есть пустые комнаты'), 'type' => 'boolean'],
                'no_empty_rooms' => ['label' => __('Нет пустых комнат'), 'type' => 'boolean'],
            ],
            self::ENTITY_WORK_PLACE => [
                'has_no_workers' => ['label' => __('Нет сотрудников'), 'type' => 'boolean'],
                'workers_below' => ['label' => __('Сотрудников меньше'), 'type' => 'number'],
                'workers_above' => ['label' => __('Сотрудников больше'), 'type' => 'number'],
            ],
            default => [],
        };
    }

    /**
     * Scope for current user
     */
    public function scopeForCurrentUser($query)
    {
        return $query->where('created_by', Auth::user()->creatorId());
    }

    /**
     * Scope for active rules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get period display text
     */
    public function getPeriodTextAttribute(): string
    {
        if ($this->period_from == 0 && !$this->period_to) {
            return __('Сразу');
        }
        
        if ($this->period_to) {
            return $this->period_from . '-' . $this->period_to . ' ' . __('дней');
        }
        
        return __('от') . ' ' . $this->period_from . ' ' . __('дней');
    }

    /**
     * Get severity info
     */
    public function getSeverityInfoAttribute(): array
    {
        return self::getSeverityLevels()[$this->severity] ?? self::getSeverityLevels()[self::SEVERITY_INFO];
    }

    /**
     * Check if days count matches this rule's period
     */
    public function matchesPeriod(int $days): bool
    {
        if ($days < $this->period_from) {
            return false;
        }
        
        if ($this->period_to !== null && $days > $this->period_to) {
            return false;
        }
        
        return true;
    }
}
