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
    
    /**
     * Minimum number of worker salary transactions to group into a list
     * Change this value to adjust when grouping starts (e.g., 5, 10, 50)
     */
    const SALARY_LIST_MIN_COUNT = 5;
    
    /**
     * Maximum number of worker salary transactions per list
     * Change this value to adjust list size (e.g., 20, 50, 100)
     */
    const SALARY_LIST_MAX_COUNT = 20;

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

        // Start with deposit transactions (root nodes), sorted by time
        $deposits = $transactions->where('type', CashTransaction::TYPE_DEPOSIT)->values();
        
        $tree = [];
        
        // Track which transactions have been used to avoid duplicates
        $usedTransactionIds = collect();
        
        // Track carryover from previous deposits
        $carryoverFromPrevious = 0;
        
        // Global counter for salary lists in this period
        $salaryListCounter = 1;
        
        // Process each deposit with its time-bounded transactions
        foreach ($deposits as $index => $deposit) {
            $usedTransactionIds->push($deposit->id);
            
            // Get the time boundary for this deposit
            // Transactions belong to this deposit if created AFTER this deposit and BEFORE next deposit
            $depositTime = $deposit->created_at;
            $nextDeposit = $deposits->get($index + 1);
            $nextDepositTime = $nextDeposit ? $nextDeposit->created_at : null;
            
            // Filter transactions that belong to this deposit's time window
            $depositTransactions = $transactions->filter(function ($t) use ($depositTime, $nextDepositTime) {
                // Skip deposits themselves
                if ($t->type === CashTransaction::TYPE_DEPOSIT) {
                    return false;
                }
                
                // Transaction must be created after this deposit
                if ($t->created_at < $depositTime) {
                    return false;
                }
                
                // If there's a next deposit, transaction must be before it
                if ($nextDepositTime && $t->created_at >= $nextDepositTime) {
                    return false;
                }
                
                return true;
            });
            
            // Track carryover by recipient for child transactions
            $recipientCarryovers = [];
            $node = $this->buildNodeWithChildren($deposit, $depositTransactions, $usedTransactionIds, $recipientCarryovers, $salaryListCounter);
            
            // Calculate spent amount for this deposit's transactions
            $spentAmount = $depositTransactions->whereIn('type', [
                CashTransaction::TYPE_DISTRIBUTION,
                CashTransaction::TYPE_SELF_SALARY,
            ])->where('sender_id', $deposit->recipient_id)
              ->where('sender_type', $deposit->recipient_type)
              ->sum('amount');
            
            // Calculate refunds received back
            $refundsReceived = $depositTransactions->where('type', CashTransaction::TYPE_REFUND)
                ->where('recipient_id', $deposit->recipient_id)
                ->where('recipient_type', $deposit->recipient_type)
                ->sum('amount');
            
            // Total available = deposit amount + carryover from previous
            $totalAvailable = $deposit->amount + $carryoverFromPrevious;
            
            // Calculate remaining balance for this deposit period
            // Available = total available + refunds - spent
            $availableForThisDeposit = $totalAvailable + $refundsReceived - $spentAmount;
            
            // Add carryover info from previous deposit and update amount
            if ($carryoverFromPrevious > 0) {
                $node['carryover_received'] = (float) $carryoverFromPrevious;
                // Update amount to show total (original deposit + carryover)
                $node['amount'] = (float) $totalAvailable;
            }
            
            // If there's a next deposit and remaining balance, carryover goes there
            if ($nextDeposit && $availableForThisDeposit > 0) {
                $node['carryover_to_next'] = (float) $availableForThisDeposit;
                // Money moved to next deposit - show 0 on this one
                $node['current_balance'] = 0;
                $carryoverFromPrevious = $availableForThisDeposit;
            } else {
                // No next deposit - show actual remaining balance
                $node['current_balance'] = max(0, (float) $availableForThisDeposit);
                $carryoverFromPrevious = 0;
            }
            
            $tree[] = $node;
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
     * @param Collection $usedTransactionIds - track used transactions to avoid duplicates
     * @param array $recipientCarryovers - track carryover amounts by recipient
     * @param int $salaryListCounter - global counter for salary lists in this period
     * @return array
     */
    protected function buildNodeWithChildren(CashTransaction $transaction, Collection $allTransactions, Collection &$usedTransactionIds, array &$recipientCarryovers = [], int &$salaryListCounter = 1): array
    {
        // Get recipient key for carryover tracking
        $recipientKey = $transaction->recipient_type . '_' . $transaction->recipient_id;
        
        // Salary distributions are leaf nodes - no children should be built from them
        // Money paid as salary leaves the business, it's not available for further distribution
        // For salary, show full amount (it's the final destination, money is given to recipient)
        if ($transaction->distribution_type === CashTransaction::DISTRIBUTION_TYPE_SALARY) {
            $node = $this->formatNode($transaction, (float) $transaction->amount);
            // Reset carryover for this recipient after salary (money is spent from sender's perspective)
            $recipientCarryovers[$recipientKey] = 0;
            return $node;
        }

        // Find child transactions (where this transaction's recipient is the sender)
        // Only include transactions that haven't been used yet
        $children = $allTransactions->filter(function ($t) use ($transaction, $usedTransactionIds) {
            // Skip already used transactions
            if ($usedTransactionIds->contains($t->id)) {
                return false;
            }
            
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

        // Calculate how much this recipient spent from this transaction
        $spentFromThis = 0;
        $childNodes = [];
        $workerSalaryNodes = []; // Collect worker salary nodes for potential grouping
        
        foreach ($children as $child) {
            $usedTransactionIds->push($child->id);
            $spentFromThis += $child->amount;
            
            $childNode = $this->buildNodeWithChildren($child, $allTransactions, $usedTransactionIds, $recipientCarryovers, $salaryListCounter);
            
            // Check if this is a salary to a worker - collect for grouping
            if ($child->distribution_type === CashTransaction::DISTRIBUTION_TYPE_SALARY && 
                $child->recipient_type === Worker::class) {
                $workerSalaryNodes[] = $childNode;
            } else {
                $childNodes[] = $childNode;
            }
        }
        
        // Group worker salary nodes into lists if there are enough
        $childNodes = array_merge($childNodes, $this->groupWorkerSalaries($workerSalaryNodes, $salaryListCounter));
        
        // Add self_salary transactions as special children (leaf nodes)
        // Only include transactions that haven't been used yet
        $selfSalaries = $allTransactions->filter(function ($t) use ($transaction, $usedTransactionIds) {
            if ($usedTransactionIds->contains($t->id)) {
                return false;
            }
            
            return $t->type === CashTransaction::TYPE_SELF_SALARY &&
                   $t->sender_id === $transaction->recipient_id &&
                   $t->sender_type === $transaction->recipient_type &&
                   $t->id !== $transaction->id;
        });

        foreach ($selfSalaries as $selfSalary) {
            $usedTransactionIds->push($selfSalary->id);
            $spentFromThis += $selfSalary->amount;
            // Self salary - show full amount (it's the final destination, money is taken as salary)
            $childNodes[] = $this->formatNode($selfSalary, (float) $selfSalary->amount);
        }

        // Add refunds as special children
        $refundsReceived = 0;
        $refundNodes = [];
        $refunds = $allTransactions->filter(function ($t) use ($transaction, $usedTransactionIds) {
            if ($usedTransactionIds->contains($t->id)) {
                return false;
            }
            
            return $t->type === CashTransaction::TYPE_REFUND &&
                   $t->parent_transaction_id === $transaction->id;
        });

        foreach ($refunds as $refund) {
            $usedTransactionIds->push($refund->id);
            $refundsReceived += $refund->amount;
            $refundNodes[] = $this->formatNode($refund);
        }
        
        // Calculate remaining balance for this recipient
        // Available = received amount + previous carryover + refunds - spent
        $previousCarryover = $recipientCarryovers[$recipientKey] ?? 0;
        
        // Total amount available = transaction amount + carryover from previous
        $totalAvailable = $transaction->amount + $previousCarryover;
        $remaining = $totalAvailable + $refundsReceived - $spentFromThis;
        
        // Create node - we'll set current_balance after determining carryover
        $node = $this->formatNode($transaction, max(0, $remaining));
        $node['children'] = $childNodes;
        $node['refunds'] = $refundNodes;
        
        // If there's carryover received, update the displayed amount to include it
        if ($previousCarryover > 0) {
            $node['carryover_received'] = (float) $previousCarryover;
            // Update amount to show total (original + carryover)
            $node['amount'] = (float) $totalAvailable;
        }
        
        // Store carryover for next transaction to this recipient
        // Always show actual remaining balance - carryover_to_next is just for the arrow
        $node['current_balance'] = max(0, $remaining);
        
        if ($remaining > 0) {
            $recipientCarryovers[$recipientKey] = $remaining;
            $node['carryover_to_next'] = (float) $remaining;
        } else {
            $recipientCarryovers[$recipientKey] = 0;
        }

        return $node;
    }

    /**
     * Format a transaction as a node for the diagram
     * Requirement 9.2: Show icon, name, amount, task, comment, status
     *
     * @param CashTransaction $transaction
     * @param float|null $currentBalance - актуальный остаток (если null, равен amount)
     * @return array
     */
    public function formatNode(CashTransaction $transaction, ?float $currentBalance = null): array
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
            'original_amount' => (float) $transaction->amount,
            'current_balance' => $currentBalance ?? (float) $transaction->amount,
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
    
    /**
     * Group worker salary nodes into lists if there are enough
     * 
     * @param array $workerSalaryNodes
     * @param int &$salaryListCounter - global counter for unique list numbers in period
     * @return array
     */
    protected function groupWorkerSalaries(array $workerSalaryNodes, int &$salaryListCounter): array
    {
        $count = count($workerSalaryNodes);
        
        // If less than minimum, return as individual nodes
        if ($count < self::SALARY_LIST_MIN_COUNT) {
            return $workerSalaryNodes;
        }
        
        $result = [];
        
        // Split into chunks of max size
        $chunks = array_chunk($workerSalaryNodes, self::SALARY_LIST_MAX_COUNT);
        
        foreach ($chunks as $chunk) {
            // Calculate total amount for this list
            $totalAmount = 0;
            $recipients = [];
            $transactionIds = [];
            
            foreach ($chunk as $node) {
                $totalAmount += $node['original_amount'] ?? $node['amount'];
                $transactionIds[] = $node['id'];
                $recipients[] = [
                    'name' => $node['recipient']['name'] ?? 'Неизвестно',
                    'amount' => $node['original_amount'] ?? $node['amount'],
                    'id' => $node['recipient']['id'] ?? null,
                ];
            }
            
            // Use global counter for unique list number
            $listNumber = $salaryListCounter;
            
            // Create a grouped list node
            $result[] = [
                'id' => 'salary_list_' . $listNumber,
                'type' => 'salary_list',
                'distribution_type' => 'salary',
                'distribution_type_label' => 'Список ЗП',
                'sender' => null,
                'recipient' => [
                    'id' => null,
                    'type' => 'list',
                    'name' => 'Список ЗП №' . $listNumber,
                    'role' => 'worker',
                    'role_label' => 'Работники',
                    'icon' => 'ti ti-users',
                ],
                'amount' => (float) $totalAmount,
                'original_amount' => (float) $totalAmount,
                'current_balance' => (float) $totalAmount,
                'task' => count($chunk) . ' ' . $this->pluralize(count($chunk), 'работник', 'работника', 'работников'),
                'comment' => null,
                'status' => 'completed',
                'status_label' => 'Выполнено',
                'created_at' => $chunk[0]['created_at'] ?? now()->toIso8601String(),
                'children' => [],
                'refunds' => [],
                'is_salary_list' => true,
                'salary_list_number' => $listNumber,
                'salary_recipients' => $recipients,
                'transaction_ids' => $transactionIds,
            ];
            
            // Increment global counter for next list
            $salaryListCounter++;
        }
        
        return $result;
    }
    
    /**
     * Pluralize Russian word
     */
    protected function pluralize(int $count, string $one, string $few, string $many): string
    {
        $mod10 = $count % 10;
        $mod100 = $count % 100;
        
        if ($mod100 >= 11 && $mod100 <= 19) {
            return $many;
        }
        
        if ($mod10 === 1) {
            return $one;
        }
        
        if ($mod10 >= 2 && $mod10 <= 4) {
            return $few;
        }
        
        return $many;
    }
}
