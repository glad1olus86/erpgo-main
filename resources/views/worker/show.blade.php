@extends('layouts.admin')

@section('page-title')
    {{ __('Профиль работника') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('worker.index') }}">{{ __('Работники') }}</a></li>
    <li class="breadcrumb-item">{{ $worker->first_name }} {{ $worker->last_name }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 col-sm-6">
                            <div class="d-flex align-items-start">
                                <div class="theme-avtar bg-primary">
                                    <i class="ti ti-user"></i>
                                </div>
                                <div class="ms-2">
                                    <p class="text-muted text-sm mb-0">{{ __('Имя Фамилия') }}</p>
                                    <h5 class="mb-0 mt-1">{{ $worker->first_name }} {{ $worker->last_name }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <div class="d-flex align-items-start">
                                <div class="theme-avtar bg-info">
                                    <i class="ti ti-calendar"></i>
                                </div>
                                <div class="ms-2">
                                    <p class="text-muted text-sm mb-0">{{ __('Дата рождения') }}</p>
                                    <h5 class="mb-0 mt-1">{{ \Auth::user()->dateFormat($worker->dob) }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <div class="d-flex align-items-start">
                                <div class="theme-avtar bg-warning">
                                    <i class="ti ti-gender-bigender"></i>
                                </div>
                                <div class="ms-2">
                                    <p class="text-muted text-sm mb-0">{{ __('Пол') }}</p>
                                    <h5 class="mb-0 mt-1">{{ $worker->gender == 'male' ? __('Мужчина') : __('Женщина') }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-12 text-center">
                            <h5 class="mb-0">{{ __('Фото внешности') }}</h5>
                            <div class="mt-3">
                                @if (!empty($worker->photo))
                                    <img src="{{ asset('uploads/worker_photos/' . $worker->photo) }}" alt="photo"
                                        class="img-fluid rounded-circle"
                                        style="width: 150px; height: 150px; object-fit: cover;">
                                @else
                                    <img src="{{ asset('assets/images/user/avatar-4.jpg') }}" alt="photo"
                                        class="img-fluid rounded-circle"
                                        style="width: 150px; height: 150px; object-fit: cover;">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-6 col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Детальная информация') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group">
                                <h6>{{ __('Национальность') }}</h6>
                                <p class="mb-0">{{ $worker->nationality }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <h6>{{ __('Дата регистрации') }}</h6>
                                <p class="mb-0">{{ \Auth::user()->dateFormat($worker->registration_date) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mt-4">
                            <div class="info-group">
                                <h6>{{ __('Телефон') }}</h6>
                                <p class="mb-0">{{ !empty($worker->phone) ? $worker->phone : '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mt-4">
                            <div class="info-group">
                                <h6>{{ __('Email') }}</h6>
                                <p class="mb-0">{{ !empty($worker->email) ? $worker->email : '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-12 mt-4">
                            <div class="info-group">
                                <h6>{{ __('Фото документов') }}</h6>
                                @if (!empty($worker->document_photo))
                                    <div class="mt-2">
                                        <a href="{{ asset('uploads/worker_documents/' . $worker->document_photo) }}"
                                            target="_blank" class="btn btn-sm btn-primary">
                                            <i class="ti ti-file"></i> {{ __('Просмотреть документ') }}
                                        </a>
                                    </div>
                                @else
                                    <p class="mb-0">-</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
