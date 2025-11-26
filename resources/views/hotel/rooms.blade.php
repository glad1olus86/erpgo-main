@extends('layouts.admin')

@section('page-title')
    {{ __('Комнаты отеля') }} - {{ $hotel->name }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('hotel.index') }}">{{ __('Отели') }}</a></li>
    <li class="breadcrumb-item">{{ $hotel->name }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="my-3 d-flex justify-content-end">
                @can('create hotel')
                    <a href="#" data-url="{{ route('room.create', ['hotel_id' => $hotel->id]) }}" data-ajax-popup="true"
                        data-title="{{ __('Создать новую комнату') }}" data-bs-toggle="tooltip" title="{{ __('Создать') }}"
                        class="btn btn-sm btn-primary">
                        <i class="ti ti-plus"></i>
                    </a>
                @endcan
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body table-border-style">
                            @if ($rooms->count() > 0)
                                <div class="table-responsive">
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Номер комнаты') }}</th>
                                                <th>{{ __('Вместимость') }}</th>
                                                <th>{{ __('Цена') }}</th>
                                                <th width="200px">{{ __('Действие') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="font-style">
                                            @foreach ($rooms as $room)
                                                <tr>
                                                    <td>{{ $room->room_number }}</td>
                                                    <td>{{ $room->currentAssignments->count() }} / {{ $room->capacity }}
                                                    </td>
                                                    <td>{{ $room->price }}</td>

                                                    <td class="Action">
                                                        <span>
                                                            @can('manage hotel')
                                                                <div class="action-btn me-2">
                                                                    <a href="#"
                                                                        data-url="{{ route('room.show', $room->id) }}"
                                                                        data-ajax-popup="true"
                                                                        data-title="{{ __('Жильцы комнаты') }}"
                                                                        class="mx-3 btn btn-sm align-items-center bg-warning"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Просмотр жильцов') }}"
                                                                        data-original-title="{{ __('View') }}">
                                                                        <i class="ti ti-eye text-white"></i>
                                                                    </a>
                                                                </div>
                                                            @endcan
                                                            @can('edit hotel')
                                                                <div class="action-btn me-2">

                                                                    <a href="#"
                                                                        data-url="{{ URL::to('room/' . $room->id . '/edit') }}"
                                                                        data-ajax-popup="true"
                                                                        data-title="{{ __('Редактировать комнату') }}"
                                                                        class="mx-3 btn btn-sm align-items-center bg-info"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Редактировать') }}"
                                                                        data-original-title="{{ __('Edit') }}">
                                                                        <i class="ti ti-pencil text-white"></i></a>
                                                                </div>
                                                            @endcan
                                                            @can('delete hotel')
                                                                <div class="action-btn ">
                                                                    {!! Form::open([
                                                                        'method' => 'DELETE',
                                                                        'route' => ['room.destroy', $room->id],
                                                                        'id' => 'delete-form-' . $room->id,
                                                                    ]) !!}


                                                                    <a href="#"
                                                                        class="mx-3 btn btn-sm  align-items-center bs-pass-para bg-danger"
                                                                        data-bs-toggle="tooltip" title="{{ __('Удалить') }}"
                                                                        data-original-title="{{ __('Delete') }}"
                                                                        data-confirm="{{ __('Вы уверены?') . '|' . __('Это действие нельзя отменить. Вы хотите продолжить?') }}"
                                                                        data-confirm-yes="document.getElementById('delete-form-{{ $room->id }}').submit();"><i
                                                                            class="ti ti-trash text-white"></i></a>
                                                                    {!! Form::close() !!}
                                                                </div>
                                                            @endcan
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="ti ti-bed" style="font-size: 48px; opacity: 0.3;"></i>
                                    <h5 class="mt-3">{{ __('Нет комнат') }}</h5>
                                    <p class="text-muted">{{ __('Для этого отеля пока не созданы комнаты') }}</p>
                                    @can('create hotel')
                                        <a href="#" data-url="{{ route('room.create', ['hotel_id' => $hotel->id]) }}"
                                            data-ajax-popup="true" data-title="{{ __('Создать новую комнату') }}"
                                            class="btn btn-primary mt-2">
                                            <i class="ti ti-plus"></i> {{ __('Создать комнату') }}
                                        </a>
                                    @endcan
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
