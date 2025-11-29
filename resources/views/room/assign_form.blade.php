@php
    $unassignedWorkers = \App\Models\Worker::whereDoesntHave('currentAssignment')
        ->where('created_by', \Auth::user()->creatorId())
        ->get();
    $availableSpots = $room->availableSpots();
@endphp

{{ Form::open(['route' => ['room.assign.workers.bulk', $room->id], 'method' => 'POST', 'id' => 'assign-workers-form']) }}
<div class="modal-body">
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted">
                {{ __('Комната') }}: <strong>{{ $room->room_number }}</strong> | 
                {{ __('Свободно мест') }}: <strong id="available-spots">{{ $availableSpots }}</strong> {{ __('из') }} {{ $room->capacity }}
            </span>
            <span class="badge bg-primary" id="selected-count-badge">{{ __('Выбрано') }}: 0</span>
        </div>
    </div>
    
    <div class="mb-3">
        <input type="text" class="form-control" id="worker-search" placeholder="{{ __('Поиск по имени...') }}">
    </div>
    
    <div class="mb-2">
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-secondary filter-gender active" data-gender="all">{{ __('Все') }}</button>
            <button type="button" class="btn btn-outline-primary filter-gender" data-gender="male">{{ __('Мужчины') }}</button>
            <button type="button" class="btn btn-outline-danger filter-gender" data-gender="female">{{ __('Женщины') }}</button>
        </div>
    </div>

    <input type="hidden" name="worker_ids" id="worker-ids-input">
    
    <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
        <table class="table table-hover" id="workers-select-table">
            <thead class="sticky-top bg-white">
                <tr>
                    <th style="width: 40px;"></th>
                    <th>{{ __('Имя Фамилия') }}</th>
                    <th>{{ __('Пол') }}</th>
                    <th>{{ __('Национальность') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($unassignedWorkers as $worker)
                    <tr class="worker-row" data-gender="{{ $worker->gender }}" data-name="{{ strtolower($worker->first_name . ' ' . $worker->last_name) }}">
                        <td>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input worker-select-checkbox" 
                                    value="{{ $worker->id }}" 
                                    data-name="{{ $worker->first_name }} {{ $worker->last_name }}">
                            </div>
                        </td>
                        <td>{{ $worker->first_name }} {{ $worker->last_name }}</td>
                        <td>
                            @if($worker->gender == 'male')
                                <span class="badge bg-primary">{{ __('М') }}</span>
                            @else
                                <span class="badge bg-danger">{{ __('Ж') }}</span>
                            @endif
                        </td>
                        <td>{{ $worker->nationality }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">{{ __('Нет доступных работников для заселения') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Отмена') }}</button>
    <button type="submit" class="btn btn-primary" id="assign-submit-btn" disabled>{{ __('Заселить') }}</button>
</div>
{{ Form::close() }}

<script>
(function() {
    var maxSelectable = {{ $availableSpots }};
    var selectedWorkers = [];
    var checkboxes = document.querySelectorAll('.worker-select-checkbox');
    var submitBtn = document.getElementById('assign-submit-btn');
    var workerIdsInput = document.getElementById('worker-ids-input');
    var selectedCountBadge = document.getElementById('selected-count-badge');
    var availableSpotsEl = document.getElementById('available-spots');
    var searchInput = document.getElementById('worker-search');
    var filterButtons = document.querySelectorAll('.filter-gender');
    var currentGenderFilter = 'all';

    function updateUI() {
        selectedCountBadge.textContent = '{{ __("Выбрано") }}: ' + selectedWorkers.length + ' / ' + maxSelectable;
        workerIdsInput.value = selectedWorkers.join(',');
        submitBtn.disabled = selectedWorkers.length === 0;
        
        // Update checkbox states
        checkboxes.forEach(function(cb) {
            var row = cb.closest('tr');
            if (row.style.display !== 'none') {
                if (selectedWorkers.length >= maxSelectable && !cb.checked) {
                    cb.disabled = true;
                    row.classList.add('text-muted');
                } else {
                    cb.disabled = false;
                    row.classList.remove('text-muted');
                }
            }
        });
    }

    function filterRows() {
        var searchTerm = searchInput.value.toLowerCase();
        var rows = document.querySelectorAll('.worker-row');
        
        rows.forEach(function(row) {
            var name = row.dataset.name;
            var gender = row.dataset.gender;
            
            var matchesSearch = name.indexOf(searchTerm) !== -1;
            var matchesGender = currentGenderFilter === 'all' || gender === currentGenderFilter;
            
            row.style.display = (matchesSearch && matchesGender) ? '' : 'none';
        });
        
        updateUI();
    }

    // Checkbox change handler
    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', function() {
            var workerId = this.value;
            
            if (this.checked) {
                if (selectedWorkers.length >= maxSelectable) {
                    // Remove first selected worker
                    var firstWorkerId = selectedWorkers.shift();
                    var firstCheckbox = document.querySelector('.worker-select-checkbox[value="' + firstWorkerId + '"]');
                    if (firstCheckbox) firstCheckbox.checked = false;
                }
                selectedWorkers.push(workerId);
            } else {
                var index = selectedWorkers.indexOf(workerId);
                if (index > -1) selectedWorkers.splice(index, 1);
            }
            
            updateUI();
        });
    });

    // Search handler
    searchInput.addEventListener('input', filterRows);

    // Gender filter handler
    filterButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            filterButtons.forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            currentGenderFilter = this.dataset.gender;
            filterRows();
        });
    });

    updateUI();
})();
</script>
