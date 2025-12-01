@extends('layouts.admin')

@section('page-title')
    {{ __('Настройки кассы') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('cashbox.index') }}">{{ __('Касса') }}</a></li>
    <li class="breadcrumb-item">{{ __('Настройки') }}</li>
@endsection

@section('content')
    <div class="row">
        {{-- Currency Settings --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="ti ti-currency-euro me-2"></i>{{ __('Валюта') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('cashbox.settings.save') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="form-label">{{ __('Валюта кассы') }}</label>
                            <select name="cashbox_currency" class="form-control">
                                <option value="EUR" {{ $currentCurrency === 'EUR' ? 'selected' : '' }}>€ EUR (Евро)</option>
                                <option value="USD" {{ $currentCurrency === 'USD' ? 'selected' : '' }}>$ USD (Доллар США)</option>
                                <option value="PLN" {{ $currentCurrency === 'PLN' ? 'selected' : '' }}>zł PLN (Польский злотый)</option>
                                <option value="CZK" {{ $currentCurrency === 'CZK' ? 'selected' : '' }}>Kč CZK (Чешская крона)</option>
                            </select>
                            <small class="text-muted">{{ __('Выберите валюту для отображения сумм в кассе') }}</small>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>{{ __('Сохранить') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Debug Tools (only for Boss) --}}
        @if($isBoss)
        <div class="col-lg-6">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="ti ti-bug me-2"></i>{{ __('Инструменты отладки') }}</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-triangle me-1"></i>
                        {{ __('Внимание! Эти действия необратимы и предназначены только для тестирования.') }}
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">{{ __('Текущий период') }}</label>
                        <p class="mb-2">
                            <strong>{{ $currentPeriod->name }}</strong>
                            <br>
                            <small class="text-muted">
                                {{ __('Внесено:') }} {{ number_format($currentPeriod->total_deposited, 2, ',', ' ') }} {{ $currentCurrency }}
                            </small>
                        </p>
                    </div>

                    <form action="{{ route('cashbox.reset-period') }}" method="POST" 
                          onsubmit="return confirm('{{ __('Вы уверены? Все транзакции текущего месяца будут удалены!') }}');">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="ti ti-trash me-1"></i>{{ __('Сбросить текущий месяц') }}
                        </button>
                    </form>
                    <small class="text-muted d-block mt-2">
                        {{ __('Удаляет все транзакции и сбрасывает баланс текущего периода') }}
                    </small>
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection
