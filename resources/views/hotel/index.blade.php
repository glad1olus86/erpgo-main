@extends('layouts.admin')

@section('page-title')
    {{ __('Управление отелями') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Отели') }}</li>
@endsection


@section('action-btn')
@endsection

@section('content')
    <div class="row">

        <div class="col-12">
            <div class="my-3 d-flex justify-content-end">
                @can('manage hotel')
                    <a href="#" data-url="{{ route('hotel.export.modal') }}" data-ajax-popup="true"
                        data-title="{{ __('Экспорт отелей') }}" data-bs-toggle="tooltip" title="{{ __('Экспорт') }}"
                        data-size="lg"
                        class="btn btn-sm btn-secondary me-1">
                        <i class="ti ti-file-export"></i>
                    </a>
                @endcan
                @can('create hotel')
                    <a href="#" data-url="{{ route('hotel.create') }}" data-ajax-popup="true"
                        data-title="{{ __('Создать новый отель') }}" data-bs-toggle="tooltip" title="{{ __('Создать') }}"
                        class="btn btn-sm btn-primary">
                        <i class="ti ti-plus"></i>
                    </a>
                @endcan
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table datatable">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Название') }}</th>
                                            <th>{{ __('Адрес') }}</th>
                                            <th>{{ __('Вместимость') }}</th>
                                            <th>{{ __('Контакты') }}</th>
                                            <th width="200px">{{ __('Действие') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="font-style">
                                        @foreach ($hotels as $hotel)
                                            @php
                                                $totalCapacity = $hotel->rooms->sum('capacity');
                                                $totalOccupied = $hotel->rooms->sum(function ($room) {
                                                    return $room->currentAssignments->count();
                                                });
                                                $percentage =
                                                    $totalCapacity > 0 ? ($totalOccupied / $totalCapacity) * 100 : 0;
                                                $color =
                                                    $percentage < 50
                                                        ? 'bg-danger'
                                                        : ($percentage < 100
                                                            ? 'bg-warning'
                                                            : 'bg-success');
                                            @endphp
                                            <tr>
                                                <td>{{ $hotel->name }}</td>
                                                <td>{{ $hotel->address }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span class="me-2">{{ $totalOccupied }} /
                                                            {{ $totalCapacity }}</span>
                                                        <div class="progress w-100" style="height: 6px;">
                                                            <div class="progress-bar {{ $color }}"
                                                                role="progressbar" style="width: {{ $percentage }}%;"
                                                                aria-valuenow="{{ $percentage }}" aria-valuemin="0"
                                                                aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if ($hotel->phone)
                                                        <div><i class="ti ti-phone"></i> {{ $hotel->phone }}</div>
                                                    @endif
                                                    @if ($hotel->email)
                                                        <div><i class="ti ti-mail"></i> {{ $hotel->email }}</div>
                                                    @endif
                                                    @if (!$hotel->phone && !$hotel->email)
                                                        <span class="text-muted">{{ __('Нет данных') }}</span>
                                                    @endif
                                                </td>

                                                <td class="Action">
                                                    <span>
                                                        @can('edit hotel')
                                                            <div class="action-btn me-2">

                                                                <a href="#"
                                                                    data-url="{{ URL::to('hotel/' . $hotel->id . '/edit') }}"
                                                                    data-ajax-popup="true"
                                                                    data-title="{{ __('Редактировать отель') }}"
                                                                    class="mx-3 btn btn-sm align-items-center bg-info"
                                                                    data-bs-toggle="tooltip" title="{{ __('Редактировать') }}"
                                                                    data-original-title="{{ __('Edit') }}">
                                                                    <i class="ti ti-pencil text-white"></i></a>
                                                            </div>
                                                        @endcan
                                                        @can('delete hotel')
                                                            <div class="action-btn ">
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['hotel.destroy', $hotel->id],
                                                                    'id' => 'delete-form-' . $hotel->id,
                                                                ]) !!}


                                                                <a href="#"
                                                                    class="mx-3 btn btn-sm  align-items-center bs-pass-para bg-danger"
                                                                    data-bs-toggle="tooltip" title="{{ __('Удалить') }}"
                                                                    data-original-title="{{ __('Delete') }}"
                                                                    data-confirm="{{ __('Вы уверены?') . '|' . __('Это действие нельзя отменить. Вы хотите продолжить?') }}"
                                                                    data-confirm-yes="document.getElementById('delete-form-{{ $hotel->id }}').submit();"><i
                                                                        class="ti ti-trash text-white"></i></a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        @endcan
                                                        <div class="action-btn ms-2">
                                                            <a href="{{ route('hotel.rooms', $hotel->id) }}"
                                                                class="mx-3 btn btn-sm align-items-center bg-warning"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Просмотреть комнаты') }}">
                                                                <i class="ti ti-eye text-white"></i>
                                                            </a>
                                                        </div>
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
