<?php

namespace App\Services;

use App\Models\CashTransaction;
use App\Models\NotificationRule;
use App\Models\SystemNotification;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Support\Facades\Auth;

class CashboxNotificationService
{
    // Event types
    const EVENT_MONEY_RECEIVED = 'cashbox_money_received';
    const EVENT_MONEY_SENT = 'cashbox_money_sent';
    const EVENT_MONEY_REFUNDED = 'cashbox_money_refunded';
    const EVENT_TAKEN_TO_WORK = 'cashbox_taken_to_work';

    /**
     * Template variables available for cashbox notifications
     */
    public static function getTemplateVariables(): array
    {
        return [
            '{amount}' => __('Сумма транзакции'),
            '{sender_name}' => __('Имя отправителя'),
            '{recipient_name}' => __('Имя получателя'),
            '{comment}' => __('Комментарий'),
            '{task}' => __('Задача'),
        ];
    }

    /**
     * Notify about money received (distribution to recipient)
     * Requirement 12.2: cashbox_money_received event
     */
    public function notifyMoneyReceived(CashTransaction $transaction): void
    {
        $this->processEvent(self::EVENT_MONEY_RECEIVED, $transaction);
    }

    /**
     * Notify about money sent (distribution from sender)
     * Requirement 12.2: cashbox_money_sent event
     */
    public function notifyMoneySent(CashTransaction $transaction): void
    {
        $this->processEvent(self::EVENT_MONEY_SENT, $transaction);
    }

    /**
     * Notify about money refunded
     * Requirement 12.2: cashbox_money_refunded event
     */
    public function notifyMoneyRefunded(CashTransaction $transaction): void
    {
        $this->processEvent(self::EVENT_MONEY_REFUNDED, $transaction);
    }

    /**
     * Notify about transaction taken to work
     * Requirement 12.2: cashbox_taken_to_work event
     */
    public function notifyTakenToWork(CashTransaction $transaction): void
    {
        $this->processEvent(self::EVENT_TAKEN_TO_WORK, $transaction);
    }

    /**
     * Process event and create notifications based on rules
     */
    protected function processEvent(string $eventType, CashTransaction $transaction): void
    {
        $companyId = $transaction->created_by;

        // Get active rules for cashbox entity with this event
        $rules = NotificationRule::where('created_by', $companyId)
            ->where('is_active', true)
            ->where('entity_type', NotificationRule::ENTITY_CASHBOX)
            ->get();

        foreach ($rules as $rule) {
            if ($this->ruleMatchesEvent($rule, $eventType)) {
                $this->createNotification($rule, $transaction, $eventType);
            }
        }
    }

