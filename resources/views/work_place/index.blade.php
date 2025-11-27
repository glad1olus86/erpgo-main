@extends('layouts.admin')

@section('page-title')
    {{ __('Управление рабочими местами') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Рабочие места') }}</li>
@endsection

@section('action-btn')
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="my-3 d-flex justify-content-end">
                @can('create work place')
                    <a href="#" data-url="{{ route('work-place.create') }}" data-ajax-popup="true"
                        data-title="{{ __('Создать рабочее место') }}" data-bs-toggle="tooltip" title="{{ __('Создать') }}"
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
                                            <th>{{ __('Сотрудники') }}</th>
                                            <th>{{ __('Адрес') }}</th>
                                            <th>{{ __('Контакты') }}</th>
                                            <th width="200px">{{ __('Действие') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="font-style">
                                        @foreach ($workPlaces as $workPlace)
                                            <tr>
                                                <td>{{ $workPlace->name }}</td>
                                                <td>{{ $workPlace->currentAssignments->count() }}</td>
                                                <td>{{ $workPlace->address }}</td>
                                                <td>
                                                    @if ($workPlace->phone)
                                                        <div><i class="ti ti-phone"></i> {{ $workPlace->phone }}</div>
                                                    @endif
                                                    @if ($workPlace->email)
                                                        <div><i class="ti ti-mail"></i> {{ $workPlace->email }}</div>
                                                    @endif
                                                    @if (!$workPlace->phone && !$workPlace->email)
                                                        <span class="text-muted">{{ __('Нет данных') }}</span>
                                                    @endif
                                                </td>

                                                <td class="Action">
                                                    <span>
                                                        @can('edit work place')
                                                            <div class="action-btn me-2">
                                                                <a href="#"
                                                                    data-url="{{ URL::to('work-place/' . $workPlace->id . '/edit') }}"
                                                                    data-ajax-popup="true"
                                                                    data-title="{{ __('Редактировать рабочее место') }}"
                                                                    class="mx-3 btn btn-sm align-items-center bg-info"
                                                                    data-bs-toggle="tooltip" title="{{ __('Редактировать') }}"
                                                                    data-original-title="{{ __('Edit') }}">
                                                                    <i class="ti ti-pencil text-white"></i></a>
                                                            </div>
                                                        @endcan
                                                        @can('delete work place')
                                                            <div class="action-btn ">
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['work-place.destroy', $workPlace->id],
                                                                    'id' => 'delete-form-' . $workPlace->id,
                                                                ]) !!}

                                                                <a href="#"
                                                                    class="mx-3 btn btn-sm  align-items-center bs-pass-para bg-danger"
                                                                    data-bs-toggle="tooltip" title="{{ __('Удалить') }}"
                                                                    data-original-title="{{ __('Delete') }}"
                                                                    data-confirm="{{ __('Вы уверены?') . '|' . __('Это действие нельзя отменить. Вы хотите продолжить?') }}"
                                                                    data-confirm-yes="document.getElementById('delete-form-{{ $workPlace->id }}').submit();"><i
                                                                        class="ti ti-trash text-white"></i></a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        @endcan
                                                        @can('manage work place')
                                                            <div class="action-btn ms-2">
                                                                <a href="#"
                                                                    data-url="{{ route('work-place.workers', $workPlace->id) }}"
                                                                    data-ajax-popup="true" data-title="{{ __('Сотрудники') }}"
                                                                    class="mx-3 btn btn-sm align-items-center bg-warning"
                                                                    data-bs-toggle="tooltip" title="{{ __('Просмотреть') }}">
                                                                    <i class="ti ti-eye text-white"></i>
                                                                </a>
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
