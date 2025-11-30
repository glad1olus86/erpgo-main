@extends('layouts.admin')

@section('page-title')
    {{ __('Конструктор уведомлений') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('settings') }}">{{ __('Settings') }}</a></li>
    <li class="breadcrumb-item">{{ __('Конструктор уведомлений') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="#" data-url="{{ route('notification-rules.create') }}" data-ajax-popup="true"
            data-title="{{ __('Создать правило уведомления') }}" data-size="lg"
            class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i> {{ __('Создать правило') }}
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Правила уведомлений') }}</h5>
                <small class="text-muted">{{ __('Настройте автоматические уведомления для вашей компании') }}</small>
            </div>
            <div class="card-body">
                @if($rules->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Статус') }}</th>
                                    <th>{{ __('Название') }}</th>
                                    <th>{{ __('Правило') }}</th>
                                    <th>{{ __('Период') }}</th>
                                    <th>{{ __('Тип') }}</th>
                                    <th>{{ __('Действия') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rules as $rule)
                                    <tr class="{{ !$rule->is_active ? 'text-muted' : '' }}">
                                        <td>
                                            <form action="{{ route('notification-rules.toggle', $rule->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $rule->is_active ? 'btn-success' : 'btn-secondary' }}" 
                                                    data-bs-toggle="tooltip" title="{{ $rule->is_active ? __('Выключить') : __('Включить') }}">
                                                    <i class="ti {{ $rule->is_active ? 'ti-check' : 'ti-x' }}"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <strong>{{ $rule->name }}</strong>
                                            @if($rule->is_grouped)
                                                <span class="badge bg-secondary ms-1" data-bs-toggle="tooltip" title="{{ __('Группировка включена') }}">
                                                    <i class="ti ti-stack"></i>
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @include('notification_rules.partials.rule_display', ['rule' => $rule])
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $rule->period_text }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $rule->severity_info['color'] }}">
                                                <i class="ti {{ $rule->severity_info['icon'] }} me-1"></i>
                                                {{ $rule->severity_info['label'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="#" data-url="{{ route('notification-rules.edit', $rule->id) }}" 
                                                    data-ajax-popup="true" data-title="{{ __('Редактировать правило') }}" data-size="lg"
                                                    class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="{{ __('Редактировать') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['notification-rules.destroy', $rule->id], 'class' => 'd-inline']) !!}
                                                <a href="#" class="btn btn-sm btn-danger bs-pass-para" 
                                                    data-bs-toggle="tooltip" title="{{ __('Удалить') }}"
                                                    data-confirm="{{ __('Вы уверены?') . '|' . __('Это действие удалит правило уведомления.') }}"
                                                    data-confirm-yes="$(this).closest('form').submit();">
                                                    <i class="ti ti-trash text-white"></i>
                                                </a>
                                                {!! Form::close() !!}
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="ti ti-bell-off" style="font-size: 48px; color: #ccc;"></i>
                        <p class="text-muted mt-3">{{ __('Нет настроенных правил уведомлений') }}</p>
                        <a href="#" data-url="{{ route('notification-rules.create') }}" data-ajax-popup="true"
                            data-title="{{ __('Создать правило уведомления') }}" data-size="lg"
                            class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i>{{ __('Создать первое правило') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
