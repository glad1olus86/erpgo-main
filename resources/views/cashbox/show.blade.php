@extends('layouts.admin')

@section('page-title')
    {{ __('Касса') }} - {{ $period->name }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('cashbox.index') }}">{{ __('Касса') }}</a></li>
    <li class="breadcrumb-item">{{ $period->name }}</li>
@endsection

@section('action-btn')
    <div class="float-end d-flex gap-2">
        @if ($balance['received'] > 0)
            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#distributeModal">
                <i class="ti ti-send"></i> {{ __('Выдать') }}
            </button>
        @endif

        @if ($balance['received'] > $balance['sent'] && $userRole !== 'boss')
            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#refundModal">
                <i class="ti ti-arrow-back"></i> {{ __('Вернуть') }}
            </button>
        @endif

        @if ($canSelfSalary && !$hasSelfSalaryThisPeriod && $balance['received'] > $balance['sent'])
            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#selfSalaryModal">
                <i class="ti ti-wallet"></i> {{ __('ЗП себе') }}
            </button>
        @endif

        @if ($isBoss)
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#depositModal">
                <i class="ti ti-plus"></i> {{ __('Внести') }}
            </button>
        @endif
    </div>
@endsection

@push('css-page')
    <style>
        .budget-card {
            position: sticky;
            top: 80px;
        }

        .diagram-container {
            min-height: 500px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            overflow: auto;
            position: relative;
        }

        .diagram-container::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .diagram-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .diagram-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .diagram-container::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Status filter buttons */
        .status-filter-btn {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            border: 1px solid #dee2e6;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
        }

        .status-filter-btn:hover {
            background: #f8f9fa;
        }

        .status-filter-btn.active {
            background: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }

        .status-filter-btn.filter-pending.active {
            background: #ffc107;
            border-color: #ffc107;
            color: #000;
        }

        .status-filter-btn.filter-in_progress.active {
            background: #0dcaf0;
            border-color: #0dcaf0;
        }

        .status-filter-btn.filter-completed.active {
            background: #198754;
            border-color: #198754;
        }

        /* Transaction detail modal enhancements */
        #transactionDetailContent .mb-3 {
            padding-bottom: 8px;
            border-bottom: 1px solid #f0f0f0;
        }

        #transactionDetailContent .mb-3:last-child {
            border-bottom: none;
        }

        /* Legacy styles for fallback */
        .transaction-node {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px;
            min-width: 200px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .transaction-node.status-pending {
            border-left: 4px solid #ffc107;
        }

        .transaction-node.status-in_progress {
            border-left: 4px solid #17a2b8;
        }

        .transaction-node.status-completed {
            border-left: 4px solid #28a745;
        }

        .transaction-node.status-overdue {
            border-left: 4px solid #dc3545;
        }

        .node-role-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .role-boss {
            background: #6f42c1;
            color: white;
        }

        .role-manager {
            background: #007bff;
            color: white;
        }

        .role-curator {
            background: #20c997;
            color: white;
        }

        .role-worker {
            background: #6c757d;
            color: white;
        }

        /* Balance card animation */
        .budget-card .card-body h4 {
            transition: all 0.3s ease;
        }

        .budget-card .card-body h4.updated {
            animation: balance-update 0.5s ease;
        }

        @keyframes balance-update {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
                color: #0d6efd;
            }

            100% {
                transform: scale(1);
            }
        }
    </style>
@endpush

