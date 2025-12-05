@extends('layouts.admin')

@section('page-title')
    {{ __('Транспорт') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Транспорт') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('vehicle_create')
            <a href="{{ route('vehicles.create') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                title="{{ __('Добавить автомобиль') }}">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table" id="vehicles-table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">{{ __('Фото') }}</th>
                                    <th>{{ __('Номер') }}</th>
                                    <th>{{ __('Марка') }}</th>
                                    <th>{{ __('Цвет') }}</th>
                                    <th>{{ __('Ответственный') }}</th>
                                    <th>{{ __('Статус ТО') }}</th>
                                    <th>{{ __('Действие') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($vehicles as $vehicle)
                                    <tr>
                                        <td>
                                            @if ($vehicle->photo)
                                                <img src="{{ asset('uploads/vehicle_photos/' . $vehicle->photo) }}" alt=""
                                                    class="rounded" style="width: 50px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                    style="width: 50px; height: 40px;">
                                                    <i class="ti ti-car text-muted"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('vehicles.show', $vehicle) }}" class="fw-medium">
                                                {{ $vehicle->license_plate }}
                                            </a>
                                        </td>
                                        <td>{{ $vehicle->brand }}</td>
                                        <td>{{ $vehicle->color ?? '-' }}</td>
                                        <td>{{ $vehicle->assigned_name ?? '-' }}</td>
                                        <td>
                                            <span class="badge {{ $vehicle->inspection_status_badge }}">
                                                {{ $vehicle->inspection_status_label }}
                                            </span>
                                        </td>
                                        <td class="Action">
                                            <span>
                                                @can('vehicle_read')
                                                    <div class="action-btn me-2">
                                                        <a href="{{ route('vehicles.show', $vehicle) }}"
                                                            class="mx-3 btn btn-sm align-items-center bg-warning"
                                                            data-bs-toggle="tooltip" title="{{ __('Просмотр') }}">
                                                            <i class="ti ti-eye text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('vehicle_edit')
                                                    <div class="action-btn me-2">
                                                        <a href="{{ route('vehicles.edit', $vehicle) }}"
                                                            class="mx-3 btn btn-sm align-items-center bg-info"
                                                            data-bs-toggle="tooltip" title="{{ __('Редактировать') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('vehicle_delete')
                                                    <div class="action-btn">
                                                        {!! Form::open([
                                                            'method' => 'DELETE',
                                                            'route' => ['vehicles.destroy', $vehicle->id],
                                                            'id' => 'delete-form-' . $vehicle->id,
                                                        ]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para bg-danger"
                                                            data-bs-toggle="tooltip" title="{{ __('Удалить') }}"
                                                            data-confirm="{{ __('Вы уверены?') . '|' . __('Это действие нельзя отменить.') }}"
                                                            data-confirm-yes="document.getElementById('delete-form-{{ $vehicle->id }}').submit();">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="ti ti-car" style="font-size: 48px;"></i>
                                            <p class="mt-2">{{ __('Автомобили не найдены') }}</p>
                                            @can('vehicle_create')
                                                <a href="{{ route('vehicles.create') }}" class="btn btn-primary btn-sm">
                                                    <i class="ti ti-plus me-1"></i>{{ __('Добавить первый автомобиль') }}
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($vehicles->hasPages())
                        <div class="mt-3">
                            {{ $vehicles->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
