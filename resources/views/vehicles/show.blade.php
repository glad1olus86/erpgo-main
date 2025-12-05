@extends('layouts.admin')

@section('page-title')
    {{ $vehicle->brand }} - {{ $vehicle->license_plate }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('vehicles.index') }}">{{ __('Транспорт') }}</a></li>
    <li class="breadcrumb-item">{{ $vehicle->license_plate }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('vehicle_edit')
            <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip"
                title="{{ __('Редактировать') }}">
                <i class="ti ti-pencil"></i>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    @if ($vehicle->photo)
                        <img src="{{ asset('uploads/vehicle_photos/' . $vehicle->photo) }}" alt="" class="rounded mb-3"
                            style="max-width: 100%; max-height: 200px; object-fit: cover;">
                    @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3 mx-auto"
                            style="width: 150px; height: 100px;">
                            <i class="ti ti-car text-muted" style="font-size: 48px;"></i>
                        </div>
                    @endif
                    <h4>{{ $vehicle->brand }}</h4>
                    <h5 class="text-primary">{{ $vehicle->license_plate }}</h5>
                    <span class="badge {{ $vehicle->inspection_status_badge }}">
                        {{ $vehicle->inspection_status_label }}
                    </span>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Информация') }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">{{ __('Цвет') }}</span>
                            <span>{{ $vehicle->color ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">{{ __('VIN-код') }}</span>
                            <span>{{ $vehicle->vin_code ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">{{ __('Расход топлива') }}</span>
                            <span>{{ $vehicle->fuel_consumption ? $vehicle->fuel_consumption . ' л/100км' : '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">{{ __('Ответственный') }}</span>
                            <span>{{ $vehicle->assigned_name ?? '-' }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('История техосмотров') }}</h5>
                    @can('technical_inspection_manage')
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                            data-bs-target="#addInspectionModal">
                            <i class="ti ti-plus me-1"></i>{{ __('Добавить ТО') }}
                        </button>
                    @endcan
                </div>
                <div class="card-body">
                    @if ($vehicle->inspections->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Дата ТО') }}</th>
                                        <th>{{ __('След. ТО') }}</th>
                                        <th>{{ __('Пробег') }}</th>
                                        <th>{{ __('Стоимость') }}</th>
                                        <th>{{ __('СТО') }}</th>
                                        <th>{{ __('Действие') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($vehicle->inspections as $inspection)
                                        <tr>
                                            <td>{{ $inspection->formatted_inspection_date }}</td>
                                            <td>{{ $inspection->formatted_next_inspection_date }}</td>
                                            <td>{{ $inspection->mileage ? number_format($inspection->mileage, 0, '', ' ') . ' км' : '-' }}</td>
                                            <td>{{ $inspection->formatted_cost ?? '-' }}</td>
                                            <td>{{ $inspection->service_station ?? '-' }}</td>
                                            <td>
                                                @can('technical_inspection_manage')
                                                    <div class="action-btn">
                                                        {!! Form::open([
                                                            'method' => 'DELETE',
                                                            'route' => ['inspections.destroy', $inspection->id],
                                                            'id' => 'delete-inspection-' . $inspection->id,
                                                        ]) !!}
                                                        <a href="#" class="btn btn-sm bg-danger bs-pass-para"
                                                            data-bs-toggle="tooltip" title="{{ __('Удалить') }}"
                                                            data-confirm="{{ __('Удалить запись?') . '|' . __('Это действие нельзя отменить.') }}"
                                                            data-confirm-yes="document.getElementById('delete-inspection-{{ $inspection->id }}').submit();">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="ti ti-clipboard-check" style="font-size: 48px;"></i>
                            <p class="mt-2">{{ __('Записи техосмотров отсутствуют') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @can('technical_inspection_manage')
        @include('vehicles.partials.inspection_form')
    @endcan
@endsection
