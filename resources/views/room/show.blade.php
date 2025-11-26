<div class="mb-3 d-flex justify-content-between align-items-center">
    <h6>{{ __('') }}</h6>
    @if (!$room->isFull())
        <a href="#" data-url="{{ route('room.assign.form', $room->id) }}" data-ajax-popup="true"
            data-title="{{ __('Заселить работника') }}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i> {{ __('Заселить') }}
        </a>
    @endif
</div>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('Имя Фамилия') }}</th>
                <th>{{ __('Пол') }}</th>
                <th>{{ __('Дата заселения') }}</th>
                <th>{{ __('Действие') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($room->currentAssignments as $assignment)
                <tr>
                    <td>
                        <a href="{{ route('worker.show', $assignment->worker->id) }}" target="_blank">
                            {{ $assignment->worker->first_name }} {{ $assignment->worker->last_name }}
                        </a>
                    </td>
                    <td>{{ $assignment->worker->gender == 'male' ? __('Мужчина') : __('Женщина') }}</td>
                    <td>{{ \Auth::user()->dateFormat($assignment->check_in_date) }}</td>
                    <td>
                        <div class="action-btn bg-danger ms-2">
                            {!! Form::open([
                                'method' => 'POST',
                                'route' => ['worker.unassign.room', $assignment->worker->id],
                                'id' => 'unassign-form-' . $assignment->worker->id,
                            ]) !!}
                            <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                data-bs-toggle="tooltip" title="{{ __('Выселить') }}"
                                data-original-title="{{ __('Unassign') }}"
                                data-confirm="{{ __('Вы уверены?') . '|' . __('Это действие выселит работника из комнаты.') }}"
                                data-confirm-yes="document.getElementById('unassign-form-{{ $assignment->worker->id }}').submit();">
                                <i class="ti ti-logout text-white"></i>
                            </a>
                            {!! Form::close() !!}
                        </div>
                        <div class="action-btn bg-info ms-2">
                            <a href="{{ route('worker.show', $assignment->worker->id) }}" target="_blank"
                                class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip"
                                title="{{ __('Профиль') }}">
                                <i class="ti ti-user text-white"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">{{ __('В этой комнате никто не живет') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
