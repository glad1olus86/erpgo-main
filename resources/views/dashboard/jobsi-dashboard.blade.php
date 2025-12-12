@extends('layouts.admin')

@section('page-title')
    {{ __('Главная панель') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active">{{ __('Главная панель') }}</li>
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{ asset('css/jobsi-theme.css') }}">
@endpush

@section('content')
    <div class="jobsi-dashboard-wrapper">
        <!-- Quick Actions - TOP -->
        <div class="jobsi-quick-actions mb-3">
            <a href="#" data-url="{{ route('worker.create') }}" data-ajax-popup="true" 
                data-title="{{ __('Добавить нового работника') }}" data-size="lg" class="jobsi-action-btn">
                <img src="{{ asset('fromfigma/new_worker.svg') }}" alt="">
                <span>{{ __('НОВЫЙ') }}<br>{{ __('РАБОТНИК') }}</span>
            </a>
            <a href="#" data-url="{{ route('room.create') }}" data-ajax-popup="true"
                data-title="{{ __('Добавить новую комнату') }}" data-size="lg" class="jobsi-action-btn">
                <img src="{{ asset('fromfigma/new_hotel.svg') }}" alt="">
                <span>{{ __('НОВОЕ') }}<br>{{ __('ЖИЛЬЕ') }}</span>
            </a>
            <a href="{{ route('vehicles.create') }}" class="jobsi-action-btn">
                <img src="{{ asset('fromfigma/new_car.svg') }}" alt="">
                <span>{{ __('НОВЫЙ') }}<br>{{ __('ТРАНСПОРТ') }}</span>
            </a>
            @if($isBoss && $currentPeriod)
            <a href="#" class="jobsi-action-btn" data-bs-toggle="modal" data-bs-target="#dashboardDepositModal">
                <img src="{{ asset('fromfigma/new_in.svg') }}" alt="">
                <span>{{ __('ВНЕСТИ В') }}<br>{{ __('КАССУ') }}</span>
            </a>
        @else
            <a href="{{ route('cashbox.index') }}" class="jobsi-action-btn">
                <img src="{{ asset('fromfigma/new_in.svg') }}" alt="">
                <span>{{ __('ВНЕСТИ В') }}<br>{{ __('КАССУ') }}</span>
            </a>
        @endif
        @if($canDistribute && $currentPeriod)
            <a href="#" class="jobsi-action-btn" data-bs-toggle="modal" data-bs-target="#dashboardDistributeModal">
                <img src="{{ asset('fromfigma/new_out.svg') }}" alt="">
                <span>{{ __('ВЫДАТЬ ИЗ') }}<br>{{ __('КАССЫ') }}</span>
            </a>
        @else
            <a href="{{ route('cashbox.index') }}" class="jobsi-action-btn">
                <img src="{{ asset('fromfigma/new_out.svg') }}" alt="">
                <span>{{ __('ВЫДАТЬ ИЗ') }}<br>{{ __('КАССЫ') }}</span>
            </a>
        @endif
        @can('document_generate')
            <a href="#" class="jobsi-action-btn" data-bs-toggle="modal" data-bs-target="#dashboardDocumentModal">
                <img src="{{ asset('fromfigma/new_document.svg') }}" alt="">
                <span>{{ __('СГЕНЕРИРОВАТЬ') }}<br>{{ __('ДОКУМЕНТЫ') }}</span>
            </a>
        @else
            <a href="{{ route('documents.index') }}" class="jobsi-action-btn">
                <img src="{{ asset('fromfigma/new_document.svg') }}" alt="">
                <span>{{ __('СГЕНЕРИРОВАТЬ') }}<br>{{ __('ДОКУМЕНТЫ') }}</span>
            </a>
        @endcan
        </div>

        <!-- Widgets Row -->
        <div class="row">
            <!-- Accommodation Widget -->
            <div class="col-lg-6 col-12 mb-3">
                <div class="jobsi-widget jobsi-widget-hotel">
                    <div class="jobsi-widget-header">
                        <div class="dropdown">
                            <span class="jobsi-filter-dropdown" data-bs-toggle="dropdown">
                                {{ $hotelId ? $hotels->find($hotelId)?->name ?? __('все отели') : __('все отели') }}
                                <svg width="25" height="25" viewBox="0 0 24 24" fill="#000">
                                    <path d="M7 10l5 5 5-5z" />
                                </svg>
                            </span>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item"
                                        href="{{ route('jobsi.dashboard') }}">{{ __('все отели') }}</a></li>
                                @foreach ($hotels as $hotel)
                                    <li><a class="dropdown-item"
                                            href="{{ route('jobsi.dashboard', ['hotel_id' => $hotel->id]) }}">{{ $hotel->name }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="jobsi-widget-body">
                        <div class="jobsi-hotel-stats">
                            <p class="jobsi-stat-row">
                                {{ __('Всего мест') }}: <span class="jobsi-stat-num">{{ $hotelStats['total_spots'] }}</span>
                            </p>
                            <p class="jobsi-stat-row">
                                {{ __('Свободных мест') }}: <span class="jobsi-stat-num">{{ $hotelStats['free_spots'] }}</span>
                                @if ($hotelStats['free_spots'] < 10)
                                    <span class="jobsi-warning-icon">
                                        <svg width="26" height="21" viewBox="0 0 26 21" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.28333 20.5333C0.813876 20.5333 0.381893 20.2771 0.156938 19.865C-0.0680178 19.4529 -0.0500383 18.951 0.203818 18.556L11.7538 0.589358C11.9899 0.222042 12.3966 0 12.8333 0C13.2701 0 13.6767 0.222042 13.9129 0.589358L25.4629 18.556C25.7167 18.951 25.7347 19.4529 25.5097 19.865C25.2847 20.2771 24.8528 20.5333 24.3833 20.5333H1.28333Z" fill="#FA1228"/></svg>
                                        <span class="jobsi-warning-exclamation">!</span>
                                    </span>
                                @endif
                            </p>
                            <p class="jobsi-stat-row">
                                {{ __('Платят за жилье') }}: <span class="jobsi-stat-num">{{ $hotelStats['pays_self'] }}</span>
                            </p>
                            <p class="jobsi-stat-row">
                                {{ __('Проживают бесплатно') }}: <span class="jobsi-stat-num">{{ $hotelStats['pays_free'] }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="jobsi-widget-bg">
                        <img src="{{ asset('fromfigma/hotel.svg') }}" alt="">
                    </div>

                    <div class="jobsi-widget-footer-line"></div>
                    <div class="jobsi-widget-footer-text">{{ __('Статус проживания') }}</div>
                </div>
            </div>

            <!-- Workplace Widget -->
            <div class="col-lg-6 col-12 mb-3">
                <div class="jobsi-widget jobsi-widget-workplace">
                    <div class="jobsi-widget-header">
                        <div class="dropdown">
                            <span class="jobsi-filter-dropdown" data-bs-toggle="dropdown">
                                {{ $workplaceId ? $workplaces->find($workplaceId)?->name ?? __('все рабочие места') : __('все рабочие места') }}
                                <svg width="25" height="25" viewBox="0 0 24 24" fill="#000">
                                    <path d="M7 10l5 5 5-5z" />
                                </svg>
                            </span>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item"
                                        href="{{ route('jobsi.dashboard', ['month' => $month]) }}">{{ __('все рабочие места') }}</a>
                                </li>
                                @foreach ($workplaces as $workplace)
                                    <li><a class="dropdown-item"
                                            href="{{ route('jobsi.dashboard', ['workplace_id' => $workplace->id, 'month' => $month]) }}">{{ $workplace->name }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="jobsi-widget-body">
                        <div class="jobsi-workplace-stats">
                            <div class="jobsi-workplace-row">
                                <span class="jobsi-workplace-label">{{ __('Рабочих принято') }}:</span>
                                <span class="jobsi-workplace-num">{{ $workplaceStats['hired'] }}</span>
                                <div class="jobsi-progress-green" style="width: {{ min($workplaceStats['hired'] * 10, 225) }}px;"></div>
                            </div>
                            <div class="jobsi-workplace-row">
                                <span class="jobsi-workplace-label">{{ __('Рабочих уволено') }}:</span>
                                <span class="jobsi-workplace-num">{{ $workplaceStats['fired'] }}</span>
                                <div class="jobsi-progress-red" style="width: {{ min($workplaceStats['fired'] * 10, 157) }}px;"></div>
                            </div>
                        </div>

                        <div class="jobsi-fluctuation-block">
                            <div class="jobsi-fluctuation-left">
                                <span class="jobsi-fluctuation-label">{{ __('ФЛУКТУАЦИЯ') }}</span>
                                @if ($workplaceStats['fluctuation'] > 50)
                                    <span class="jobsi-warning-icon">
                                        <svg width="26" height="21" viewBox="0 0 26 21" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.28333 20.5333C0.813876 20.5333 0.381893 20.2771 0.156938 19.865C-0.0680178 19.4529 -0.0500383 18.951 0.203818 18.556L11.7538 0.589358C11.9899 0.222042 12.3966 0 12.8333 0C13.2701 0 13.6767 0.222042 13.9129 0.589358L25.4629 18.556C25.7167 18.951 25.7347 19.4529 25.5097 19.865C25.2847 20.2771 24.8528 20.5333 24.3833 20.5333H1.28333Z" fill="#FA1228"/></svg>
                                        <span class="jobsi-warning-exclamation">!</span>
                                    </span>
                                @endif
                            </div>
                            <span class="jobsi-fluctuation-value">{{ number_format($workplaceStats['fluctuation'], 1, ',', ' ') }}%</span>
                        </div>
                    </div>

                    <div class="jobsi-widget-bg">
                        <img src="{{ asset('fromfigma/workplaces.svg') }}" alt="">
                    </div>

                    <div class="jobsi-widget-footer-line"></div>
                    <div class="jobsi-widget-footer-text">{{ __('Статистика флуктуации за актуальный месяц') }}</div>
                </div>
            </div>
        </div>

        <!-- Month Tabs -->
        <div class="jobsi-month-tabs">
            @foreach ($months as $m)
                <a href="{{ route('jobsi.dashboard', ['month' => $m['value'], 'hotel_id' => $hotelId, 'workplace_id' => $workplaceId]) }}"
                    class="jobsi-month-tab {{ $month == $m['value'] || (!$month && $loop->first) ? 'active' : '' }}">
                    {{ $m['label'] }}
                </a>
            @endforeach
        </div>

        <!-- Cashbox Widget - LARGE -->
        <div class="row">
            <div class="col-12">
                <div class="jobsi-widget jobsi-widget-cashbox-large">
                    <div class="jobsi-widget-header">
                        <span class="jobsi-filter-dropdown">
                            {{ __('касса всей фирмы') }}
                        </span>
                    </div>

                    <div class="jobsi-cashbox-content-large">
                        <div class="jobsi-cashbox-left-large">
                            <div class="jobsi-cashbox-balance-row-large">
                                <span
                                    class="jobsi-cashbox-balance-label-large">{{ __('Актуальный остаток в кассе') }}:</span>
                                <span
                                    class="jobsi-cashbox-balance-value-large">{{ number_format($cashboxStats['balance'], 0, ',', ' ') }}</span>
                                <span class="jobsi-cashbox-balance-currency-large">Kč</span>
                            </div>
                            <div class="jobsi-cashbox-expenses-large">
                                <div class="jobsi-cashbox-expense-row-large">
                                    <span class="jobsi-cashbox-expense-label-large">{{ __('Выдано на зарплаты') }}:</span>
                                    <div class="jobsi-cashbox-expense-amount-large">
                                        <span
                                            class="jobsi-cashbox-expense-value-large">{{ number_format($cashboxStats['salaries'], 0, ',', ' ') }}</span>
                                        <span class="jobsi-cashbox-expense-currency-large">Kč</span>
                                    </div>
                                </div>
                                <div class="jobsi-cashbox-expense-row-large">
                                    <span class="jobsi-cashbox-expense-label-large">{{ __('Траты на транспорт') }}:</span>
                                    <div class="jobsi-cashbox-expense-amount-large">
                                        <span
                                            class="jobsi-cashbox-expense-value-large">{{ number_format($cashboxStats['transport'], 0, ',', ' ') }}</span>
                                        <span class="jobsi-cashbox-expense-currency-large">Kč</span>
                                    </div>
                                </div>
                                <div class="jobsi-cashbox-expense-row-large">
                                    <span class="jobsi-cashbox-expense-label-large">{{ __('Другие затраты') }}:</span>
                                    <div class="jobsi-cashbox-expense-amount-large">
                                        <span
                                            class="jobsi-cashbox-expense-value-large">{{ number_format($cashboxStats['other'], 0, ',', ' ') }}</span>
                                        <span class="jobsi-cashbox-expense-currency-large">Kč</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="jobsi-cashbox-right-large">
                            <div class="jobsi-cashbox-chart-wrapper">
                                <div class="jobsi-cashbox-chart-area">
                                    <div class="jobsi-cashbox-legend-large">
                                        <div class="jobsi-cashbox-legend-item-large">
                                            <div class="jobsi-cashbox-legend-dot-large light"></div>
                                            <span>{{ __('вся сумма денежных') }}<br>{{ __('оборотов за месяц') }}</span>
                                        </div>
                                        <div class="jobsi-cashbox-legend-item-large">
                                            <div class="jobsi-cashbox-legend-dot-large dark"></div>
                                            <span>{{ __('остаток кассы после') }}<br>{{ __('окончания месяца') }}</span>
                                        </div>
                                    </div>
                                    <div class="jobsi-cashbox-chart-large">
                                        @php
                                            $maxTurnover = collect($chartData)->max('turnover') ?: 1;
                                        @endphp
                                        @foreach ($chartData as $index => $data)
                                            @php
                                                $turnoverHeight = $maxTurnover > 0 ? ($data['turnover'] / $maxTurnover) * 140 : 20;
                                                $balanceHeight = $maxTurnover > 0 ? ($data['balance'] / $maxTurnover) * 140 : 10;
                                            @endphp
                                            <div class="jobsi-cashbox-bar-group-large">
                                                <div class="jobsi-cashbox-bars-large">
                                                    <div class="jobsi-cashbox-bar-light-large" style="height: {{ max($turnoverHeight, 15) }}px;"></div>
                                                    <div class="jobsi-cashbox-bar-dark-large" style="height: {{ max($balanceHeight, 10) }}px;"></div>
                                                </div>
                                                <span class="jobsi-cashbox-bar-month-large">{{ $index + 1 }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="jobsi-cashbox-coins-icon">
                                    <img src="{{ asset('fromfigma/cashbox.svg') }}" alt="">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="jobsi-widget-footer-line"></div>
                    <div class="jobsi-widget-footer-text">{{ __('Отчет кассовых операций за актуальный месяц') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Deposit Modal (only for Boss) --}}
    @if($isBoss && $currentPeriod)
        <div class="modal fade" id="dashboardDepositModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Внести деньги в кассу') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="dashboardDepositForm">
                        @csrf
                        <input type="hidden" name="period_id" value="{{ $currentPeriod->id }}">
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Период') }}</label>
                                <input type="text" class="form-control" value="{{ $currentPeriod->name }}" disabled>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Сумма') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required placeholder="0.00">
                                    <span class="input-group-text">{{ getCashboxCurrencySymbol() }}</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('Комментарий') }}</label>
                                <textarea name="comment" class="form-control" rows="2" placeholder="{{ __('Необязательный комментарий...') }}"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Отмена') }}</button>
                            <button type="submit" class="btn btn-primary" id="dashboardDepositSubmitBtn">{{ __('Внести') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Distribute Modal --}}
    @if($canDistribute && $currentPeriod)
        <div class="modal fade" id="dashboardDistributeModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Выдать деньги из кассы') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="dashboardDistributeForm">
                        @csrf
                        <input type="hidden" name="period_id" value="{{ $currentPeriod->id }}">
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Тип выдачи') }} <span class="text-danger">*</span></label>
                                <select name="distribution_type" id="dashboardDistributionType" class="form-control" required>
                                    <option value="">{{ __('Выберите тип выдачи') }}</option>
                                    <option value="salary">{{ __('Зарплата сотруднику') }}</option>
                                    <option value="transfer">{{ __('Передача средств') }}</option>
                                </select>
                                <small class="text-muted" id="dashboardDistributionTypeHint"></small>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Получатель') }} <span class="text-danger">*</span></label>
                                <select name="recipient" class="form-control" required>
                                    <option value="">{{ __('Выберите получателя') }}</option>
                                    @foreach($recipients as $recipient)
                                        @if(!isset($recipient['is_self']))
                                            <option value="{{ $recipient['type'] === 'App\\Models\\Worker' ? 'worker' : 'user' }}_{{ $recipient['id'] }}" data-role="{{ $recipient['role'] }}">
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
                                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required placeholder="0.00">
                                    <span class="input-group-text">{{ getCashboxCurrencySymbol() }}</span>
                                </div>
                                <small class="text-muted">{{ __('Доступно:') }} {{ formatCashboxCurrency($cashboxBalance['available']) }}</small>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Задача') }}</label>
                                <input type="text" name="task" class="form-control" placeholder="{{ __('Описание задачи...') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('Комментарий') }}</label>
                                <textarea name="comment" class="form-control" rows="2" placeholder="{{ __('Дополнительная информация...') }}"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Отмена') }}</button>
                            <button type="submit" class="btn btn-success" id="dashboardDistributeSubmitBtn">{{ __('Выдать') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Document Generation Modal --}}
    @can('document_generate')
    @php
        $docWorkers = \App\Models\Worker::where('created_by', Auth::user()->creatorId())->get();
        $docTemplates = \App\Models\DocumentTemplate::where('created_by', Auth::user()->creatorId())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    @endphp
    <div class="modal fade" id="dashboardDocumentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-file-text me-2"></i>{{ __('Генерация документа') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="dashboardDocumentForm" method="POST" action="{{ route('worker.bulk.generate-documents') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label class="form-label">{{ __('Работник') }} <span class="text-danger">*</span></label>
                            <select name="single_worker_id" id="dashboardDocWorker" class="form-control" required>
                                <option value="">{{ __('Выберите работника') }}</option>
                                @foreach($docWorkers as $worker)
                                    <option value="{{ $worker->id }}">{{ $worker->first_name }} {{ $worker->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">{{ __('Шаблон документа') }} <span class="text-danger">*</span></label>
                            <select name="template_id" id="dashboardDocTemplate" class="form-control" required>
                                <option value="">{{ __('Выберите шаблон') }}</option>
                                @foreach($docTemplates as $template)
                                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                                @endforeach
                            </select>
                            @if($docTemplates->isEmpty())
                                <small class="text-muted">
                                    <a href="{{ route('documents.create') }}">{{ __('Создать первый шаблон') }}</a>
                                </small>
                            @endif
                        </div>
                        
                        <div id="dashboardDocDynamicFields" class="mb-3"></div>
                        
                        <div class="form-group">
                            <label class="form-label">{{ __('Формат') }} <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="format" id="dashboardFormatPdf" value="pdf" checked>
                                    <label class="form-check-label" for="dashboardFormatPdf">
                                        <i class="ti ti-file-type-pdf text-danger me-1"></i>PDF
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="format" id="dashboardFormatDocx" value="docx">
                                    <label class="form-check-label" for="dashboardFormatDocx">
                                        <i class="ti ti-file-type-doc text-primary me-1"></i>Word
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="format" id="dashboardFormatXlsx" value="xlsx">
                                    <label class="form-check-label" for="dashboardFormatXlsx">
                                        <i class="ti ti-file-spreadsheet text-success me-1"></i>Excel
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Отмена') }}</button>
                        <button type="submit" class="btn btn-info" id="dashboardDocGenerateBtn" {{ $docTemplates->isEmpty() ? 'disabled' : '' }}>
                            <i class="ti ti-download me-1"></i>{{ __('Сгенерировать') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan
@endsection

@push('script-page')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // Deposit form handler
    var depositForm = document.getElementById('dashboardDepositForm');
    if (depositForm) {
        depositForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            var submitBtn = document.getElementById('dashboardDepositSubmitBtn');
            var originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>{{ __("Загрузка...") }}';
            
            var formData = new FormData(depositForm);
            
            fetch('{{ route("cashbox.deposit") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    show_toastr('success', data.message);
                    var modal = bootstrap.Modal.getInstance(document.getElementById('dashboardDepositModal'));
                    if (modal) modal.hide();
                    depositForm.reset();
                    // Reload page to update stats
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    show_toastr('error', data.error || '{{ __("Произошла ошибка") }}');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                show_toastr('error', '{{ __("Произошла ошибка") }}');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }

    // Distribute form handler
    var distributeForm = document.getElementById('dashboardDistributeForm');
    if (distributeForm) {
        distributeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            var submitBtn = document.getElementById('dashboardDistributeSubmitBtn');
            var originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>{{ __("Загрузка...") }}';
            
            var formData = new FormData(distributeForm);
            
            // Parse recipient field
            var recipientVal = formData.get('recipient');
            if (recipientVal) {
                var parts = recipientVal.split('_');
                formData.set('recipient_type', parts[0]);
                formData.set('recipient_id', parts[1]);
                formData.delete('recipient');
            }
            
            fetch('{{ route("cashbox.distribute") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    show_toastr('success', data.message);
                    var modal = bootstrap.Modal.getInstance(document.getElementById('dashboardDistributeModal'));
                    if (modal) modal.hide();
                    distributeForm.reset();
                    // Reload page to update stats
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    show_toastr('error', data.error || '{{ __("Произошла ошибка") }}');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                show_toastr('error', '{{ __("Произошла ошибка") }}');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }

    // Distribution type hint
    var distributionTypeSelect = document.getElementById('dashboardDistributionType');
    var distributionTypeHint = document.getElementById('dashboardDistributionTypeHint');
    
    if (distributionTypeSelect && distributionTypeHint) {
        distributionTypeSelect.addEventListener('change', function() {
            var value = this.value;
            if (value === 'salary') {
                distributionTypeHint.textContent = '{{ __("Конечная выплата зарплаты. Транзакция будет сразу завершена.") }}';
            } else if (value === 'transfer') {
                distributionTypeHint.textContent = '{{ __("Передача денег для дальнейшего распределения другим сотрудникам.") }}';
            } else {
                distributionTypeHint.textContent = '';
            }
        });
    }
    
    // Document template dynamic fields
    var docTemplateSelect = document.getElementById('dashboardDocTemplate');
    var docDynamicFields = document.getElementById('dashboardDocDynamicFields');
    
    if (docTemplateSelect && docDynamicFields) {
        docTemplateSelect.addEventListener('change', function() {
            var templateId = this.value;
            docDynamicFields.innerHTML = '';
            
            if (!templateId) return;
            
            fetch('{{ url("/documents/template-fields") }}/' + templateId)
                .then(response => response.json())
                .then(data => {
                    if (data.fields && data.fields.length > 0) {
                        data.fields.forEach(function(field) {
                            var fieldHtml = '<div class="form-group mb-3">' +
                                '<label class="form-label">' + field.label + '</label>' +
                                '<input type="date" name="' + field.field_name + '" class="form-control" required>' +
                                '</div>';
                            docDynamicFields.innerHTML += fieldHtml;
                        });
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    }
});
</script>
@endpush
