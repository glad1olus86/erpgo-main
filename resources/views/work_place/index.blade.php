@extends('layouts.admin')

@section('page-title')
    {{ __('Work Places Management') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Work Places') }}</li>
@endsection

@section('action-btn')
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="my-3 d-flex justify-content-end">
                @can('manage work place')
                    <a href="#" data-url="{{ route('work-place.export.modal') }}" data-ajax-popup="true"
                        data-title="{{ __('Export Work Places') }}" data-bs-toggle="tooltip" title="{{ __('Export') }}"
                        data-size="lg"
                        class="btn btn-sm btn-secondary me-1">
                        <i class="ti ti-file-export"></i>
                    </a>
                @endcan
                @can('create work place')
                    <a href="#" data-url="{{ route('work-place.create') }}" data-ajax-popup="true"
                        data-title="{{ __('Create Work Place') }}" data-bs-toggle="tooltip" title="{{ __('Create') }}"
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
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Employees') }}</th>
                                            <th>{{ __('Address') }}</th>
                                            <th>{{ __('Contacts') }}</th>
                                            <th width="200px">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="font-style">
                                        @foreach ($workPlaces as $workPlace)
                                            <tr>
                                                <td>
                                                    @if($workPlace->positions_count == 1)
                                                        {{-- Single position - open workers modal --}}
                                                        <a href="#"
                                                            data-url="{{ route('positions.workers', $workPlace->positions->first()->id) }}"
                                                            data-ajax-popup="true"
                                                            data-title="{{ __('Employees') }}: {{ $workPlace->name }}"
                                                            data-size="lg"
                                                            class="text-primary fw-bold">
                                                            {{ $workPlace->name }}
                                                        </a>
                                                    @elseif($workPlace->positions_count > 1)
                                                        {{-- Multiple positions - go to positions list --}}
                                                        <a href="{{ route('work-place.positions', $workPlace->id) }}" class="text-primary fw-bold">
                                                            {{ $workPlace->name }}
                                                        </a>
                                                    @else
                                                        {{-- No positions - go to create --}}
                                                        <a href="{{ route('work-place.positions', $workPlace->id) }}" class="text-muted fw-bold">
                                                            {{ $workPlace->name }}
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($workPlace->currentAssignments->count() > 0)
                                                        <span class="badge bg-success">{{ $workPlace->currentAssignments->count() }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">0</span>
                                                    @endif
                                                </td>
                                                <td>{{ $workPlace->address }}</td>
                                                <td>
                                                    @if ($workPlace->phone)
                                                        <div><i class="ti ti-phone"></i> {{ $workPlace->phone }}</div>
                                                    @endif
                                                    @if ($workPlace->email)
                                                        <div><i class="ti ti-mail"></i> {{ $workPlace->email }}</div>
                                                    @endif
                                                    @if (!$workPlace->phone && !$workPlace->email)
                                                        <span class="text-muted">{{ __('No data') }}</span>
                                                    @endif
                                                </td>

                                                <td class="Action">
                                                    <span>
                                                        @can('edit work place')
                                                            <div class="action-btn me-2">
                                                                <a href="#"
                                                                    data-url="{{ URL::to('work-place/' . $workPlace->id . '/edit') }}"
                                                                    data-ajax-popup="true"
                                                                    data-title="{{ __('Edit Work Place') }}"
                                                                    class="mx-3 btn btn-sm align-items-center bg-info"
                                                                    data-bs-toggle="tooltip" title="{{ __('Edit') }}"
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
                                                                    data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                                    data-original-title="{{ __('Delete') }}"
                                                                    data-confirm="{{ __('Are you sure?') . '|' . __('This action cannot be undone. Do you want to continue?') }}"
                                                                    data-confirm-yes="document.getElementById('delete-form-{{ $workPlace->id }}').submit();"><i
                                                                        class="ti ti-trash text-white"></i></a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        @endcan
                                                        @can('manage work place')
                                                            <div class="action-btn ms-2">
                                                                <a href="{{ route('work-place.positions', $workPlace->id) }}"
                                                                    class="mx-3 btn btn-sm align-items-center bg-success"
                                                                    data-bs-toggle="tooltip" title="{{ __('Positions') }}">
                                                                    <i class="ti ti-briefcase text-white"></i>
                                                                </a>
                                                            </div>
                                                            <div class="action-btn ms-2">
                                                                <a href="#"
                                                                    data-url="{{ route('work-place.workers', $workPlace->id) }}"
                                                                    data-ajax-popup="true" data-title="{{ __('Employees') }}"
                                                                    data-size="lg"
                                                                    class="mx-3 btn btn-sm align-items-center bg-warning"
                                                                    data-bs-toggle="tooltip" title="{{ __('View Employees') }}">
                                                                    <i class="ti ti-users text-white"></i>
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
