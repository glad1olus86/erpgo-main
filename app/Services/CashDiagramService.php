<?php

namespace App\Services;

use App\Models\CashPeriod;
use App\Models\CashTransaction;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Support\Collection;

class CashDiagramService
{
    protected CashHierarchyService $hierarchyService;

    public function __construct(CashHierarchyService $hierarchyService)
    {
        $this->hierarchyService = $hierarchyService;
    }

    /**
     * Build tree structure for diagram visualization
     * Requirement 9.1: Display diagram as tree with participant nodes
     * Requirement 9.2: Show icon, name, amount, task, comment, status on each node
     * Requirement 9.3: Connect nodes with lines showing money flow direction
     *
     * @param CashPeriod $period
     * @return array
     */
    public function buildTree(CashPeriod $period): array
    {
        // Get all transactions for this period
        $transactions = CashTransaction::where('cash_period_id', $period->id)
            ->orderBy('created_at', 'asc')
            ->get();

        // Start with deposit transactions (root nodes)
        $deposits = $transactions->where('type', CashTransaction::TYPE_DEPOSIT);
        
        $tree = [];
        
        foreach ($deposits as $deposit) {
            $tree[] = $this->buildNodeWithChildren($deposit, $transactions);
        }

        return [
            'period' => [
                'id' => $period->id,
                'name' => $period->name,
                'year' => $period->year,
                'month' => $period->month,
                'total_deposited' => (float) $period->total_deposited,
                'is_frozen' => $period->is_frozen,
            ],
            'nodes' => $tree,
        ];
    }

    /**
     * Build a node with its children recursively
     *
     * @param CashTransaction $transaction
     * @param Collection $allTransactions
     * @return array
     */
    protected function buildNodeWithChildren(CashTransaction $transaction, Collection $allTransactions): array
    {
        $node = $this->formatNode($transaction);
        
        // Salary distributions are leaf nodes - no children should be built from them
        // Money paid as salary leaves the business, it's not available for further distribution
        if ($transaction->distribution_type === CashTransaction::DISTRIBUTION_TYPE_SALARY) {
            return $node;
        }

        // Find child transactions (where this transaction's recipient is the sender)
        $children = $allTransactions->filter(function ($t) use ($transaction) {
            // Skip deposits (they are root nodes)
            if ($t->type === CashTransaction::TYPE_DEPOSIT) {
                return false;
            }
            
            // Skip refunds (they go back up the tree)
            if ($t->type === CashTransaction::TYPE_REFUND) {
                return false;
            }
            
            // Skip self - prevent infinite loop (self_salary has same sender and recipient)
            if ($t->id === $transaction->id) {
                return false;
            }
            
            // Skip self_salary transactions as children - they are leaf nodes
            if ($t->type === CashTransaction::TYPE_SELF_SALARY) {
                return false;
            }
            
            // Match sender to recipient of parent transaction
            return $t->sender_id === $transaction->recipient_id &&
                   $t->sender_type === $transaction->recipient_type;
        });

        foreach ($children as $child) {
            $node['children'][] = $this->buildNodeWithChildren($child, $allTransactions);
        }
        
        // Add self_salary transactions as special children (leaf nodes)
        $selfSalaries = $allTransactions->filter(function ($t) use ($transaction) {
            return $t->type === CashTransaction::TYPE_SELF_SALARY &&
                   $t->sender_id === $transaction->recipient_id &&
                   $t->sender_type === $transaction->recipient_type &&
                   $t->id !== $transaction->id;
        });

        foreach ($selfSalaries as $selfSalary) {
            $node['children'][] = $this->formatNode($selfSalary);
        }

        // Add refunds as special children
        $refunds = $allTransactions->filter(function ($t) use ($transaction) {
            return $t->type === CashTransaction::TYPE_REFUND &&
                   $t->parent_transaction_id === $transaction->id;
        });

        foreach ($refunds as $refund) {
            $node['refunds'][] = $this->formatNode($refund);
        }

        return $node;
    }

    /**
     * Format a transaction as a node for the diagram
     * Requirement 9.2: Show icon, name, amount, task, comment, status
     *
     * @param CashTransaction $transaction
     * @return array
     */
    public function formatNode(CashTransaction $transaction): array
    {
        $sender = $this->formatParticipant(
            $transaction->sender_id,
            $transaction->sender_type
        );
        
        $recipient = $this->formatParticipant(
            $transaction->recipient_id,
            $transaction->recipient_type
        );

        return [
            'id' => $transaction->id,
            'type' => $transaction->type,
            'distribution_type' => $transaction->distribution_type,
            'distribution_type_label' => $this->getDistributionTypeLabel($transaction->distribution_type),
            'sender' => $sender,
            'recipient' => $recipient,
            'amount' => (float) $transaction->amount,
            'task' => $transaction->task,
            'comment' => $transaction->comment,
            'status' => $transaction->status,
            'status_label' => $this->getStatusLabel($transaction->status),
            'created_at' => $transaction->created_at->toIso8601String(),
            'children' => [],
            'refunds' => [],
        ];
    }

