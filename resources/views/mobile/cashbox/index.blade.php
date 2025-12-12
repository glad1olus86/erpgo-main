@extends('layouts.mobile')

@section('content')
    {{-- Header --}}
    <div class="mobile-header">
        <div class="mobile-header-row">
            <div class="mobile-header-left">
                <button type="button" class="mobile-header-btn" onclick="openSidebar()">
                    <img src="{{ asset('fromfigma/menu_mobile.svg') }}" alt=""
                        onerror="this.outerHTML='<svg width=24 height=24 viewBox=\'0 0 24 24\' fill=none stroke=#FF0049 stroke-width=2><line x1=3 y1=6 x2=21 y2=6></line><line x1=3 y1=12 x2=21 y2=12></line><line x1=3 y1=18 x2=21 y2=18></line></svg>'">
                </button>
                <a href="{{ route('mobile.notifications.index') }}" class="mobile-header-btn">
                    <img src="{{ asset('fromfigma/bell_mobile.svg') }}" alt=""
                        onerror="this.outerHTML='<svg width=24 height=24 viewBox=\'0 0 24 24\' fill=none stroke=#FF0049 stroke-width=2><path d=\'M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9\'></path><path d=\'M13.73 21a2 2 0 0 1-3.46 0\'></path></svg>'">
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
        <div class="mobile-section-title">
            <div class="mobile-section-title-left">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#FF0049" stroke-width="2">
                    <path d="M17 8V5a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h3"></path>
                    <path d="M21 12v6a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-6a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1z"></path>
                    <circle cx="14" cy="15" r="2"></circle>
                </svg>
                <span>{{ __('Cashbox') }}</span>
            </div>
            @can('cashbox_deposit')
                <button type="button" class="mobile-add-btn" data-bs-toggle="modal" data-bs-target="#depositModal">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FF0049" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                </button>
            @endcan
        </div>

        {{-- Current Balance Card --}}
        @if($currentPeriod)
            <div class="mobile-card mb-3" style="background: linear-gradient(135deg, #FF0049, #FF6B6B); color: #fff;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small style="opacity: 0.8;">{{ __('Current Period') }}</small>
                        <h5 class="mb-0">{{ $currentPeriod->name }}</h5>
                    </div>
                    <div class="text-end">
                        <small style="opacity: 0.8;">{{ __('Balance') }}</small>
                        <h4 class="mb-0">{{ formatCashboxCurrency($balance['received'] - $balance['sent']) }}</h4>
                    </div>
                </div>
                <div class="mt-3 d-flex justify-content-between" style="font-size: 13px;">
                    <div>
                        <span style="opacity: 0.8;">{{ __('Received') }}:</span>
                        <span class="fw-bold">{{ formatCashboxCurrency($balance['received']) }}</span>
                    </div>
                    <div>
                        <span style="opacity: 0.8;">{{ __('Distributed') }}:</span>
                        <span class="fw-bold">{{ formatCashboxCurrency($balance['sent']) }}</span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Quick Actions --}}
        @if($currentPeriod)
            <div class="mobile-card mb-3">
                <h6 class="mb-3"><i class="ti ti-bolt me-2 text-primary"></i>{{ __('Quick Actions') }}</h6>
                <div class="d-flex flex-wrap gap-2">
                    @if($balance['received'] > $balance['sent'])
                        @can('cashbox_distribute')
                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#distributeModal">
                                <i class="ti ti-send me-1"></i>{{ __('Distribute') }}
                            </button>
                        @endcan
                        @can('cashbox_refund')
                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#refundModal">
                                <i class="ti ti-arrow-back me-1"></i>{{ __('Refund') }}
                            </button>
                        @endcan
                    @endif
                    @can('cashbox_view_audit')
                        <a href="{{ route('cashbox.audit') }}" class="btn btn-sm btn-secondary">
                            <i class="ti ti-history me-1"></i>{{ __('Audit') }}
                        </a>
                    @endcan
                </div>
            </div>
        @endif

        {{-- Periods List --}}
        <div class="mobile-card mb-3">
            <h6 class="mb-3"><i class="ti ti-calendar me-2 text-primary"></i>{{ __('Periods') }}</h6>
            
            @forelse($periods as $period)
                <a href="{{ route('mobile.cashbox.show', $period->id) }}" class="mobile-period-item d-block text-decoration-none">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-medium text-dark">{{ $period->name }}</div>
                            <small class="text-muted">
                                @if($period->is_frozen)
                                    <span class="badge bg-secondary"><i class="ti ti-lock me-1"></i>{{ __('Frozen') }}</span>
                                @else
                                    <span class="badge bg-success"><i class="ti ti-circle-check me-1"></i>{{ __('Active') }}</span>
                                @endif
                            </small>
                        </div>
                        <div class="text-end">
                            @can('cashbox_view_boss')
                                <div class="fw-bold text-primary">{{ formatCashboxCurrency($period->total_deposited) }}</div>
                                <small class="text-muted">{{ __('Deposited') }}</small>
                            @endcan
                        </div>
                    </div>
                </a>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="ti ti-cash" style="font-size: 32px; opacity: 0.5;"></i>
                    <p class="small mt-2 mb-0">{{ __('No cashbox periods') }}</p>
                    <p class="small text-muted">{{ __('Periods are created automatically when depositing money') }}</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Deposit Modal --}}
    @can('cashbox_deposit')
        @if($currentPeriod)
            <div class="modal fade" id="depositModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form id="depositForm">
                            @csrf
                            <input type="hidden" name="period_id" value="{{ $currentPeriod->id }}">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ __('Deposit Money') }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Period') }}</label>
                                    <input type="text" class="form-control" value="{{ $currentPeriod->name }}" disabled>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required placeholder="0.00">
                                        <span class="input-group-text">{{ getCashboxCurrencySymbol() }}</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{ __('Comment') }}</label>
                                    <textarea name="comment" class="form-control" rows="2" placeholder="{{ __('Optional comment...') }}"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                <button type="submit" class="btn mobile-btn-primary" id="depositSubmitBtn">{{ __('Deposit') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endcan


    {{-- Distribute Modal --}}
    @can('cashbox_distribute')
        @if($currentPeriod && $balance['received'] > $balance['sent'])
            <div class="modal fade" id="distributeModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form id="distributeForm">
                            @csrf
                            <input type="hidden" name="period_id" value="{{ $currentPeriod->id }}">
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
    @endcan

    {{-- Refund Modal --}}
    @can('cashbox_refund')
        @if($currentPeriod && $refundableTransactions->count() > 0)
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
    @endcan

    <style>
        .mobile-btn-primary {
            background: #FF0049 !important;
            border-color: #FF0049 !important;
            color: #fff !important;
        }
        .mobile-card {
            background: #fff;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .mobile-period-item {
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .mobile-period-item:last-child {
            border-bottom: none;
        }
        .text-primary {
            color: #FF0049 !important;
        }
        .mobile-add-btn {
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
        }
        .mobile-add-btn:focus {
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
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

    // Deposit form
    var depositForm = document.getElementById('depositForm');
    if (depositForm) {
        depositForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitCashboxForm(this, '{{ route("cashbox.deposit") }}', 'depositSubmitBtn', '{{ __("Deposit") }}');
        });
    }

    // Distribute form
    var distributeForm = document.getElementById('distributeForm');
    if (distributeForm) {
        distributeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitCashboxForm(this, '{{ route("cashbox.distribute") }}', 'distributeSubmitBtn', '{{ __("Distribute") }}');
        });
    }

    // Refund form
    var refundForm = document.getElementById('refundForm');
    if (refundForm) {
        refundForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitCashboxForm(this, '{{ route("cashbox.refund") }}', 'refundSubmitBtn', '{{ __("Refund") }}');
        });
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
</script>
@endpush
