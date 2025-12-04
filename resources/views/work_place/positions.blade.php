@extends('layouts.admin')

@section('page-title')
    {{ __('Должности') }}: {{ $workPlace->name }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('work-place.index') }}">{{ __('Рабочие места') }}</a></li>
    <li class="breadcrumb-item">{{ $workPlace->name }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createPositionModal">
            <i class="ti ti-plus"></i> {{ __('Создать должность') }}
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Должности в') }} {{ $workPlace->name }}</h5>
                    <small class="text-muted">{{ $workPlace->address }}</small>
                </div>
                <div class="card-body">
                    @if($positions->isEmpty())
                        <div class="text-center py-4">
                            <i class="ti ti-briefcase text-muted" style="font-size: 48px;"></i>
                            <p class="text-muted mt-2">{{ __('Должности ещё не созданы') }}</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Название должности') }}</th>
                                        <th class="text-center">{{ __('Работников') }}</th>
                                        <th class="text-end">{{ __('Действия') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($positions as $position)
                                        <tr>
                                            <td>
                                                <i class="ti ti-briefcase me-2 text-primary"></i>
                                                {{ $position->name }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info">{{ $position->workers_count }}</span>
                                            </td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-success hire-btn" 
                                                        data-position-id="{{ $position->id }}"
                                                        data-position-name="{{ $position->name }}">
                                                    <i class="ti ti-user-plus"></i> {{ __('Трудоустроить') }}
                                                </button>
                                                <form action="{{ route('positions.destroy', $position->id) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('{{ __('Удалить должность?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Create Position Modal -->
    <div class="modal fade" id="createPositionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('positions.store', $workPlace->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Создать должность') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">{{ __('Название должности') }}</label>
                            <input type="text" name="name" class="form-control" required 
                                   placeholder="{{ __('Например: Менеджер') }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Отмена') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Создать') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hire Workers Modal -->
    <div class="modal fade" id="hireWorkersModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="hireWorkersForm" method="POST">
                    @csrf
                    <input type="hidden" name="worker_ids" id="selectedWorkerIds">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Трудоустроить на должность') }}: <span id="positionNameTitle"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="workersLoading" class="text-center py-4">
                            <div class="spinner-border text-primary"></div>
                            <p class="mt-2">{{ __('Загрузка...') }}</p>
                        </div>
                        <div id="workersContainer" style="display: none;">
                            <div class="mb-3">
                                <input type="text" id="workerSearch" class="form-control" 
                                       placeholder="{{ __('Поиск работника...') }}">
                            </div>
                            <div id="noWorkersMessage" class="text-center py-4" style="display: none;">
                                <i class="ti ti-users text-muted" style="font-size: 48px;"></i>
                                <p class="text-muted mt-2">{{ __('Нет свободных работников') }}</p>
                            </div>
                            <div id="workersList" class="list-group" style="max-height: 400px; overflow-y: auto;">
                                <!-- Workers will be loaded here -->
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <span class="me-auto text-muted">{{ __('Выбрано') }}: <span id="selectedCount">0</span></span>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Отмена') }}</button>
                        <button type="submit" class="btn btn-success" id="hireBtn" disabled>
                            <i class="ti ti-user-plus me-1"></i>{{ __('Трудоустроить') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var selectedWorkers = [];
    var allWorkers = [];
    
    // Hire button click
    document.querySelectorAll('.hire-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var positionId = this.dataset.positionId;
            var positionName = this.dataset.positionName;
            
            document.getElementById('positionNameTitle').textContent = positionName;
            document.getElementById('hireWorkersForm').action = '/positions/' + positionId + '/assign';
            
            selectedWorkers = [];
            updateSelectedCount();
            loadUnassignedWorkers();
            
            var modal = new bootstrap.Modal(document.getElementById('hireWorkersModal'));
            modal.show();
        });
    });
    
    // Load unassigned workers
    function loadUnassignedWorkers() {
        document.getElementById('workersLoading').style.display = 'block';
        document.getElementById('workersContainer').style.display = 'none';
        
        fetch('{{ route("workers.unassigned") }}')
            .then(response => response.json())
            .then(data => {
                allWorkers = data;
                document.getElementById('workersLoading').style.display = 'none';
                document.getElementById('workersContainer').style.display = 'block';
                
                if (data.length === 0) {
                    document.getElementById('noWorkersMessage').style.display = 'block';
                    document.getElementById('workersList').style.display = 'none';
                } else {
                    document.getElementById('noWorkersMessage').style.display = 'none';
                    document.getElementById('workersList').style.display = 'block';
                    renderWorkers(data);
                }
            });
    }
    
    // Render workers list
    function renderWorkers(workers) {
        var html = '';
        workers.forEach(function(worker) {
            var isSelected = selectedWorkers.includes(worker.id.toString());
            html += `
                <label class="list-group-item list-group-item-action worker-item">
                    <input type="checkbox" class="form-check-input me-2 worker-checkbox" 
                           value="${worker.id}" ${isSelected ? 'checked' : ''}>
                    ${worker.first_name} ${worker.last_name}
                </label>
            `;
        });
        document.getElementById('workersList').innerHTML = html;
        
        // Add checkbox listeners
        document.querySelectorAll('.worker-checkbox').forEach(function(cb) {
            cb.addEventListener('change', function() {
                if (this.checked) {
                    selectedWorkers.push(this.value);
                } else {
                    selectedWorkers = selectedWorkers.filter(id => id !== this.value);
                }
                updateSelectedCount();
            });
        });
    }
    
    // Update selected count
    function updateSelectedCount() {
        document.getElementById('selectedCount').textContent = selectedWorkers.length;
        document.getElementById('selectedWorkerIds').value = selectedWorkers.join(',');
        document.getElementById('hireBtn').disabled = selectedWorkers.length === 0;
    }
    
    // Search workers
    document.getElementById('workerSearch').addEventListener('input', function() {
        var search = this.value.toLowerCase();
        var filtered = allWorkers.filter(function(w) {
            return (w.first_name + ' ' + w.last_name).toLowerCase().includes(search);
        });
        renderWorkers(filtered);
    });
});
</script>
@endpush
