@extends('layouts.admin')

@section('page-title')
    {{ __('Редактировать автомобиль') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('vehicles.index') }}">{{ __('Транспорт') }}</a></li>
    <li class="breadcrumb-item">{{ __('Редактировать') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Информация об автомобиле') }}</h5>
                </div>
                <div class="card-body">
                    {{ Form::model($vehicle, ['route' => ['vehicles.update', $vehicle->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('license_plate', __('Номер машины'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                {{ Form::text('license_plate', null, ['class' => 'form-control', 'required' => true]) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('brand', __('Марка/Модель'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                {{ Form::text('brand', null, ['class' => 'form-control', 'required' => true]) }}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('color', __('Цвет'), ['class' => 'form-label']) }}
                                {{ Form::text('color', null, ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('vin_code', __('VIN-код'), ['class' => 'form-label']) }}
                                {{ Form::text('vin_code', null, ['class' => 'form-control', 'maxlength' => 17]) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('fuel_consumption', __('Расход топлива (л/100км)'), ['class' => 'form-label']) }}
                                {{ Form::number('fuel_consumption', null, ['class' => 'form-control', 'step' => '0.1', 'min' => '0', 'max' => '99.9']) }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        {{ Form::label('photo', __('Фото автомобиля'), ['class' => 'form-label']) }}
                        @if ($vehicle->photo)
                            <div class="mb-2">
                                <img src="{{ asset('uploads/vehicle_photos/' . $vehicle->photo) }}" alt="" class="rounded" style="max-width: 200px;">
                            </div>
                        @endif
                        {{ Form::file('photo', ['class' => 'form-control', 'accept' => 'image/jpeg,image/png,image/webp']) }}
                        <small class="text-muted">{{ __('Оставьте пустым, чтобы сохранить текущее фото') }}</small>
                    </div>

                    <hr>
                    <h6 class="mb-3">{{ __('Ответственный (опционально)') }}</h6>

                    @php
                        $currentType = '';
                        if ($vehicle->assigned_type === \App\Models\Worker::class) $currentType = 'worker';
                        elseif ($vehicle->assigned_type === \App\Models\User::class) $currentType = 'user';
                    @endphp

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('assigned_type', __('Тип'), ['class' => 'form-label']) }}
                                {{ Form::select('assigned_type', ['' => __('Не назначен'), 'worker' => __('Работник'), 'user' => __('Пользователь')], $currentType, ['class' => 'form-control', 'id' => 'assigned_type']) }}
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                {{ Form::label('assigned_id', __('Ответственный'), ['class' => 'form-label']) }}
                                <select name="assigned_id" id="assigned_id" class="form-control">
                                    <option value="">{{ __('Выберите') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">{{ __('Отмена') }}</a>
                        <button type="submit" class="btn btn-primary">{{ __('Сохранить') }}</button>
                    </div>

                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
<script>
    var workers = @json($workers->map(fn($w) => ['id' => $w->id, 'name' => $w->first_name . ' ' . $w->last_name]));
    var users = @json($users->map(fn($u) => ['id' => $u->id, 'name' => $u->name]));
    var currentType = '{{ $currentType }}';
    var currentId = {{ $vehicle->assigned_id ?? 'null' }};

    function updateAssignedSelect() {
        var select = document.getElementById('assigned_id');
        var type = document.getElementById('assigned_type').value;

        select.innerHTML = '';

        if (!type) {
            select.innerHTML = '<option value="">{{ __("Сначала выберите тип") }}</option>';
            select.disabled = true;
            return;
        }

        var items = type === 'worker' ? workers : users;
        select.innerHTML = '<option value="">{{ __("Выберите") }}</option>';
        items.forEach(function(item) {
            var selected = (type === currentType && item.id === currentId) ? ' selected' : '';
            select.innerHTML += '<option value="' + item.id + '"' + selected + '>' + item.name + '</option>';
        });
        select.disabled = false;
    }

    document.getElementById('assigned_type').addEventListener('change', updateAssignedSelect);
    updateAssignedSelect();
</script>
@endpush
