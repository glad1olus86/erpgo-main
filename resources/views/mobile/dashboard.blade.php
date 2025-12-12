@extends('layouts.mobile')

@section('content')
    {{-- Header --}}
    <div class="mobile-header">
        <div class="mobile-header-row">
            {{-- Left side: Menu + Notifications --}}
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

            {{-- Right side: Language + Logo --}}
            <div class="mobile-header-right">
                <div class="dropdown">
                    <button class="mobile-lang-btn" data-bs-toggle="dropdown">
                        @php $lang = app()->getLocale(); @endphp
                        @if ($lang == 'cs')
                            <img src="{{ asset('fromfigma/czech_flag.svg') }}" alt="CS" class="mobile-flag">
                        @elseif ($lang == 'uk')
                            <img src="{{ asset('fromfigma/ukraine_flag.png') }}" alt="UK" class="mobile-flag">
                        @elseif ($lang == 'ru')
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#666"
                                stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="2" y1="12" x2="22" y2="12"></line>
                                <path
                                    d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z">
                                </path>
                            </svg>
                        @else
                            <img src="{{ asset('fromfigma/uk_flag.png') }}" alt="EN" class="mobile-flag">
                        @endif
                        <span>{{ strtoupper($lang) }}</span>
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="#000">
                            <path d="M7 10l5 5 5-5z" />
                        </svg>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        @foreach (['ru' => 'Русский', 'en' => 'English', 'cs' => 'Čeština', 'uk' => 'Українська'] as $code => $language)
                            <a href="{{ route('change.language', $code) }}"
                                class="dropdown-item {{ $lang == $code ? 'text-primary' : '' }}">{{ $language }}</a>
                        @endforeach
                    </div>
                </div>
                <div class="mobile-logo">
                    <img src="{{ asset('fromfigma/jobsi_mobile.png') }}" alt="JOBSI">
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="mobile-actions">
        {{-- Row 1: New Worker, New Housing, New Vehicle --}}
        <div class="mobile-actions-row">
            @can('create worker')
                <a href="#" data-url="{{ route('worker.create', ['redirect_to' => 'mobile']) }}" data-ajax-popup="true"
                    data-title="{{ __('Add New Worker') }}" data-size="lg" class="mobile-action-btn">
                    <div class="mobile-action-icon">
                        <img src="{{ asset('fromfigma/new_worker.svg') }}" alt="">
                    </div>
                    <span class="mobile-action-label">{{ __('НОВЫЙ') }}<br>{{ __('РАБОТНИК') }}</span>
                </a>
            @else
                <a href="{{ route('mobile.workers.index') }}" class="mobile-action-btn">
                    <div class="mobile-action-icon">
                        <img src="{{ asset('fromfigma/new_worker.svg') }}" alt="">
                    </div>
                    <span class="mobile-action-label">{{ __('НОВЫЙ') }}<br>{{ __('РАБОТНИК') }}</span>
                </a>
            @endcan

            @can('create room')
                <a href="#" data-url="{{ route('room.create') }}" data-ajax-popup="true"
                    data-title="{{ __('Add New Room') }}" data-size="md" class="mobile-action-btn">
                    <div class="mobile-action-icon">
                        <img src="{{ asset('fromfigma/new_hotel.svg') }}" alt="">
                    </div>
                    <span class="mobile-action-label">{{ __('НОВОЕ') }}<br>{{ __('ЖИЛЬЕ') }}</span>
                </a>
            @else
                <a href="{{ route('mobile.hotels.index') }}" class="mobile-action-btn">
                    <div class="mobile-action-icon">
                        <img src="{{ asset('fromfigma/new_hotel.svg') }}" alt="">
                    </div>
                    <span class="mobile-action-label">{{ __('НОВОЕ') }}<br>{{ __('ЖИЛЬЕ') }}</span>
                </a>
            @endcan

            @can('create vehicle')
                <a href="{{ route('vehicles.create') }}" class="mobile-action-btn">
                    <div class="mobile-action-icon">
                        <img src="{{ asset('fromfigma/new_car.svg') }}" alt="">
                    </div>
                    <span class="mobile-action-label">{{ __('НОВЫЙ') }}<br>{{ __('ТРАНСПОРТ') }}</span>
                </a>
            @else
                <a href="{{ route('mobile.vehicles.index') }}" class="mobile-action-btn">
                    <div class="mobile-action-icon">
                        <img src="{{ asset('fromfigma/new_car.svg') }}" alt="">
                    </div>
                    <span class="mobile-action-label">{{ __('НОВЫЙ') }}<br>{{ __('ТРАНСПОРТ') }}</span>
                </a>
            @endcan
        </div>

        {{-- Row 2: Cashbox In, Cashbox Out, Documents --}}
        <div class="mobile-actions-row">
            <a href="#" class="mobile-action-btn" data-bs-toggle="modal" data-bs-target="#mobileDepositModal">
                <div class="mobile-action-icon">
                    <img src="{{ asset('fromfigma/new_in.svg') }}" alt="">
                </div>
                <span class="mobile-action-label">{{ __('ВНЕСТИ В') }}<br>{{ __('КАССУ') }}</span>
            </a>

            <a href="#" class="mobile-action-btn" data-bs-toggle="modal" data-bs-target="#mobileWithdrawModal">
                <div class="mobile-action-icon">
                    <img src="{{ asset('fromfigma/new_out.svg') }}" alt="">
                </div>
                <span class="mobile-action-label">{{ __('ВЫДАТЬ ИЗ') }}<br>{{ __('КАССЫ') }}</span>
            </a>

            @can('document_generate')
                <a href="#" class="mobile-action-btn" data-bs-toggle="modal" data-bs-target="#mobileDocumentModal">
                    <div class="mobile-action-icon">
                        <img src="{{ asset('fromfigma/new_document.svg') }}" alt="">
                    </div>
                    <span class="mobile-action-label">{{ __('СГЕНЕРИРОВАТЬ') }}<br>{{ __('ДОКУМЕНТЫ') }}</span>
                </a>
            @else
                <a href="{{ route('mobile.documents.index') }}" class="mobile-action-btn">
                    <div class="mobile-action-icon">
                        <img src="{{ asset('fromfigma/new_document.svg') }}" alt="">
                    </div>
                    <span class="mobile-action-label">{{ __('СГЕНЕРИРОВАТЬ') }}<br>{{ __('ДОКУМЕНТЫ') }}</span>
                </a>
            @endcan
        </div>
    </div>

    <div class="mobile-content">
        {{-- Workplace Widget --}}
        <div class="mobile-widget">
            <div class="mobile-widget-header">
                <div class="dropdown">
                    <span class="mobile-filter-dropdown" data-bs-toggle="dropdown">
                        {{ $workplaceId ? $workplaces->find($workplaceId)?->name ?? __('все рабочие места') : __('все рабочие места') }}
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="#000">
                            <path d="M7 10l5 5 5-5z" />
                        </svg>
                    </span>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item"
                                href="{{ route('mobile.dashboard') }}">{{ __('все рабочие места') }}</a></li>
                        @foreach ($workplaces as $workplace)
                            <li><a class="dropdown-item"
                                    href="{{ route('mobile.dashboard', ['workplace_id' => $workplace->id]) }}">{{ $workplace->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="mobile-workplace-stats">
                <div class="mobile-stat-row">
                    <span class="mobile-stat-label">{{ __('Рабочих принято') }}:</span>
                    <span class="mobile-stat-num">{{ $workplaceStats['hired'] }}</span>
                    <div class="mobile-progress-green" style="width: {{ min($workplaceStats['hired'] * 0.8, 120) }}px;">
                    </div>
                </div>
                <div class="mobile-stat-row">
                    <span class="mobile-stat-label">{{ __('Рабочих уволено') }}:</span>
                    <span class="mobile-stat-num">{{ $workplaceStats['fired'] }}</span>
                    <div class="mobile-progress-red" style="width: {{ min($workplaceStats['fired'] * 0.8, 100) }}px;">
                    </div>
                </div>
            </div>

            <div class="mobile-fluctuation">
                <div class="mobile-fluctuation-left">
                    <span class="mobile-fluctuation-label">{{ __('ФЛУКТУАЦИЯ') }}</span>
                    @if ($workplaceStats['fluctuation'] > 50)
                        <span class="mobile-warning-icon">
                            <svg width="20" height="16" viewBox="0 0 26 21" fill="none">
                                <path
                                    d="M1.28333 20.5333C0.813876 20.5333 0.381893 20.2771 0.156938 19.865C-0.0680178 19.4529 -0.0500383 18.951 0.203818 18.556L11.7538 0.589358C11.9899 0.222042 12.3966 0 12.8333 0C13.2701 0 13.6767 0.222042 13.9129 0.589358L25.4629 18.556C25.7167 18.951 25.7347 19.4529 25.5097 19.865C25.2847 20.2771 24.8528 20.5333 24.3833 20.5333H1.28333Z"
                                    fill="#FA1228" />
                            </svg>
                            <span class="mobile-warning-exclamation">!</span>
                        </span>
                    @endif
                </div>
                <span class="mobile-fluctuation-value">{{ number_format($workplaceStats['fluctuation'], 1, ',', ' ') }}
                    %</span>
            </div>

            <div class="mobile-widget-footer">
                <div class="mobile-widget-footer-line"></div>
                <span class="mobile-widget-footer-text">{{ __('Статистика флуктуации за актуальный месяц') }}</span>
            </div>
        </div>

        {{-- Hotel Widget --}}
        <div class="mobile-widget">
            <div class="mobile-widget-header">
                <div class="dropdown">
                    <span class="mobile-filter-dropdown" data-bs-toggle="dropdown">
                        {{ $hotelId ? $hotels->find($hotelId)?->name ?? __('все отели') : __('все отели') }}
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="#000">
                            <path d="M7 10l5 5 5-5z" />
                        </svg>
                    </span>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('mobile.dashboard') }}">{{ __('все отели') }}</a>
                        </li>
                        @foreach ($hotels as $hotel)
                            <li><a class="dropdown-item"
                                    href="{{ route('mobile.dashboard', ['hotel_id' => $hotel->id]) }}">{{ $hotel->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="mobile-hotel-stats">
                <p class="mobile-hotel-stat">{{ __('Всего мест') }}: <span
                        class="mobile-hotel-num">{{ $hotelStats['total_spots'] }}</span></p>
                <p class="mobile-hotel-stat">
                    {{ __('Свободных мест') }}: <span class="mobile-hotel-num">{{ $hotelStats['free_spots'] }}</span>
                    @if ($hotelStats['free_spots'] < 10)
                        <span class="mobile-warning-icon">
                            <svg width="20" height="16" viewBox="0 0 26 21" fill="none">
                                <path
                                    d="M1.28333 20.5333C0.813876 20.5333 0.381893 20.2771 0.156938 19.865C-0.0680178 19.4529 -0.0500383 18.951 0.203818 18.556L11.7538 0.589358C11.9899 0.222042 12.3966 0 12.8333 0C13.2701 0 13.6767 0.222042 13.9129 0.589358L25.4629 18.556C25.7167 18.951 25.7347 19.4529 25.5097 19.865C25.2847 20.2771 24.8528 20.5333 24.3833 20.5333H1.28333Z"
                                    fill="#FA1228" />
                            </svg>
                            <span class="mobile-warning-exclamation">!</span>
                        </span>
                    @endif
                </p>
                <p class="mobile-hotel-stat">{{ __('Платят за жилье') }}: <span
                        class="mobile-hotel-num">{{ $hotelStats['pays_self'] }}</span></p>
                <p class="mobile-hotel-stat">{{ __('Проживают бесплатно') }}: <span
                        class="mobile-hotel-num">{{ $hotelStats['pays_free'] }}</span></p>
            </div>

            <div class="mobile-widget-footer">
                <div class="mobile-widget-footer-line"></div>
                <span class="mobile-widget-footer-text">{{ __('Статус проживания') }}</span>
            </div>
        </div>
    </div>

    {{-- Mobile Deposit Modal --}}
    @php
        $currentPeriod = \App\Models\CashPeriod::where('created_by', Auth::user()->creatorId())
            ->where('is_frozen', false)
            ->orderBy('created_at', 'desc')
            ->first();
        $canDeposit = Auth::user()->can('cashbox_deposit');
    @endphp

    @if ($canDeposit && $currentPeriod)
        <div class="modal fade" id="mobileDepositModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Deposit Money') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="mobileDepositForm">
                        @csrf
                        <input type="hidden" name="period_id" value="{{ $currentPeriod->id }}">
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Period') }}</label>
                                <input type="text" class="form-control" value="{{ $currentPeriod->name }}" disabled>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="amount" class="form-control" step="0.01"
                                        min="0.01" required placeholder="0.00">
                                    <span class="input-group-text">{{ getCashboxCurrencySymbol() }}</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('Comment') }}</label>
                                <textarea name="comment" class="form-control" rows="2" placeholder="{{ __('Optional comment...') }}"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary"
                                id="mobileDepositBtn">{{ __('Deposit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @else
        <div class="modal fade" id="mobileDepositModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Deposit Money') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <i class="ti ti-lock" style="font-size: 48px; color: #ccc;"></i>
                        <p class="mt-3 text-muted">{{ __('No active cashbox period or no permission') }}</p>
                        <a href="{{ route('mobile.cashbox.index') }}"
                            class="btn btn-primary btn-sm">{{ __('Go to Cashbox') }}</a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Mobile Withdraw/Distribute Modal --}}
    @php
        $canDistribute = Auth::user()->can('cashbox_distribute');
        $recipients = [];
        $cashboxBalance = ['available' => 0];
        if ($canDistribute && $currentPeriod) {
            $creatorId = Auth::user()->creatorId();
            // Get users (managers, curators)
            $users = \App\Models\User::where('created_by', $creatorId)
                ->whereIn('type', ['manager', 'curator'])
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'type' => 'App\\Models\\User',
                        'role' => $user->type,
                    ];
                });
            // Get workers
            $workers = \App\Models\Worker::where('created_by', $creatorId)
                ->get()
                ->map(function ($worker) {
                    return [
                        'id' => $worker->id,
                        'name' => $worker->first_name . ' ' . $worker->last_name,
                        'type' => 'App\\Models\\Worker',
                        'role' => 'worker',
                    ];
                });
            $recipients = $users->merge($workers);

            // Calculate available balance
            $received = \App\Models\CashTransaction::where('cash_period_id', $currentPeriod->id)
                ->where('recipient_id', Auth::id())
                ->where('recipient_type', 'App\\Models\\User')
                ->whereIn('status', ['completed', 'in_progress'])
                ->sum('amount');
            $sent = \App\Models\CashTransaction::where('cash_period_id', $currentPeriod->id)
                ->where('sender_id', Auth::id())
                ->where('sender_type', 'App\\Models\\User')
                ->sum('amount');
            $cashboxBalance['available'] = $received - $sent;
        }
    @endphp

    @if ($canDistribute && $currentPeriod)
        <div class="modal fade" id="mobileWithdrawModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Distribute Money') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="mobileDistributeForm">
                        @csrf
                        <input type="hidden" name="period_id" value="{{ $currentPeriod->id }}">
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Distribution Type') }} <span
                                        class="text-danger">*</span></label>
                                <select name="distribution_type" id="mobileDistributionType" class="form-control"
                                    required>
                                    <option value="">{{ __('Select type') }}</option>
                                    <option value="salary">{{ __('Employee Salary') }}</option>
                                    <option value="transfer">{{ __('Fund Transfer') }}</option>
                                </select>
                                <small class="text-muted" id="mobileDistributionTypeHint"></small>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Recipient') }} <span
                                        class="text-danger">*</span></label>
                                <select name="recipient" class="form-control" required>
                                    <option value="">{{ __('Select recipient') }}</option>
                                    @foreach ($recipients as $recipient)
                                        <option
                                            value="{{ $recipient['type'] === 'App\\Models\\Worker' ? 'worker' : 'user' }}_{{ $recipient['id'] }}"
                                            data-role="{{ $recipient['role'] }}">
                                            {{ $recipient['name'] }}
                                            ({{ $recipient['role'] === 'manager' ? __('Manager') : ($recipient['role'] === 'curator' ? __('Curator') : __('Worker')) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="amount" class="form-control" step="0.01"
                                        min="0.01" required placeholder="0.00">
                                    <span class="input-group-text">{{ getCashboxCurrencySymbol() }}</span>
                                </div>
                                <small class="text-muted">{{ __('Available:') }}
                                    {{ formatCashboxCurrency($cashboxBalance['available']) }}</small>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Task') }}</label>
                                <input type="text" name="task" class="form-control"
                                    placeholder="{{ __('Task description...') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('Comment') }}</label>
                                <textarea name="comment" class="form-control" rows="2" placeholder="{{ __('Additional information...') }}"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-success"
                                id="mobileDistributeBtn">{{ __('Distribute') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @else
        <div class="modal fade" id="mobileWithdrawModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Distribute Money') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <i class="ti ti-lock" style="font-size: 48px; color: #ccc;"></i>
                        <p class="mt-3 text-muted">{{ __('No active cashbox period or no permission') }}</p>
                        <a href="{{ route('mobile.cashbox.index') }}"
                            class="btn btn-primary btn-sm">{{ __('Go to Cashbox') }}</a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Mobile Document Generation Modal --}}
    @can('document_generate')
        @php
            $mobileDocWorkers = \App\Models\Worker::where('created_by', Auth::user()->creatorId())->get();
            $mobileDocTemplates = \App\Models\DocumentTemplate::where('created_by', Auth::user()->creatorId())
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        @endphp
        <div class="modal fade" id="mobileDocumentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Document Generation') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="mobileDocumentForm" method="POST" action="{{ route('worker.bulk.generate-documents') }}">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Worker') }} <span class="text-danger">*</span></label>
                                <select name="single_worker_id" id="mobileDocWorker" class="form-control" required>
                                    <option value="">{{ __('Select Worker') }}</option>
                                    @foreach ($mobileDocWorkers as $worker)
                                        <option value="{{ $worker->id }}">{{ $worker->first_name }}
                                            {{ $worker->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Document Template') }} <span
                                        class="text-danger">*</span></label>
                                <select name="template_id" id="mobileDocTemplate" class="form-control" required>
                                    <option value="">{{ __('Select Template') }}</option>
                                    @foreach ($mobileDocTemplates as $template)
                                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                                    @endforeach
                                </select>
                                @if ($mobileDocTemplates->isEmpty())
                                    <small class="text-muted">
                                        <a href="{{ route('documents.create') }}">{{ __('Create First Template') }}</a>
                                    </small>
                                @endif
                            </div>

                            <div id="mobileDocDynamicFields" class="mb-3"></div>

                            <div class="form-group">
                                <label class="form-label">{{ __('Format') }} <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="format" id="mobileFormatPdf"
                                            value="pdf" checked>
                                        <label class="form-check-label" for="mobileFormatPdf">
                                            <i class="ti ti-file-type-pdf text-danger me-1"></i>PDF
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="format" id="mobileFormatDocx"
                                            value="docx">
                                        <label class="form-check-label" for="mobileFormatDocx">
                                            <i class="ti ti-file-type-doc text-primary me-1"></i>Word
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="format" id="mobileFormatXlsx"
                                            value="xlsx">
                                        <label class="form-check-label" for="mobileFormatXlsx">
                                            <i class="ti ti-file-spreadsheet text-success me-1"></i>Excel
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-info" id="mobileDocGenerateBtn"
                                {{ $mobileDocTemplates->isEmpty() ? 'disabled' : '' }}>
                                <i class="ti ti-download me-1"></i>{{ __('Generate') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile Deposit Form
            var depositForm = document.getElementById('mobileDepositForm');
            if (depositForm) {
                depositForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    var submitBtn = document.getElementById('mobileDepositBtn');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-1"></span>{{ __('Loading...') }}';

                    var formData = new FormData(depositForm);

                    fetch('{{ route('cashbox.deposit') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content,
                                'Accept': 'application/json',
                            },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                show_toastr('success', data.message ||
                                    '{{ __('Deposit successful') }}');
                                bootstrap.Modal.getInstance(document.getElementById(
                                    'mobileDepositModal')).hide();
                                depositForm.reset();
                            } else {
                                show_toastr('error', data.error || '{{ __('An error occurred') }}');
                            }
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '{{ __('Deposit') }}';
                        })
                        .catch(error => {
                            show_toastr('error', '{{ __('An error occurred') }}');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '{{ __('Deposit') }}';
                        });
                });
            }

            // Mobile Distribute Form
            var distributeForm = document.getElementById('mobileDistributeForm');
            if (distributeForm) {
                distributeForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    var submitBtn = document.getElementById('mobileDistributeBtn');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-1"></span>{{ __('Loading...') }}';

                    var formData = new FormData(distributeForm);

                    // Parse recipient field
                    var recipientVal = formData.get('recipient');
                    if (recipientVal) {
                        var parts = recipientVal.split('_');
                        formData.set('recipient_type', parts[0]);
                        formData.set('recipient_id', parts[1]);
                        formData.delete('recipient');
                    }

                    fetch('{{ route('cashbox.distribute') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content,
                                'Accept': 'application/json',
                            },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                show_toastr('success', data.message ||
                                    '{{ __('Distribution successful') }}');
                                bootstrap.Modal.getInstance(document.getElementById(
                                    'mobileWithdrawModal')).hide();
                                distributeForm.reset();
                                // Reset hint
                                var hint = document.getElementById('mobileDistributionTypeHint');
                                if (hint) hint.textContent = '';
                            } else {
                                show_toastr('error', data.error || '{{ __('An error occurred') }}');
                            }
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '{{ __('Distribute') }}';
                        })
                        .catch(error => {
                            show_toastr('error', '{{ __('An error occurred') }}');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '{{ __('Distribute') }}';
                        });
                });
            }

            // Distribution type hint
            var distributionTypeSelect = document.getElementById('mobileDistributionType');
            var distributionTypeHint = document.getElementById('mobileDistributionTypeHint');

            if (distributionTypeSelect && distributionTypeHint) {
                distributionTypeSelect.addEventListener('change', function() {
                    var value = this.value;
                    if (value === 'salary') {
                        distributionTypeHint.textContent =
                            '{{ __('Final salary payment. Transaction will be completed immediately.') }}';
                    } else if (value === 'transfer') {
                        distributionTypeHint.textContent =
                            '{{ __('Money transfer for further distribution to other employees.') }}';
                    } else {
                        distributionTypeHint.textContent = '';
                    }
                });
            }

            // Document template dynamic fields
            var mobileDocTemplateSelect = document.getElementById('mobileDocTemplate');
            var mobileDocDynamicFields = document.getElementById('mobileDocDynamicFields');

            if (mobileDocTemplateSelect && mobileDocDynamicFields) {
                mobileDocTemplateSelect.addEventListener('change', function() {
                    var templateId = this.value;
                    mobileDocDynamicFields.innerHTML = '';

                    if (!templateId) return;

                    fetch('{{ url('/documents/template-fields') }}/' + templateId)
                        .then(response => response.json())
                        .then(data => {
                            if (data.fields && data.fields.length > 0) {
                                data.fields.forEach(function(field) {
                                    var fieldHtml = '<div class="form-group mb-3">' +
                                        '<label class="form-label">' + field.label +
                                        '</label>' +
                                        '<input type="date" name="' + field.field_name +
                                        '" class="form-control" required>' +
                                        '</div>';
                                    mobileDocDynamicFields.innerHTML += fieldHtml;
                                });
                            }
                        })
                        .catch(error => console.error('Error:', error));
                });
            }
        });
    </script>
@endpush
