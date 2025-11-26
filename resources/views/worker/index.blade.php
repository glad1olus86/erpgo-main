@extends('layouts.admin')

@section('page-title')
    {{ __('Управление работниками') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Работники') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create worker')
            <a href="#" data-url="{{ route('worker.create') }}" data-ajax-popup="true"
                data-title="{{ __('Добавить нового работника') }}" data-bs-toggle="tooltip" title="{{ __('Создать') }}"
                class="btn btn-sm btn-primary">
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
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Имя') }}</th>
                                    <th>{{ __('Фамилия') }}</th>
                                    <th>{{ __('Дата рождения') }}</th>
                                    <th>{{ __('Пол') }}</th>
                                    <th>{{ __('Национальность') }}</th>
                                    <th>{{ __('Дата регистрации') }}</th>
                                    <th>{{ __('Действие') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($workers as $worker)
                                    <tr>
                                        <td>{{ $worker->first_name }}</td>
                                        <td>{{ $worker->last_name }}</td>
                                        <td>{{ \Auth::user()->dateFormat($worker->dob) }}</td>
                                        <td>{{ $worker->gender == 'male' ? __('Мужчина') : __('Женщина') }}</td>
                                        <td>{{ $worker->nationality }}</td>
                                        <td>{{ \Auth::user()->dateFormat($worker->registration_date) }}</td>
                                        <td class="Action">
                                            <span>
                                                @can('edit worker')
                                                    <div class="action-btn me-2">
                                                        <a href="#" data-url="{{ route('worker.edit', $worker->id) }}"
                                                            data-ajax-popup="true"
                                                            data-title="{{ __('Редактировать работника') }}"
                                                            class="mx-3 btn btn-sm align-items-center bg-info"
                                                            data-bs-toggle="tooltip" title="{{ __('Редактировать') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete worker')
                                                    <div class="action-btn">
                                                        {!! Form::open([
                                                            'method' => 'DELETE',
                                                            'route' => ['worker.destroy', $worker->id],
                                                            'id' => 'delete-form-' . $worker->id,
                                                        ]) !!}
                                                        <a href="#"
                                                            class="mx-3 btn btn-sm align-items-center bs-pass-para bg-danger"
                                                            data-bs-toggle="tooltip" title="{{ __('Удалить') }}"
                                                            data-confirm="{{ __('Вы уверены?') . '|' . __('Это действие нельзя отменить. Вы хотите продолжить?') }}"
                                                            data-confirm-yes="document.getElementById('delete-form-{{ $worker->id }}').submit();">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                                <div class="action-btn ms-2">
                                                    <a href="{{ route('worker.show', $worker->id) }}"
                                                        class="mx-3 btn btn-sm align-items-center bg-warning"
                                                        data-bs-toggle="tooltip" title="{{ __('Просмотреть') }}">
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
    @endsection
