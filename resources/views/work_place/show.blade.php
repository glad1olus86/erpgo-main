<div class="mb-3 px-2 d-flex justify-content-between align-items-center">
    <div>
        <span class="text-muted">
            {{ __('Рабочее место') }}: <strong>{{ $workPlace->name }}</strong> | 
            {{ __('Сотрудников') }}: <strong>{{ $workPlace->currentAssignments->count() }}</strong>
        </span>
    </div>
    <a href="#" data-url="{{ route('work-place.assign.form', $workPlace->id) }}" data-ajax-popup="true"
        data-title="{{ __('Устроить на работу') }}" data-size="lg" class="btn btn-sm btn-primary">
        <i class="ti ti-plus"></i> {{ __('Устроить') }}
    </a>
</div>

@if($workPlace->currentAssignments->count() > 0)
    {{-- Bulk Actions Panel --}}
    <div id="work-bulk-actions" class="mb-3 p-2 bg-light rounded" style="display: none;">
        <div class="d-flex align-items-center gap-2">
            <span class="small"><strong id="work-selected-count">0</strong> {{ __('выбрано') }}</span>
            <button type="button" class="btn btn-sm btn-warning" id="work-bulk-dismiss-btn">
                <i class="ti ti-user-off me-1"></i>{{ __('Уволить выбранных') }}
            </button>
        </div>
    </div>

    {{ Form::open(['route' => ['work-place.dismiss.bulk', $workPlace->id], 'method' => 'POST', 'id' => 'work-bulk-dismiss-form']) }}
    <input type="hidden" name="worker_ids" id="work-dismiss-worker-ids">
    {{ Form::close() }}
@endif

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                @if($workPlace->currentAssignments->count() > 0)
                    <th style="width: 40px;">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="work-select-all">
                        </div>
                    </th>
                @endif
                <th>{{ __('Имя Фамилия') }}</th>
                <th>{{ __('Пол') }}</th>
                <th>{{ __('Дата устройства') }}</th>
                <th>{{ __('Действие') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($workPlace->currentAssignments as $assignment)
                <tr>
                    <td>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input work-employee-checkbox" 
                                value="{{ $assignment->worker->id }}" 
                                data-name="{{ $assignment->worker->first_name }} {{ $assignment->worker->last_name }}">
                        </div>
                    </td>
                    <td>
                        <a href="{{ route('worker.show', $assignment->worker->id) }}" target="_blank" class="text-primary fw-medium">
                            {{ $assignment->worker->first_name }} {{ $assignment->worker->last_name }}
                        </a>
                    </td>
                    <td>
                        @if($assignment->worker->gender == 'male')
                            <span class="badge bg-primary">{{ __('Мужчина') }}</span>
                        @else
                            <span class="badge bg-danger">{{ __('Женщина') }}</span>
                        @endif
                    </td>
                    <td>{{ \Auth::user()->dateFormat($assignment->started_at) }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('worker.show', $assignment->worker->id) }}" target="_blank"
                                class="btn btn-sm btn-info d-flex align-items-center" data-bs-toggle="tooltip" title="{{ __('Профиль') }}">
                                <i class="ti ti-user text-white"></i>
                            </a>
                            {!! Form::open([
                                'method' => 'POST',
                                'route' => ['worker.dismiss', $assignment->worker->id],
                                'id' => 'dismiss-form-' . $assignment->worker->id,
                                'class' => 'd-inline'
                            ]) !!}
                            <a href="#" class="btn btn-sm btn-warning d-flex align-items-center gap-1 bs-pass-para"
                                data-bs-toggle="tooltip" title="{{ __('Уволить') }}"
                                data-confirm="{{ __('Вы уверены?') . '|' . __('Это действие уволит работника.') }}"
                                data-confirm-yes="document.getElementById('dismiss-form-{{ $assignment->worker->id }}').submit();">
                                <i class="ti ti-user-off text-white"></i>
                                <span class="text-white">{{ __('Уволить') }}</span>
                            </a>
                            {!! Form::close() !!}
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="ti ti-briefcase-off" style="font-size: 24px;"></i><br>
                        {{ __('На этом месте никто не работает') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($workPlace->currentAssignments->count() > 0)
<script>
(function() {
    var selectAllCheckbox = document.getElementById('work-select-all');
    var checkboxes = document.querySelectorAll('.work-employee-checkbox');
    var bulkActionsPanel = document.getElementById('work-bulk-actions');
    var selectedCountEl = document.getElementById('work-selected-count');
    var bulkDismissBtn = document.getElementById('work-bulk-dismiss-btn');
    var workerIdsInput = document.getElementById('work-dismiss-worker-ids');
    var bulkForm = document.getElementById('work-bulk-dismiss-form');

    function getSelectedWorkers() {
        var selected = [];
        checkboxes.forEach(function(cb) {
            if (cb.checked) selected.push(cb.value);
        });
        return selected;
    }

    function updateUI() {
        var selected = getSelectedWorkers();
        selectedCountEl.textContent = selected.length;
        bulkActionsPanel.style.display = selected.length > 0 ? 'block' : 'none';
        workerIdsInput.value = selected.join(',');
    }

    // Select all
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            checkboxes.forEach(function(cb) {
                cb.checked = selectAllCheckbox.checked;
            });
            updateUI();
        });
    }

    // Individual checkboxes
    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', function() {
            updateUI();
            // Update select-all state
            var allChecked = Array.from(checkboxes).every(function(c) { return c.checked; });
            if (selectAllCheckbox) selectAllCheckbox.checked = allChecked;
        });
    });

    // Bulk dismiss button
    if (bulkDismissBtn) {
        bulkDismissBtn.addEventListener('click', function() {
            var selected = getSelectedWorkers();
            if (selected.length === 0) return;
            
            if (confirm('{{ __("Вы уверены что хотите уволить") }} ' + selected.length + ' {{ __("работников?") }}')) {
                bulkForm.submit();
            }
        });
    }
})();
</script>
@endif
