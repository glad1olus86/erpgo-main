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

        {{-- Проживание Section --}}
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>{{ __('Проживание') }}</h5>
                    @if ($worker->currentAssignment)
                        <form action="{{ route('worker.unassign.room', $worker->id) }}" method="POST"
                            style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="ti ti-door-exit"></i> {{ __('Выселить') }}
                            </button>
                        </form>
                    @else
                        @can('manage worker')
                            <a href="#" class="btn btn-sm btn-primary"
                                onclick="event.preventDefault(); $('#assign-room-modal').modal('show');">
                                <i class="ti ti-home-plus"></i> {{ __('Заселить') }}
                            </a>
                        @endcan
                    @endif
                </div>
                <div class="card-body">
                    @if ($worker->currentAssignment)
                        <div class="row">
                            <div class="col-md-4">
                                <div class="d-flex align-items-start">
                                    <div class="theme-avtar bg-success">
                                        <i class="ti ti-building"></i>
                                    </div>
                                    <div class="ms-2">
                                        <p class="text-muted text-sm mb-0">{{ __('Отель') }}</p>
                                        <h5 class="mb-0 mt-1">{{ $worker->currentAssignment->hotel->name }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-start">
                                    <div class="theme-avtar bg-info">
                                        <i class="ti ti-bed"></i>
                                    </div>
                                    <div class="ms-2">
                                        <p class="text-muted text-sm mb-0">{{ __('Номер комнаты') }}</p>
                                        <h5 class="mb-0 mt-1">{{ $worker->currentAssignment->room->room_number }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-start">
                                    <div class="theme-avtar bg-warning">
                                        <i class="ti ti-calendar"></i>
                                    </div>
                                    <div class="ms-2">
                                        <p class="text-muted text-sm mb-0">{{ __('Дата заселения') }}</p>
                                        <h5 class="mb-0 mt-1">
                                            {{ \Auth::user()->dateFormat($worker->currentAssignment->check_in_date) }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-home-off" style="font-size: 48px; opacity: 0.3;"></i>
                            <h5 class="mt-3">{{ __('Работник не заселён') }}</h5>
                            <p class="text-muted">{{ __('Нажмите "Заселить" чтобы назначить комнату') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Трудоустройство Section --}}
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>{{ __('Трудоустройство') }}</h5>
                    @if ($worker->currentWorkAssignment)
                        <form action="{{ route('worker.dismiss', $worker->id) }}" method="POST"
                            style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm('{{ __('Вы уверены, что хотите уволить этого работника?') }}')">
                                <i class="ti ti-briefcase-off"></i> {{ __('Уволить') }}
                            </button>
                        </form>
                    @endif
                </div>
                <div class="card-body">
                    @if ($worker->currentWorkAssignment)
                        @php
                            $startDate = \Carbon\Carbon::parse($worker->currentWorkAssignment->started_at);
                            $today = \Carbon\Carbon::now();
                            $daysWorked = max(1, (int) floor($startDate->diffInDays($today)) + 1); // Минимум 1 день

                            // Формат для отображения
                            if ($daysWorked == 1) {
                                $workDuration = '1 ' . __('день');
                            } elseif ($daysWorked >= 2 && $daysWorked <= 4) {
                                $workDuration = $daysWorked . ' ' . __('дня');
                            } else {
                                $workDuration = $daysWorked . ' ' . __('дней');
                            }

                            $createdBy = \App\Models\User::find($worker->currentWorkAssignment->created_by);
                        @endphp
                        <div class="row">
                            <div class="col-md-3">
                                <div class="d-flex align-items-start">
                                    <div class="theme-avtar bg-primary">
                                        <i class="ti ti-briefcase"></i>
                                    </div>
                                    <div class="ms-2">
                                        <p class="text-muted text-sm mb-0">{{ __('Рабочее место') }}</p>
                                        <h5 class="mb-0 mt-1">{{ $worker->currentWorkAssignment->workPlace->name }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-start">
                                    <div class="theme-avtar bg-info">
                                        <i class="ti ti-calendar"></i>
                                    </div>
                                    <div class="ms-2">
                                        <p class="text-muted text-sm mb-0">{{ __('Дата устройства') }}</p>
                                        <h5 class="mb-0 mt-1">
                                            {{ \Auth::user()->dateFormat($worker->currentWorkAssignment->started_at) }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-start">
                                    <div class="theme-avtar bg-success">
                                        <i class="ti ti-clock"></i>
                                    </div>
                                    <div class="ms-2">
                                        <p class="text-muted text-sm mb-0">{{ __('Время работы') }}</p>
                                        <h5 class="mb-0 mt-1">{{ $workDuration }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-start">
                                    <div class="theme-avtar bg-warning">
                                        <i class="ti ti-user-check"></i>
                                    </div>
                                    <div class="ms-2">
                                        <p class="text-muted text-sm mb-0">{{ __('Устроен кем') }}</p>
                                        <h5 class="mb-0 mt-1">{{ $createdBy ? $createdBy->name : '-' }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-briefcase-off" style="font-size: 48px; opacity: 0.3;"></i>
                            <h5 class="mt-3">{{ __('Работник не трудоустроен') }}</h5>
                            <p class="text-muted">
                                {{ __('Устройте работника на рабочее место через модуль "Рабочие места"') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Assignment Modal --}}
    <div class="modal fade" id="assign-room-modal" tabindex="-1" role="dialog"
        aria-labelledby="assign-room-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assign-room-modal-label">{{ __('Заселить работника') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('worker.assign.room', $worker->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="hotel_id"
                                        class="form-label">{{ __('Отель') }}</label><x-required></x-required>
                                    <select name="hotel_id" id="hotel_id" class="form-control" required>
                                        <option value="" selected>{{ __('Выберите отель') }}</option>
                                        @foreach ($hotels as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 mt-3">
                                <div class="form-group">
                                    <label for="room_id"
                                        class="form-label">{{ __('Комната') }}</label><x-required></x-required>
                                    <select name="room_id" id="room_id" class="form-control" required disabled>
                                        <option value="">{{ __('Сначала выберите отель') }}</option>
                                    </select>
                                    <small class="form-text text-muted" id="room-capacity-info"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Отмена') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Заселить') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            $('#hotel_id').on('change', function() {
                var hotelId = $(this).val();
                var roomSelect = $('#room_id');
                var capacityInfo = $('#room-capacity-info');

                if (hotelId) {
                    $.ajax({
                        url: '/hotel/' + hotelId + '/available-rooms',
                        type: 'GET',
                        success: function(rooms) {
                            roomSelect.empty();
                            roomSelect.prop('disabled', false);

                            if (rooms.length === 0) {
                                roomSelect.append(
                                    '<option value="">{{ __('Нет доступных комнат') }}</option>'
                                );
                                roomSelect.prop('disabled', true);
                                return;
                            }

                            roomSelect.append(
                                '<option value="">{{ __('Выберите комнату') }}</option>');

                            rooms.forEach(function(room) {
                                var optionText = '{{ __('Комната') }} ' + room
                                    .room_number +
                                    ' (' + room.occupancy_status +
                                    ' {{ __('занято') }})';

                                if (room.is_full) {
                                    optionText += ' - {{ __('Заполнено') }}';
                                    roomSelect.append('<option value="' + room.id +
                                        '" disabled>' + optionText + '</option>');
                                } else {
                                    roomSelect.append('<option value="' + room.id +
                                        '">' + optionText + '</option>');
                                }
                            });
                        },
                        error: function() {
                            roomSelect.empty();
                            roomSelect.append(
                                '<option value="">{{ __('Ошибка загрузки комнат') }}</option>'
                            );
                            roomSelect.prop('disabled', true);
                        }
                    });
                } else {
                    roomSelect.empty();
                    roomSelect.append('<option value="">{{ __('Сначала выберите отель') }}</option>');
                    roomSelect.prop('disabled', true);
                    capacityInfo.text('');
                }
            });

            $('#room_id').on('change', function() {
                var selectedOption = $(this).find('option:selected');
                var capacityInfo = $('#room-capacity-info');

                if ($(this).val() && !selectedOption.is(':disabled')) {
                    capacityInfo.text('{{ __('Комната доступна для заселения') }}');
                    capacityInfo.removeClass('text-danger').addClass('text-success');
                } else {
                    capacityInfo.text('');
                }
            });
        });
    </script>
@endpush