    /**
     * Format a participant (sender or recipient) for display
     *
     * @param int|null $id
     * @param string|null $type
     * @return array|null
     */
    protected function formatParticipant(?int $id, ?string $type): ?array
    {
        if (!$id || !$type) {
            return null;
        }

        if ($type === User::class) {
            $user = User::find($id);
            if (!$user) {
                return null;
            }

            $role = $this->hierarchyService->getUserCashboxRole($user);

            return [
                'id' => $user->id,
                'type' => 'user',
                'name' => $user->name,
                'role' => $role,
                'role_label' => $this->getRoleLabel($role),
                'icon' => $this->getRoleIcon($role),
                'avatar' => $user->profile,
            ];
        }

        if ($type === Worker::class) {
            $worker = Worker::find($id);
            if (!$worker) {
                return null;
            }

            return [
                'id' => $worker->id,
                'type' => 'worker',
                'name' => $worker->first_name . ' ' . $worker->last_name,
                'role' => CashHierarchyService::ROLE_WORKER,
                'role_label' => $this->getRoleLabel(CashHierarchyService::ROLE_WORKER),
                'icon' => $this->getRoleIcon(CashHierarchyService::ROLE_WORKER),
                'avatar' => null,
            ];
        }

        return null;
    }

    /**
     * Get human-readable label for role
     *
     * @param string|null $role
     * @return string
     */
    protected function getRoleLabel(?string $role): string
    {
        return match ($role) {
            CashHierarchyService::ROLE_BOSS => 'Директор',
            CashHierarchyService::ROLE_MANAGER => 'Менеджер',
            CashHierarchyService::ROLE_CURATOR => 'Куратор',
            CashHierarchyService::ROLE_WORKER => 'Работник',
            default => 'Неизвестно',
        };
    }

    /**
     * Get icon class for role
     *
     * @param string|null $role
     * @return string
     */
    protected function getRoleIcon(?string $role): string
    {
        return match ($role) {
            CashHierarchyService::ROLE_BOSS => 'ti ti-crown',
            CashHierarchyService::ROLE_MANAGER => 'ti ti-user-star',
            CashHierarchyService::ROLE_CURATOR => 'ti ti-user-check',
            CashHierarchyService::ROLE_WORKER => 'ti ti-user',
            default => 'ti ti-user',
        };
    }

    /**
     * Get human-readable label for status
     *
     * @param string $status
     * @return string
     */
    protected function getStatusLabel(string $status): string
    {
        return match ($status) {
            CashTransaction::STATUS_PENDING => 'Ожидает',
            CashTransaction::STATUS_IN_PROGRESS => 'В работе',
            CashTransaction::STATUS_COMPLETED => 'Выполнено',
            CashTransaction::STATUS_OVERDUE => 'Просрочено',
            default => 'Неизвестно',
        };
    }

    /**
     * Get human-readable label for distribution type
     *
     * @param string|null $distributionType
     * @return string|null
     */
    protected function getDistributionTypeLabel(?string $distributionType): ?string
    {
        return match ($distributionType) {
            CashTransaction::DISTRIBUTION_TYPE_SALARY => 'ЗП',
            CashTransaction::DISTRIBUTION_TYPE_TRANSFER => 'Передача',
            default => null,
        };
    }

    /**
     * Get flat list of all transactions for a period (alternative view)
     *
     * @param CashPeriod $period
     * @return Collection
     */
    public function getTransactionsList(CashPeriod $period): Collection
    {
        return CashTransaction::where('cash_period_id', $period->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($transaction) {
                return $this->formatNode($transaction);
            });
    }

    /**
     * Get summary statistics for a period
     *
     * @param CashPeriod $period
     * @return array
     */
    public function getPeriodSummary(CashPeriod $period): array
    {
        $transactions = CashTransaction::where('cash_period_id', $period->id)->get();

        $totalDeposited = $transactions
            ->where('type', CashTransaction::TYPE_DEPOSIT)
            ->sum('amount');

        $totalDistributed = $transactions
            ->where('type', CashTransaction::TYPE_DISTRIBUTION)
            ->sum('amount');

        $totalRefunded = $transactions
            ->where('type', CashTransaction::TYPE_REFUND)
            ->sum('amount');

        $totalSelfSalary = $transactions
            ->where('type', CashTransaction::TYPE_SELF_SALARY)
            ->sum('amount');

        return [
            'total_deposited' => (float) $totalDeposited,
            'total_distributed' => (float) $totalDistributed,
            'total_refunded' => (float) $totalRefunded,
            'total_self_salary' => (float) $totalSelfSalary,
            'transactions_count' => $transactions->count(),
            'pending_count' => $transactions->where('status', CashTransaction::STATUS_PENDING)->count(),
            'in_progress_count' => $transactions->where('status', CashTransaction::STATUS_IN_PROGRESS)->count(),
            'completed_count' => $transactions->where('status', CashTransaction::STATUS_COMPLETED)->count(),
        ];
    }
}
