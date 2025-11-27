@extends('layouts.admin')

@section('page-title')
    {{ __('Аудит системы') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Аудит') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    {{-- Фильтры --}}
                    <form action="{{ route('audit.index') }}" method="GET" class="mb-3">
                        <div class="row align-items-end">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="start_date" class="form-label">{{ __('Начальная дата') }}</label>
                                    <input type="date" class="form-control" name="start_date" id="start_date"
                                        value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="end_date" class="form-label">{{ __('Конечная дата') }}</label>
                                    <input type="date" class="form-control" name="end_date" id="end_date"
                                        value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="user_id" class="form-label">{{ __('Пользователь') }}</label>
                                    <select name="user_id" id="user_id" class="form-control select2">
                                        <option value="">{{ __('Все пользователи') }}</option>
                                        @foreach ($users as $id => $name)
                                            <option value="{{ $id }}"
                                                {{ request('user_id') == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="event_type" class="form-label">{{ __('Тип события') }}</label>
                                    <select name="event_type" id="event_type" class="form-control select2">
                                        <option value="">{{ __('Все события') }}</option>
                                        @foreach ($eventTypes as $key => $label)
                                            <option value="{{ $key }}"
                                                {{ request('event_type') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-filter"></i> {{ __('Применить') }}
                                    </button>
                                    <a href="{{ route('audit.index') }}" class="btn btn-secondary">
                                        <i class="ti ti-refresh"></i> {{ __('Сбросить') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- Переключатель режимов --}}
                    <div class="row">
                        <div class="col-md-9"></div>
                        <div class="col-md-9">
                            <ul class="nav nav-pills mb-5 gap-2" id="pills-tab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link px-4 {{ request('tab') != 'calendar' ? 'active' : '' }}"
                                        id="pills-list-tab" data-bs-toggle="pill" data-bs-target="#pills-list"
                                        type="button" role="tab" aria-controls="pills-list"
                                        aria-selected="{{ request('tab') != 'calendar' ? 'true' : 'false' }}">
                                        <i class="ti ti-list me-1"></i>{{ __('Список') }}
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link px-3 {{ request('tab') == 'calendar' ? 'active' : '' }}"
                                        id="pills-calendar-tab" data-bs-toggle="pill" data-bs-target="#pills-calendar"
                                        type="button" role="tab" aria-controls="pills-calendar"
                                        aria-selected="{{ request('tab') == 'calendar' ? 'true' : 'false' }}">
                                        <i class="ti ti-calendar me-1"></i>{{ __('Календарь') }}
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="tab-content" id="pills-tabContent">
                        {{-- Режим списка --}}
                        <div class="tab-pane fade {{ request('tab') != 'calendar' ? 'show active' : '' }}" id="pills-list"
                            role="tabpanel" aria-labelledby="pills-list-tab">
                            @include('audit_log.partials.list_view')
                        </div>

                        {{-- Режим календаря --}}
                        <div class="tab-pane fade {{ request('tab') == 'calendar' ? 'show active' : '' }}"
                            id="pills-calendar" role="tabpanel" aria-labelledby="pills-calendar-tab">
                            @include('audit_log.partials.calendar_view')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