@section('content')
    <div class="row">
        {{-- Main Diagram Area --}}
        <div class="col-lg-9 col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0">{{ __('Диаграмма движения денег') }}</h5>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            {{-- Status filter buttons --}}
                            <div class="btn-group" role="group" id="statusFilter">
                                <button type="button" class="status-filter-btn active"
                                    data-status="all">{{ __('Все') }}</button>
                                <button type="button" class="status-filter-btn filter-pending"
                                    data-status="pending">{{ __('Ожидает') }}</button>
                                <button type="button" class="status-filter-btn filter-in_progress"
                                    data-status="in_progress">{{ __('В работе') }}</button>
                                <button type="button" class="status-filter-btn filter-completed"
                                    data-status="completed">{{ __('Выполнено') }}</button>
                            </div>
                            @if ($period->is_frozen)
                                <span class="badge bg-secondary">
                                    <i class="ti ti-lock me-1"></i>{{ __('Период заморожен') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="diagram-container p-3" id="diagramContainer">
                        <div class="text-center py-5" id="diagramLoading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">{{ __('Загрузка...') }}</span>
                            </div>
                            <p class="mt-2 text-muted">{{ __('Загрузка диаграммы...') }}</p>
                        </div>
                        <div id="diagramContent" style="display: none;"></div>
                        <div id="diagramEmpty" class="text-center py-5" style="display: none;">
                            <i class="ti ti-chart-dots" style="font-size: 48px; color: #ccc;"></i>
                            <p class="mt-2 text-muted">{{ __('Нет транзакций в этом периоде') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Budget Sidebar --}}
        <div class="col-lg-3 col-md-4">
            <div class="card budget-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="ti ti-wallet me-2"></i>{{ __('Бюджет') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">{{ __('Получено') }}</small>
                        <h4 class="text-success mb-0" id="balanceReceived">
                            {{ formatCashboxCurrency($balance['received']) }}
                        </h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">{{ __('Выдано') }}</small>
                        <h4 class="text-danger mb-0" id="balanceSent">
                            {{ formatCashboxCurrency($balance['sent']) }}
                        </h4>
                    </div>
                    <hr>
                    <div>
                        <small class="text-muted">{{ __('Остаток') }}</small>
                        <h4 class="text-primary mb-0" id="balanceRemaining">
                            {{ formatCashboxCurrency($balance['received'] - $balance['sent']) }}
                        </h4>
                    </div>
                </div>
            </div>

            {{-- Period Info --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="ti ti-info-circle me-2"></i>{{ __('Информация') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">{{ __('Период') }}</small>
                        <p class="mb-0 fw-bold">{{ $period->name }}</p>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">{{ __('Всего внесено') }}</small>
                        <p class="mb-0">{{ formatCashboxCurrency($period->total_deposited) }}</p>
                    </div>
                    <div>
                        <small class="text-muted">{{ __('Статус') }}</small>
                        <p class="mb-0">
                            @if ($period->is_frozen)
                                <span class="badge bg-secondary">{{ __('Заморожен') }}</span>
                            @else
                                <span class="badge bg-success">{{ __('Активен') }}</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Deposit Modal (only for Boss) --}}
    @if ($isBoss)
        <div class="modal fade" id="depositModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Внести деньги') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="depositForm">
                        @csrf
                        <input type="hidden" name="period_id" value="{{ $period->id }}">
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Сумма') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="amount" class="form-control" step="0.01"
                                        min="0.01" required>
                                    <span class="input-group-text">{{ getCashboxCurrencySymbol() }}</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('Комментарий') }}</label>
                                <textarea name="comment" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">{{ __('Отмена') }}</button>
                            <button type="submit" class="btn btn-primary">{{ __('Внести') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Distribute Modal --}}
    <div class="modal fade" id="distributeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Выдать деньги') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="distributeForm">
                    @csrf
                    <input type="hidden" name="period_id" value="{{ $period->id }}">
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label class="form-label">{{ __('Тип выдачи') }} <span class="text-danger">*</span></label>
                            <select name="distribution_type" id="distributionTypeSelect" class="form-control" required>
                                <option value="">{{ __('Выберите тип выдачи') }}</option>
                                <option value="salary">{{ __('Зарплата сотруднику') }}</option>
                                <option value="transfer">{{ __('Передача средств') }}</option>
                            </select>
                            <small class="text-muted" id="distributionTypeHintMain"></small>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">{{ __('Получатель') }} <span class="text-danger">*</span></label>
                            <select name="recipient" class="form-control" required>
                                <option value="">{{ __('Выберите получателя') }}</option>
                                @foreach ($recipients as $recipient)
                                    @if (!isset($recipient['is_self']))
                                        <option
                                            value="{{ $recipient['type'] === 'App\\Models\\Worker' ? 'worker' : 'user' }}_{{ $recipient['id'] }}"
                                            data-role="{{ $recipient['role'] }}">
                                            {{ $recipient['name'] }}
                                            ({{ $recipient['role'] === 'manager' ? __('Менеджер') : ($recipient['role'] === 'curator' ? __('Куратор') : __('Работник')) }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">{{ __('Сумма') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="amount" class="form-control" step="0.01" min="0.01"
                                    required>
                                <span class="input-group-text">{{ getCashboxCurrencySymbol() }}</span>
                            </div>
                            <small class="text-muted">{{ __('Доступно:') }} <span
                                    id="availableBalance">{{ formatCashboxCurrency($balance['received'] - $balance['sent']) }}</span></small>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">{{ __('Задача') }}</label>
                            <input type="text" name="task" class="form-control"
                                placeholder="{{ __('Описание задачи...') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('Комментарий') }}</label>
                            <textarea name="comment" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Отмена') }}</button>
                        <button type="submit" class="btn btn-success">{{ __('Выдать') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Refund Modal --}}
    <div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Вернуть деньги') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="refundForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label class="form-label">{{ __('Транзакция для возврата') }} <span class="text-danger">*</span></label>
                            <select name="transaction_id" id="refundTransactionId" class="form-control" required>
                                <option value="">{{ __('Выберите транзакцию') }}</option>
                                @foreach($refundableTransactions as $transaction)
                                    <option value="{{ $transaction->id }}" data-amount="{{ $transaction->amount }}">
                                        #{{ $transaction->id }} | 
                                        {{ $transaction->sender->name ?? __('Неизвестно') }} | 
                                        {{ formatCashboxCurrency($transaction->amount) }} | 
                                        {{ $transaction->created_at->format('d.m.Y H:i') }}
                                        @if($transaction->task)
                                            | {{ Str::limit($transaction->task, 20) }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @if($refundableTransactions->isEmpty())
                                <small class="text-muted">{{ __('Нет транзакций для возврата') }}</small>
                            @endif
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">{{ __('Сумма') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="amount" class="form-control" step="0.01" min="0.01"
                                    required>
                                <span class="input-group-text">{{ getCashboxCurrencySymbol() }}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('Причина возврата') }} <span
                                    class="text-danger">*</span></label>
                            <textarea name="comment" class="form-control" rows="2" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Отмена') }}</button>
                        <button type="submit" class="btn btn-warning" {{ $refundableTransactions->isEmpty() ? 'disabled' : '' }}>{{ __('Вернуть') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Self Salary Modal --}}
    @if ($canSelfSalary)
        <div class="modal fade" id="selfSalaryModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Зарплата себе') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="selfSalaryForm">
                        @csrf
                        <input type="hidden" name="period_id" value="{{ $period->id }}">
                        <div class="modal-body">
                            @if ($hasSelfSalaryThisPeriod)
                                <div class="alert alert-warning">
                                    <i class="ti ti-alert-triangle me-1"></i>
                                    {{ __('Вы уже получили зарплату в этом периоде') }}
                                </div>
                            @endif
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Сумма') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="amount" class="form-control" step="0.01"
                                        min="0.01" required {{ $hasSelfSalaryThisPeriod ? 'disabled' : '' }}>
                                    <span class="input-group-text">{{ getCashboxCurrencySymbol() }}</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('Комментарий') }}</label>
                                <textarea name="comment" class="form-control" rows="2" {{ $hasSelfSalaryThisPeriod ? 'disabled' : '' }}></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">{{ __('Отмена') }}</button>
                            <button type="submit" class="btn btn-info"
                                {{ $hasSelfSalaryThisPeriod ? 'disabled' : '' }}>{{ __('Получить') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Transaction Detail Modal --}}
    <div class="modal fade" id="transactionDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Детали транзакции') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="transactionDetailContent">
                    {{-- Content loaded dynamically --}}
                </div>
                <div class="modal-footer" id="transactionDetailFooter">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('Закрыть') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('script-page')
    <script src="{{ asset('js/cashbox-diagram.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var periodId = {{ $period->id }};
            var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            var diagram = null;

            // Currency symbol for formatting
            var currencySymbol = '{{ getCashboxCurrencySymbol() }}';

            // Initialize diagram with translations
            var translations = {
                deposit: '{{ __('Внесение') }}',
                distribution: '{{ __('Выдача') }}',
                refund: '{{ __('Возврат') }}',
                self_salary: '{{ __('ЗП себе') }}',
                pending: '{{ __('Ожидает') }}',
                in_progress: '{{ __('В работе') }}',
                completed: '{{ __('Выполнено') }}',
                overdue: '{{ __('Просрочено') }}',
                unknown: '{{ __('Неизвестно') }}',
                loading: '{{ __('Загрузка...') }}',
                error: '{{ __('Ошибка загрузки') }}',
                no_transactions: '{{ __('Нет транзакций в этом периоде') }}',
                sender: '{{ __('Отправитель') }}',
                recipient: '{{ __('Получатель') }}',
                amount: '{{ __('Сумма') }}',
                status: '{{ __('Статус') }}',
                task: '{{ __('Задача') }}',
                comment: '{{ __('Комментарий') }}',
                date: '{{ __('Дата') }}',
                type: '{{ __('Тип операции') }}',
                take_to_work: '{{ __('Взять в работу') }}',
                close: '{{ __('Закрыть') }}'
            };

            // Initialize the diagram
            diagram = new CashboxDiagram('diagramContent', {
                translations: translations,
                onNodeClick: function(node) {
                    showTransactionDetail(node);
                }
            });

            // Load diagram data
            loadDiagram();

            function loadDiagram() {
                document.getElementById('diagramLoading').style.display = 'block';
                document.getElementById('diagramContent').style.display = 'none';
                document.getElementById('diagramEmpty').style.display = 'none';

                fetch('{{ route('cashbox.diagram', $period->id) }}', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('diagramLoading').style.display = 'none';
                        if (data.diagram && data.diagram.nodes && data.diagram.nodes.length > 0) {
                            document.getElementById('diagramContent').style.display = 'block';
                            diagram.setData(data.diagram);
                        } else {
                            document.getElementById('diagramEmpty').style.display = 'block';
                        }
                    })
                    .catch(error => {
                        document.getElementById('diagramLoading').style.display = 'none';
                        document.getElementById('diagramContent').style.display = 'block';
                        diagram.showError('{{ __('Ошибка загрузки диаграммы') }}');
                    });
            }

            function getTypeName(type) {
                return translations[type] || type;
            }

            function getStatusName(status) {
                return translations[status] || status;
            }

            function getStatusBadgeClass(status) {
                switch (status) {
                    case 'pending':
                        return 'bg-warning';
                    case 'in_progress':
                        return 'bg-info';
                    case 'completed':
                        return 'bg-success';
                    case 'overdue':
                        return 'bg-danger';
                    default:
                        return 'bg-secondary';
                }
            }

            function formatMoney(amount) {
                return parseFloat(amount).toLocaleString('ru-RU', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' ' + currencySymbol;
            }

            function showTransactionDetail(node) {
                var content = document.getElementById('transactionDetailContent');
                var senderName = node.sender ? node.sender.name : null;
                var recipientName = node.recipient ? node.recipient.name : translations.unknown;

                content.innerHTML = `
            <div class="mb-3">
                <label class="text-muted small">${translations.type}</label>
                <p class="mb-0 fw-bold">${getTypeName(node.type)}</p>
            </div>
            ${senderName ? `
                                        <div class="mb-3">
                                            <label class="text-muted small">${translations.sender}</label>
                                            <p class="mb-0">${senderName}</p>
                                        </div>` : ''}
            <div class="mb-3">
                <label class="text-muted small">${translations.recipient}</label>
                <p class="mb-0">${recipientName}</p>
            </div>
            <div class="mb-3">
                <label class="text-muted small">${translations.amount}</label>
                <p class="mb-0 fw-bold text-success">${formatMoney(node.amount)}</p>
            </div>
            <div class="mb-3">
                <label class="text-muted small">${translations.status}</label>
                <p class="mb-0"><span class="badge ${getStatusBadgeClass(node.status)}">${node.status_label || getStatusName(node.status)}</span></p>
            </div>
            ${node.task ? `
                                        <div class="mb-3">
                                            <label class="text-muted small">${translations.task}</label>
                                            <p class="mb-0">${escapeHtml(node.task)}</p>
                                        </div>` : ''}
            ${node.comment ? `
                                        <div class="mb-3">
                                            <label class="text-muted small">${translations.comment}</label>
                                            <p class="mb-0">${escapeHtml(node.comment)}</p>
                                        </div>` : ''}
            ${node.created_at ? `
                                        <div class="mb-3">
                                            <label class="text-muted small">${translations.date}</label>
                                            <p class="mb-0">${new Date(node.created_at).toLocaleString('ru-RU')}</p>
                                        </div>` : ''}
        `;

                var footer = document.getElementById('transactionDetailFooter');
                footer.innerHTML = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' +
                    translations.close + '</button>';

                // Add "Take to work" button if pending and user can update
                if (node.status === 'pending') {
                    footer.innerHTML = `
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${translations.close}</button>
                <button type="button" class="btn btn-info" onclick="updateTransactionStatus(${node.id}, 'in_progress')">
                    <i class="ti ti-player-play me-1"></i>${translations.take_to_work}
                </button>
            `;
                }

                // Store selected node for refund
                window.selectedTransactionNode = node;

                new bootstrap.Modal(document.getElementById('transactionDetailModal')).show();
            }

            function escapeHtml(text) {
                var div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            window.updateTransactionStatus = function(transactionId, status) {
                fetch('{{ url('cashbox/transaction') }}/' + transactionId + '/status', {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            status: status
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            show_toastr('success', data.message);
                            bootstrap.Modal.getInstance(document.getElementById('transactionDetailModal'))
                                .hide();
                            loadDiagram();
                            updateBalance();
                        } else {
                            show_toastr('error', data.error);
                        }
                    });
            };

            function updateBalance() {
                fetch('{{ route('cashbox.balance', $period->id) }}', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.balance) {
                            document.getElementById('balanceReceived').textContent = formatMoney(data.balance
                                .received);
                            document.getElementById('balanceSent').textContent = formatMoney(data.balance.sent);
                            document.getElementById('balanceRemaining').textContent = formatMoney(data.balance
                                .available);
                            if (document.getElementById('availableBalance')) {
                                document.getElementById('availableBalance').textContent = formatMoney(data
                                    .balance.available);
                            }
                            // Animate balance update
                            var balanceEls = document.querySelectorAll('.budget-card h4');
                            balanceEls.forEach(function(el) {
                                el.classList.add('updated');
                                setTimeout(function() {
                                    el.classList.remove('updated');
                                }, 500);
                            });
                        }
                    });
            }

            // Expose functions globally for diagram refresh
            window.refreshCashboxDiagram = loadDiagram;
            window.updateCashboxBalance = updateBalance;

            // Status filter functionality
            var statusFilterBtns = document.querySelectorAll('#statusFilter .status-filter-btn');
            statusFilterBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    // Update active state
                    statusFilterBtns.forEach(function(b) {
                        b.classList.remove('active');
                    });
                    this.classList.add('active');

                    // Apply filter
                    var status = this.getAttribute('data-status');
                    if (diagram) {
                        diagram.filterByStatus(status);
                    }
                });
            });

            // Balance update animation
            function animateBalanceUpdate() {
                var balanceEls = document.querySelectorAll('.budget-card h4');
                balanceEls.forEach(function(el) {
                    el.classList.add('updated');
                    setTimeout(function() {
                        el.classList.remove('updated');
                    }, 500);
                });
            }

            // Form submissions
            function handleFormSubmit(formId, url, successCallback) {
                var form = document.getElementById(formId);
                if (!form) return;

                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    var submitBtn = form.querySelector('button[type="submit"]');
                    var originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-1"></span>{{ __('Загрузка...') }}';

                    var formData = new FormData(form);

                    // Parse recipient field for distribute form
                    if (formId === 'distributeForm') {
                        var recipientVal = formData.get('recipient');
                        if (recipientVal) {
                            var parts = recipientVal.split('_');
                            formData.set('recipient_type', parts[0]);
                            formData.set('recipient_id', parts[1]);
                            formData.delete('recipient');
                        }
                    }

                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                show_toastr('success', data.message);
                                var modal = bootstrap.Modal.getInstance(form.closest('.modal'));
                                if (modal) modal.hide();
                                form.reset();
                                loadDiagram();
                                updateBalance();
                                if (successCallback) successCallback(data);
                            } else {
                                show_toastr('error', data.error);
                            }
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        })
                        .catch(error => {
                            show_toastr('error', '{{ __('Произошла ошибка') }}');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        });
                });
            }

            handleFormSubmit('depositForm', '{{ route('cashbox.deposit') }}');
            handleFormSubmit('distributeForm', '{{ route('cashbox.distribute') }}');
            handleFormSubmit('refundForm', '{{ route('cashbox.refund') }}');
            handleFormSubmit('selfSalaryForm', '{{ route('cashbox.self-salary') }}', function() {
                // Disable self-salary button after success
                var btn = document.querySelector('[data-bs-target="#selfSalaryModal"]');
                if (btn) btn.style.display = 'none';
            });

            // Handle refund modal - pre-select transaction if one was selected on diagram
            var refundModal = document.getElementById('refundModal');
            var refundTransactionSelect = document.getElementById('refundTransactionId');
            
            if (refundModal) {
                refundModal.addEventListener('show.bs.modal', function() {
                    if (window.selectedTransactionNode && refundTransactionSelect) {
                        // Try to select the transaction in dropdown
                        var option = refundTransactionSelect.querySelector('option[value="' + window.selectedTransactionNode.id + '"]');
                        if (option) {
                            refundTransactionSelect.value = window.selectedTransactionNode.id;
                            // Trigger change to fill amount
                            refundTransactionSelect.dispatchEvent(new Event('change'));
                        }
                    }
                });
            }
            
            // Auto-fill amount when transaction is selected
            if (refundTransactionSelect) {
                refundTransactionSelect.addEventListener('change', function() {
                    var selectedOption = this.options[this.selectedIndex];
                    var amount = selectedOption.getAttribute('data-amount');
                    if (amount) {
                        var amountInput = document.querySelector('#refundForm input[name="amount"]');
                        if (amountInput) {
                            amountInput.value = amount;
                        }
                    }
                });
            }

            // Distribution type hint
            var distributionTypeSelect = document.getElementById('distributionTypeSelect');
            var distributionTypeHint = document.getElementById('distributionTypeHintMain');
            
            if (distributionTypeSelect && distributionTypeHint) {
                distributionTypeSelect.addEventListener('change', function() {
                    var value = this.value;
                    if (value === 'salary') {
                        distributionTypeHint.textContent = '{{ __("Конечная выдача зарплаты. Транзакция будет сразу завершена.") }}';
                    } else if (value === 'transfer') {
                        distributionTypeHint.textContent = '{{ __("Передача денег для дальнейшего распределения другим сотрудникам.") }}';
                    } else {
                        distributionTypeHint.textContent = '';
                    }
                });
            }
        });
    </script>
@endpush
