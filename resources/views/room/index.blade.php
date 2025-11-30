@extends('layouts.admin')

@section('page-title')
    {{ __('Управление номерами') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Номера') }}</li>
@endsection

@section('action-btn')
@endsection

@section('content')
    <div class="row">

        <div class="col-12">
            <div class="my-3 d-flex justify-content-end">
                @can('create hotel')
                    <a href="#" data-url="{{ route('room.create') }}" data-ajax-popup="true"
                        data-title="{{ __('Создать новый номер') }}" data-bs-toggle="tooltip" title="{{ __('Создать') }}"
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
                                            <th>{{ __('Номер комнаты') }}</th>
                                            <th>{{ __('Отель') }}</th>
                                            <th>{{ __('Вместимость') }}</th>
                                            <th>{{ __('Цена/месяц') }}</th>
                                            <th>{{ __('Кто платит') }}</th>
                                            <th width="200px">{{ __('Действие') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="font-style">
                                        @php
                                            $paymentLabels = [
                                                'worker' => __('Платит сам'),
                                                'agency' => __('Платит агенство'),
                                                'partial' => __('Платит частично'),
                                            ];
                                        @endphp
                                        @foreach ($rooms as $room)
                                            <tr>
                                                <td>{{ $room->room_number }}</td>
                                                <td>{{ !empty($room->hotel) ? $room->hotel->name : '-' }}</td>
                                                <td>{{ $room->capacity }}</td>
                                                <td>{{ number_format($room->monthly_price, 2) }} €</td>
                                                <td>
                                                    {{ $paymentLabels[$room->payment_type] ?? $room->payment_type }}
                                                    @if($room->payment_type == 'partial' && $room->partial_amount)
                                                        <br><small class="text-muted">({{ number_format($room->partial_amount, 2) }} €)</small>
                                                    @endif
                                                </td>

                                                <td class="Action">
                                                    <span>
                                                        @can('edit hotel')
                                                            <div class="action-btn me-2">
                                                                <a href="#"
                                                                    data-url="{{ URL::to('room/' . $room->id . '/edit') }}"
                                                                    data-ajax-popup="true"
                                                                    data-title="{{ __('Редактировать номер') }}"
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
