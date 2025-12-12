@extends('layouts.mobile')

@section('content')
    {{-- Header --}}
    <div class="mobile-header">
        <div class="mobile-header-row">
            <div class="mobile-header-left">
                <a href="{{ route('mobile.cashbox.index') }}" class="mobile-header-btn">
                    <i class="ti ti-arrow-left" style="font-size: 24px; color: #FF0049;"></i>
                </a>
            </div>
            <div class="mobile-header-right">
                <div class="mobile-logo">
                    <img src="{{ asset('fromfigma/jobsi_mobile.png') }}" alt="JOBSI">
                </div>
            </div>
        </div>
    </div>

    <div class="mobile-content">
        {{-- Page Title --}}
        <div class="mobile-section-title mb-3">
            <div class="mobile-section-title-left">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#FF0049" stroke-width="2">
                    <path d="M17 8V5a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h3"></path>
                    <path d="M21 12v6a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-6a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1z"></path>
                    <circle cx="14" cy="15" r="2"></circle>
                </svg>
                <span>{{ $period->name }}</span>
            </div>
            @if($period->is_frozen)
                <span class="badge bg-secondary"><i class="ti ti-lock me-1"></i>{{ __('Frozen') }}</span>
            @endif
        </div>

        {{-- Balance Card --}}
        <div class="mobile-card mb-3" style="background: linear-gradient(135deg, #FF0049, #FF6B6B); color: #fff;">
            <div class="row text-center">
                <div class="col-4">
                    <small style="opacity: 0.8;">{{ __('Received') }}</small>
                    <h5 class="mb-0">{{ formatCashboxCurrency($balance['received']) }}</h5>
                </div>
                <div class="col-4">
                    <small style="opacity: 0.8;">{{ __('Distributed') }}</small>
                    <h5 class="mb-0">{{ formatCashboxCurrency($balance['sent']) }}</h5>
                </div>
                <div class="col-4">
                    <small style="opacity: 0.8;">{{ __('Remaining') }}</small>
                    <h5 class="mb-0">{{ formatCashboxCurrency($balance['received'] - $balance['sent']) }}</h5>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        @if(!$period->is_frozen)
            <div class="mobile-card mb-3">
                <h6 class="mb-3"><i class="ti ti-bolt me-2 text-primary"></i>{{ __('Actions') }}</h6>
                <div class="d-flex flex-wrap gap-2">
                    @if($canDeposit)
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#depositModal">
                            <i class="ti ti-plus me-1"></i>{{ __('Deposit') }}
                        </button>
                    @endif
                    @if($balance['received'] > $balance['sent'] && $canDistribute)
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#distributeModal">
                            <i class="ti ti-send me-1"></i>{{ __('Distribute') }}
                        </button>
                    @endif
                    @if($balance['received'] > $balance['sent'] && $canRefund && $userRole !== 'boss')
                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#refundModal">
                            <i class="ti ti-arrow-back me-1"></i>{{ __('Refund') }}
                        </button>
                    @endif
                    @if($canSelfSalary && !$hasSelfSalaryThisPeriod && $balance['received'] > $balance['sent'])
                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#selfSalaryModal">
                            <i class="ti ti-wallet me-1"></i>{{ __('Self Salary') }}
                        </button>
                    @endif
                </div>
            </div>
        @endif

        {{-- Period Info --}}
        <div class="mobile-card mb-3">
            <h6 class="mb-3"><i class="ti ti-info-circle me-2 text-primary"></i>{{ __('Information') }}</h6>
            <div class="mobile-info-list">
                <div class="mobile-info-item">
                    <span class="mobile-info-label">{{ __('Period') }}</span>
                    <span class="mobile-info-value">{{ $period->name }}</span>
                </div>
                @if($canViewTotalDeposited)
                    <div class="mobile-info-item">
                        <span class="mobile-info-label">{{ __('Total Deposited') }}</span>
                        <span class="mobile-info-value">{{ formatCashboxCurrency($period->total_deposited) }}</span>
                    </div>
                @endif
                <div class="mobile-info-item">
                    <span class="mobile-info-label">{{ __('Status') }}</span>
                    <span class="mobile-info-value">
                        @if($period->is_frozen)
                            <span class="badge bg-secondary">{{ __('Frozen') }}</span>
                        @else
                            <span class="badge bg-success">{{ __('Active') }}</span>
                        @endif
                    </span>
                </div>
                <div class="mobile-info-item">
                    <span class="mobile-info-label">{{ __('Your Role') }}</span>
                    <span class="mobile-info-value">
                        @if($userRole === 'boss')
                            {{ __('Boss') }}
                        @elseif($userRole === 'manager')
                            {{ __('Manager') }}
                        @elseif($userRole === 'curator')
                            {{ __('Curator') }}
                        @else
                            {{ __('Worker') }}
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Transactions List --}}
        <div class="mobile-card mb-3">
            <h6 class="mb-3"><i class="ti ti-list me-2 text-primary"></i>{{ __('Transactions') }}</h6>
            
            @forelse($transactions as $transaction)
                <div class="mobile-transaction-item" onclick="showTransactionDetail({{ $transaction->id }})">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                @if($transaction->type === 'deposit')
                                    <span class="badge bg-primary">{{ __('Deposit') }}</span>
                                @elseif($transaction->type === 'distribution')
                                    @if($transaction->distribution_type === 'salary')
                                        <span class="badge bg-success">{{ __('Salary') }}</span>
                                    @elseif($transaction->distribution_type === 'transfer')
                                        <span class="badge bg-info">{{ __('Transfer') }}</span>
                                    @else
                                        <span class="badge bg-success">{{ __('Distribution') }}</span>
                                    @endif
                                @elseif($transaction->type === 'refund')
                                    <span class="badge bg-warning">{{ __('Refund') }}</span>
                                @elseif($transaction->type === 'self_salary')
                                    <span class="badge bg-info">{{ __('Self Salary') }}</span>
                                @endif
                                <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'info') }}">
                                    {{ $transaction->status === 'completed' ? __('Completed') : ($transaction->status === 'pending' ? __('Pending') : __('In Progress')) }}
                                </span>
                            </div>
                            <small class="text-muted">
                                @if($transaction->sender)
                                    {{ $transaction->sender->name ?? __('Unknown') }}
                                @endif
                                @if($transaction->recipient)
                                    → {{ $transaction->recipient->name ?? ($transaction->recipient->first_name ?? '') . ' ' . ($transaction->recipient->last_name ?? '') }}
                                @endif
                            </small>
                            @if($transaction->task)
                                <div class="small text-muted mt-1">
                                    <i class="ti ti-clipboard me-1"></i>{{ Str::limit($transaction->task, 30) }}
                                </div>
                            @endif
                        </div>
                        <div class="text-end">
                            <div class="fw-bold {{ $transaction->type === 'deposit' || $transaction->type === 'refund' ? 'text-success' : 'text-danger' }}">
                                {{ $transaction->type === 'deposit' || $transaction->type === 'refund' ? '+' : '-' }}{{ formatCashboxCurrency($transaction->amount) }}
                            </div>
                            <small class="text-muted">{{ $transaction->created_at->format('d.m H:i') }}</small>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="ti ti-receipt-off" style="font-size: 32px; opacity: 0.5;"></i>
                    <p class="small mt-2 mb-0">{{ __('No transactions in this period') }}</p>
                </div>
            @endforelse
        </div>
    </div>


    {{-- Deposit Modal --}}
    @if($canDeposit && !$period->is_frozen)
        <div class="modal fade" id="depositModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="depositForm">
                        @csrf
                        <input type="hidden" name="period_id" value="{{ $period->id }}">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('Deposit Money') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required placeholder="0.00">
                                    <span class="input-group-text">{{ getCashboxCurrencySymbol() }}</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('Comment') }}</label>
                                <textarea name="comment" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary" id="depositSubmitBtn">{{ __('Deposit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Distribute Modal --}}
    @if($canDistribute && !$period->is_frozen && $balance['received'] > $balance['sent'])
        <div class="modal fade" id="distributeModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="distributeForm">
                        @csrf
                        <input type="hidden" name="period_id" value="{{ $period->id }}">
                        <input type="hidden" name="recipient_id" id="recipient_id" value="">
                        <input type="hidden" name="recipient_type" id="recipient_type" value="">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('Distribute Money') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Distribution Type') }} <span class="text-danger">*</span></label>
                                <select name="distribution_type" id="distributionType" class="form-control" required>
                                    <option value="">{{ __('Select distribution type') }}</option>
                                    <option value="salary">{{ __('Employee Salary') }}</option>
                                    <option value="transfer">{{ __('Fund Transfer') }}</option>
                                </select>
                                <small class="text-muted" id="distributionTypeHint"></small>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Recipient') }} <span class="text-danger">*</span></label>
                                <select id="recipientSelect" class="form-control" required>
                                    <option value="">{{ __('Select recipient') }}</option>
                                    @foreach($recipients as $recipient)
                                        @if(!isset($recipient['is_self']))
                                            <option value="{{ $recipient['type'] === 'App\\Models\\Worker' ? 'worker' : 'user' }}_{{ $recipient['id'] }}">
                                                {{ $recipient['name'] }} 
                                                ({{ $recipient['role'] === 'manager' ? __('Manager') : ($recipient['role'] === 'curator' ? __('Curator') : __('Worker')) }})
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required placeholder="0.00">
                                    <span class="input-group-text">{{ getCashboxCurrencySymbol() }}</span>
                                </div>
                                <small class="text-muted">{{ __('Available:') }} {{ formatCashboxCurrency($balance['received'] - $balance['sent']) }}</small>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Task') }}</label>
                                <input type="text" name="task" class="form-control" placeholder="{{ __('Task description...') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('Comment') }}</label>
                                <textarea name="comment" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-success" id="distributeSubmitBtn">{{ __('Distribute') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Refund Modal --}}
    @if($canRefund && !$period->is_frozen && $refundableTransactions->count() > 0)
        <div class="modal fade" id="refundModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="refundForm">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('Refund Money') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Transaction to Refund') }} <span class="text-danger">*</span></label>
                                <select name="transaction_id" id="refundTransactionId" class="form-control" required>
                                    <option value="">{{ __('Select transaction') }}</option>
                                    @foreach($refundableTransactions as $transaction)
                                        <option value="{{ $transaction->id }}" data-amount="{{ $transaction->amount }}">
                                            #{{ $transaction->id }} | {{ formatCashboxCurrency($transaction->amount) }} | {{ $transaction->created_at->format('d.m.Y') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                                    <span class="input-group-text">{{ getCashboxCurrencySymbol() }}</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('Refund Reason') }} <span class="text-danger">*</span></label>
                                <textarea name="comment" class="form-control" rows="2" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-warning" id="refundSubmitBtn">{{ __('Refund') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Self Salary Modal --}}
    @if($canSelfSalary && !$period->is_frozen && !$hasSelfSalaryThisPeriod)
        <div class="modal fade" id="selfSalaryModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="selfSalaryForm">
                        @csrf
                        <input type="hidden" name="period_id" value="{{ $period->id }}">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('Self Salary') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required placeholder="0.00">
                                    <span class="input-group-text">{{ getCashboxCurrencySymbol() }}</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('Comment') }}</label>
                                <textarea name="comment" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-info" id="selfSalarySubmitBtn">{{ __('Receive') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Transaction Detail Modal --}}
    <div class="modal fade" id="transactionDetailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Transaction Details') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="transactionDetailContent">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .mobile-card {
            background: #fff;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .mobile-info-list {
            display: flex;
            flex-direction: column;
        }
        .mobile-info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .mobile-info-item:last-child {
            border-bottom: none;
        }
        .mobile-info-label {
            color: #666;
            font-size: 13px;
        }
        .mobile-info-value {
            font-weight: 500;
            font-size: 13px;
        }
        .mobile-transaction-item {
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
        }
        .mobile-transaction-item:last-child {
            border-bottom: none;
        }
        .mobile-transaction-item:active {
            background: #f8f9fa;
        }
        .text-primary {
            color: #FF0049 !important;
        }
    </style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Distribution type hint
    var distributionTypeSelect = document.getElementById('distributionType');
    var distributionTypeHint = document.getElementById('distributionTypeHint');
    
    if (distributionTypeSelect) {
        distributionTypeSelect.addEventListener('change', function() {
            var value = this.value;
            if (value === 'salary') {
                distributionTypeHint.textContent = '{{ __("Final salary payment. Transaction will be completed immediately.") }}';
            } else if (value === 'transfer') {
                distributionTypeHint.textContent = '{{ __("Money transfer for further distribution to other employees.") }}';
            } else {
                distributionTypeHint.textContent = '';
            }
        });
    }

    // Recipient select - parse value to recipient_id and recipient_type
    var recipientSelect = document.getElementById('recipientSelect');
    if (recipientSelect) {
        recipientSelect.addEventListener('change', function() {
            var value = this.value;
            if (value) {
                var parts = value.split('_');
                document.getElementById('recipient_type').value = parts[0];
                document.getElementById('recipient_id').value = parts[1];
            } else {
                document.getElementById('recipient_type').value = '';
                document.getElementById('recipient_id').value = '';
            }
        });
    }

    // Refund transaction amount auto-fill
    var refundTransactionSelect = document.getElementById('refundTransactionId');
    if (refundTransactionSelect) {
        refundTransactionSelect.addEventListener('change', function() {
            var selected = this.options[this.selectedIndex];
            var amount = selected.getAttribute('data-amount');
            if (amount) {
                document.querySelector('#refundModal input[name="amount"]').value = amount;
            }
        });
    }

    // Form submissions
    setupFormSubmit('depositForm', '{{ route("cashbox.deposit") }}', 'depositSubmitBtn', '{{ __("Deposit") }}');
    setupFormSubmit('distributeForm', '{{ route("cashbox.distribute") }}', 'distributeSubmitBtn', '{{ __("Distribute") }}');
    setupFormSubmit('refundForm', '{{ route("cashbox.refund") }}', 'refundSubmitBtn', '{{ __("Refund") }}');
    setupFormSubmit('selfSalaryForm', '{{ route("cashbox.self-salary") }}', 'selfSalarySubmitBtn', '{{ __("Receive") }}');

    function setupFormSubmit(formId, url, btnId, btnText) {
        var form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                submitCashboxForm(this, url, btnId, btnText);
            });
        }
    }

    function submitCashboxForm(form, url, btnId, btnText) {
        var submitBtn = document.getElementById(btnId);
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>{{ __("Loading...") }}';
        
        var formData = new FormData(form);
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                show_toastr('success', data.message);
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            } else {
                show_toastr('error', data.error || '{{ __("An error occurred") }}');
                submitBtn.disabled = false;
                submitBtn.innerHTML = btnText;
            }
        })
        .catch(error => {
            show_toastr('error', '{{ __("An error occurred") }}');
            submitBtn.disabled = false;
            submitBtn.innerHTML = btnText;
        });
    }
});

