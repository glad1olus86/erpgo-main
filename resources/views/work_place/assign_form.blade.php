{{ Form::open(['route' => ['work-place.assign.worker', $workPlace->id], 'method' => 'POST']) }}
<div class="modal-body">
    <div class="form-group">
        <label for="worker_id" class="form-label">{{ __('Выберите работника') }}<x-required></x-required></label>
        <select name="worker_id" id="worker_id" class="form-control" required>
            <option value="">{{ __('Выберите работника') }}</option>
            @php
                $unassignedWorkers = \App\Models\Worker::whereDoesntHave('currentWorkAssignment')
                    ->where('created_by', \Auth::user()->creatorId())
                    ->get();
            @endphp
            @foreach ($unassignedWorkers as $worker)
                <option value="{{ $worker->id }}">
                    {{ $worker->first_name }} {{ $worker->last_name }}
                    ({{ $worker->gender == 'male' ? __('М') : __('Ж') }})
                </option>
            @endforeach
        </select>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Отмена') }}</button>
    <button type="submit" class="btn btn-primary">{{ __('Устроить') }}</button>
</div>
{{ Form::close() }}