    /**
     * Check if rule matches the event type
     */
    protected function ruleMatchesEvent(NotificationRule $rule, string $eventType): bool
    {
        $conditions = $rule->conditions ?? [];
        
        foreach ($conditions as $condition) {
            $field = $condition['field'] ?? '';
            if ($field === $eventType) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create notification for the event
     * Requirement 12.3: Pass amount, sender, recipient, comment to notification
     */
    protected function createNotification(NotificationRule $rule, CashTransaction $transaction, string $eventType): void
    {
        $data = $this->buildNotificationData($transaction, $eventType);
        $message = $this->buildMessage($transaction, $eventType);
        $title = $this->buildTitle($rule, $eventType);
        $link = $this->buildLink($transaction);
        $targetUserId = $this->getTargetUserId($transaction, $eventType);

        // Only create notification if there's a valid target user
        if (!$targetUserId) {
            return;
        }

        SystemNotification::create([
            'type' => 'cashbox_' . $eventType,
            'title' => $title,
            'message' => $message,
            'severity' => $rule->severity,
            'data' => $data,
            'link' => $link,
            'created_by' => $targetUserId,
        ]);
    }

    /**
     * Build notification data array
     * Requirement 12.3: Include amount, sender, recipient, comment, task
     */
    protected function buildNotificationData(CashTransaction $transaction, string $eventType): array
    {
        return [
            'transaction_id' => $transaction->id,
            'event_type' => $eventType,
            'amount' => $transaction->amount,
            'sender_id' => $transaction->sender_id,
            'sender_type' => $transaction->sender_type,
            'sender_name' => $this->getParticipantName($transaction->sender_id, $transaction->sender_type),
            'recipient_id' => $transaction->recipient_id,
            'recipient_type' => $transaction->recipient_type,
            'recipient_name' => $this->getParticipantName($transaction->recipient_id, $transaction->recipient_type),
            'comment' => $transaction->comment,
            'task' => $transaction->task,
            'period_id' => $transaction->cash_period_id,
        ];
    }

    /**
     * Build notification message
     */
    protected function buildMessage(CashTransaction $transaction, string $eventType): string
    {
        $amount = formatCashboxCurrency($transaction->amount, $transaction->created_by);
        $senderName = $this->getParticipantName($transaction->sender_id, $transaction->sender_type);
        $recipientName = $this->getParticipantName($transaction->recipient_id, $transaction->recipient_type);

        $message = match($eventType) {
            self::EVENT_MONEY_RECEIVED => __('Вы получили :amount от :sender', [
                'amount' => $amount,
                'sender' => $senderName,
            ]),
            self::EVENT_MONEY_SENT => __('Вы выдали :amount для :recipient', [
                'amount' => $amount,
                'recipient' => $recipientName,
            ]),
            self::EVENT_MONEY_REFUNDED => __('Возврат :amount от :sender', [
                'amount' => $amount,
                'sender' => $senderName,
            ]),
            self::EVENT_TAKEN_TO_WORK => __(':recipient взял в работу :amount', [
                'amount' => $amount,
                'recipient' => $recipientName,
            ]),
            default => '',
        };

        // Add task if present
        if ($transaction->task) {
            $message .= ' | ' . __('Задача') . ': ' . $transaction->task;
        }

        // Add comment if present
        if ($transaction->comment) {
            $message .= ' | ' . $transaction->comment;
        }

        return $message;
    }

    /**
     * Build notification title
     */
    protected function buildTitle(NotificationRule $rule, string $eventType): string
    {
        if ($rule->name) {
            return $rule->name;
        }

        return match($eventType) {
            self::EVENT_MONEY_RECEIVED => __('Касса: Получение денег'),
            self::EVENT_MONEY_SENT => __('Касса: Выдача денег'),
            self::EVENT_MONEY_REFUNDED => __('Касса: Возврат денег'),
            self::EVENT_TAKEN_TO_WORK => __('Касса: Взято в работу'),
            default => __('Касса'),
        };
    }

    /**
     * Build link to cashbox period
     */
    protected function buildLink(CashTransaction $transaction): string
    {
        return route('cashbox.show', $transaction->cash_period_id);
    }

    /**
     * Get target user ID for notification based on event type
     */
    protected function getTargetUserId(CashTransaction $transaction, string $eventType): ?int
    {
        return match($eventType) {
            // Money received - notify recipient (if User)
            self::EVENT_MONEY_RECEIVED => $transaction->recipient_type === User::class 
                ? $transaction->recipient_id 
                : null,
            // Money sent - notify sender
            self::EVENT_MONEY_SENT => $transaction->sender_id,
            // Money refunded - notify the one who receives the refund (original sender)
            self::EVENT_MONEY_REFUNDED => $transaction->recipient_id,
            // Taken to work - notify sender about recipient's action
            self::EVENT_TAKEN_TO_WORK => $transaction->sender_id,
            default => null,
        };
    }

    /**
     * Get participant name
     */
    protected function getParticipantName(?int $id, ?string $type): string
    {
        if (!$id || !$type) {
            return __('Система');
        }

        if ($type === User::class) {
            $user = User::find($id);
            return $user ? $user->name : __('Неизвестный');
        }

        if ($type === Worker::class) {
            $worker = Worker::find($id);
            return $worker ? ($worker->first_name . ' ' . $worker->last_name) : __('Неизвестный');
        }

        return __('Неизвестный');
    }
}