function showTransactionDetail(transactionId) {
    var modal = new bootstrap.Modal(document.getElementById('transactionDetailModal'));
    var content = document.getElementById('transactionDetailContent');
    
    content.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div></div>';
    modal.show();
    
    fetch('{{ route("cashbox.transaction.show", "") }}/' + transactionId, {
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            var t = data.transaction;
            var html = '<div class="mobile-info-list">';
            html += '<div class="mobile-info-item"><span class="mobile-info-label">{{ __("Type") }}</span><span class="mobile-info-value">' + getTypeName(t.type, t.distribution_type) + '</span></div>';
            html += '<div class="mobile-info-item"><span class="mobile-info-label">{{ __("Amount") }}</span><span class="mobile-info-value fw-bold">' + t.formatted_amount + '</span></div>';
            html += '<div class="mobile-info-item"><span class="mobile-info-label">{{ __("Status") }}</span><span class="mobile-info-value">' + getStatusBadge(t.status) + '</span></div>';
            if (t.sender_name) {
                html += '<div class="mobile-info-item"><span class="mobile-info-label">{{ __("Sender") }}</span><span class="mobile-info-value">' + t.sender_name + '</span></div>';
            }
            if (t.recipient_name) {
                html += '<div class="mobile-info-item"><span class="mobile-info-label">{{ __("Recipient") }}</span><span class="mobile-info-value">' + t.recipient_name + '</span></div>';
            }
            if (t.task) {
                html += '<div class="mobile-info-item"><span class="mobile-info-label">{{ __("Task") }}</span><span class="mobile-info-value">' + t.task + '</span></div>';
            }
            if (t.comment) {
                html += '<div class="mobile-info-item"><span class="mobile-info-label">{{ __("Comment") }}</span><span class="mobile-info-value">' + t.comment + '</span></div>';
            }
            html += '<div class="mobile-info-item"><span class="mobile-info-label">{{ __("Date") }}</span><span class="mobile-info-value">' + t.created_at + '</span></div>';
            html += '</div>';
            content.innerHTML = html;
        } else {
            content.innerHTML = '<div class="alert alert-danger">{{ __("Error loading transaction") }}</div>';
        }
    })
    .catch(error => {
        content.innerHTML = '<div class="alert alert-danger">{{ __("Error loading transaction") }}</div>';
    });
}

function getTypeName(type, distributionType) {
    if (type === 'distribution') {
        if (distributionType === 'salary') {
            return '{{ __("Salary") }}';
        } else if (distributionType === 'transfer') {
            return '{{ __("Transfer") }}';
        }
        return '{{ __("Distribution") }}';
    }
    var types = {
        'deposit': '{{ __("Deposit") }}',
        'refund': '{{ __("Refund") }}',
        'self_salary': '{{ __("Self Salary") }}'
    };
    return types[type] || type;
}

function getStatusBadge(status) {
    var badges = {
        'pending': '<span class="badge bg-warning">{{ __("Pending") }}</span>',
        'in_progress': '<span class="badge bg-info">{{ __("In Progress") }}</span>',
        'completed': '<span class="badge bg-success">{{ __("Completed") }}</span>'
    };
    return badges[status] || status;
}
</script>
@endpush
