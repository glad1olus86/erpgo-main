@extends('layouts.admin')

@section('page-title')
    {{ __('Worker Management') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Workers') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('document_generate')
            <a href="#" id="bulk-generate-doc-btn"
                data-bs-toggle="tooltip" title="{{ __('Document Generation') }}"
                class="btn btn-sm btn-info me-1">
                <i class="ti ti-file-text"></i>
            </a>
        @endcan
        @can('manage worker')
            <a href="#" data-url="{{ route('worker.export.modal') }}" data-ajax-popup="true"
                data-title="{{ __('Export Workers') }}" data-bs-toggle="tooltip" title="{{ __('Export') }}"
                data-size="lg"
                class="btn btn-sm btn-secondary me-1">
                <i class="ti ti-file-export"></i>
            </a>
        @endcan
        @can('create worker')
            <a href="#" data-url="{{ route('worker.create') }}" data-ajax-popup="true"
                data-title="{{ __('Add New Worker') }}" data-bs-toggle="tooltip" title="{{ __('Create') }}"
                class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection

@push('css-page')
<style>
    #workers-table th:first-child {
        cursor: default !important;
    }
    #workers-table th:first-child::after,
    #workers-table th:first-child::before {
        display: none !important;
    }
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    {{-- Bulk Actions Panel --}}
                    <div id="bulk-actions-panel" class="mb-3 p-3 bg-light rounded" style="display: none;">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="fw-bold"><span id="selected-count">0</span> {{ __('selected') }}</span>
                            <div class="vr mx-2"></div>
                            @can('manage work place')
                                <button type="button" class="btn btn-sm btn-success" id="bulk-assign-btn">
                                    <i class="ti ti-briefcase me-1"></i>{{ __('Assign to Work') }}
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" id="bulk-dismiss-btn">
                                    <i class="ti ti-user-off me-1"></i>{{ __('Dismiss') }}
                                </button>
                            @endcan
                            @can('manage worker')
                                <button type="button" class="btn btn-sm btn-danger" id="bulk-checkout-btn">
                                    <i class="ti ti-door-exit me-1"></i>{{ __('Check Out') }}
                                </button>
                            @endcan
                            @can('document_generate')
                                <button type="button" class="btn btn-sm btn-info bulk-generate-doc-btn">
                                    <i class="ti ti-file-text me-1"></i>{{ __('Document') }}
                                </button>
                            @endcan
                            <button type="button" class="btn btn-sm btn-secondary" id="bulk-clear-btn">
                                <i class="ti ti-x me-1"></i>{{ __('Clear Selection') }}
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table" id="workers-table">
                            <thead>
                                <tr>
                                    <th data-sortable="false" style="width: 40px; cursor: default !important;">
                                        <div class="form-check" onclick="event.stopPropagation();">
                                            <input type="checkbox" class="form-check-input" id="select-all-checkbox">
                                        </div>
                                    </th>
                                    <th>{{ __('First Name') }}</th>
                                    <th>{{ __('Last Name') }}</th>
                                    <th>{{ __('Date of Birth') }}</th>
                                    <th>{{ __('Gender') }}</th>
                                    <th>{{ __('Nationality') }}</th>
                                    <th>{{ __('Registration Date') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($workers as $worker)
                                    <tr data-worker-id="{{ $worker->id }}">
                                        <td>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input worker-checkbox" 
                                                    value="{{ $worker->id }}" 
                                                    data-name="{{ $worker->first_name }} {{ $worker->last_name }}"
                                                    data-is-working="{{ $worker->currentWorkAssignment ? '1' : '0' }}"
                                                    data-work-place="{{ $worker->currentWorkAssignment ? $worker->currentWorkAssignment->workPlace->name : '' }}"
                                                    data-is-housed="{{ $worker->currentAssignment ? '1' : '0' }}"
                                                    data-hotel="{{ $worker->currentAssignment ? $worker->currentAssignment->hotel->name : '' }}">
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('worker.show', $worker->id) }}" class="text-primary fw-medium">
                                                {{ $worker->first_name }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('worker.show', $worker->id) }}" class="text-primary fw-medium">
                                                {{ $worker->last_name }}
                                            </a>
                                        </td>
                                        <td>{{ \Auth::user()->dateFormat($worker->dob) }}</td>
                                        <td>{{ $worker->gender == 'male' ? __('Male') : __('Female') }}</td>
                                        <td>{{ $worker->nationality }}</td>
                                        <td>{{ \Auth::user()->dateFormat($worker->registration_date) }}</td>
                                        <td class="Action">
                                            <span>
                                                @can('edit worker')
                                                    <div class="action-btn me-2">
                                                        <a href="#" data-url="{{ route('worker.edit', $worker->id) }}"
                                                            data-ajax-popup="true"
                                                            data-title="{{ __('Edit Worker') }}"
                                                            class="mx-3 btn btn-sm align-items-center bg-info"
                                                            data-bs-toggle="tooltip" title="{{ __('Edit') }}">
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
                                                            data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                            data-confirm="{{ __('Are you sure?') . '|' . __('This action cannot be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('delete-form-{{ $worker->id }}').submit();">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                                <div class="action-btn ms-2">
                                                    <a href="{{ route('worker.show', $worker->id) }}"
                                                        class="mx-3 btn btn-sm align-items-center bg-warning"
                                                        data-bs-toggle="tooltip" title="{{ __('View') }}">
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
    </div>

    {{-- Bulk Assign Modal --}}
    <div class="modal fade" id="bulkAssignModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Assign to Work') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="bulk-assign-form" method="POST" action="{{ route('worker.bulk.assign') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="worker_ids" id="assign-worker-ids">
                        
                        <div id="assign-already-working" class="mb-3" style="display: none;">
                            <label class="form-label text-warning"><i class="ti ti-alert-triangle me-1"></i>{{ __('Already working (will be skipped):') }}</label>
                            <div id="assign-already-working-list" class="text-muted small"></div>
                        </div>
                        
                        <div id="assign-will-be-assigned" class="mb-3">
                            <label class="form-label text-success"><i class="ti ti-check me-1"></i>{{ __('Will be assigned:') }}</label>
                            <div id="assign-workers-list" class="text-muted small"></div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">{{ __('Work Place') }}</label>
                            <select name="work_place_id" class="form-control" required>
                                <option value="">{{ __('Select Work Place') }}</option>
                                @php
                                    $workPlaces = \App\Models\WorkPlace::where('created_by', Auth::user()->creatorId())->get();
                                @endphp
                                @foreach($workPlaces as $place)
                                    <option value="{{ $place->id }}">{{ $place->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-success" id="assign-submit-btn">{{ __('Assign') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Bulk Dismiss Confirm Modal --}}
    <div class="modal fade" id="bulkDismissModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Dismiss Workers') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="bulk-dismiss-form" method="POST" action="{{ route('worker.bulk.dismiss') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="worker_ids" id="dismiss-worker-ids">
                        
                        <div id="dismiss-not-working" class="mb-3" style="display: none;">
                            <label class="form-label text-secondary"><i class="ti ti-info-circle me-1"></i>{{ __('Not working (will be skipped):') }}</label>
                            <div id="dismiss-not-working-list" class="text-muted small"></div>
                        </div>
                        
                        <div id="dismiss-will-be-fired" class="mb-3">
                            <label class="form-label text-warning"><i class="ti ti-user-off me-1"></i>{{ __('Will be dismissed:') }}</label>
                            <div id="dismiss-workers-list" class="text-muted small"></div>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="ti ti-alert-triangle me-1"></i>
                            {{ __('Workers will be dismissed from their current workplace.') }}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-warning" id="dismiss-submit-btn">{{ __('Dismiss') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Bulk Checkout Confirm Modal --}}
    <div class="modal fade" id="bulkCheckoutModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Check Out Workers') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="bulk-checkout-form" method="POST" action="{{ route('worker.bulk.checkout') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="worker_ids" id="checkout-worker-ids">
                        
                        <div id="checkout-not-housed" class="mb-3" style="display: none;">
                            <label class="form-label text-secondary"><i class="ti ti-info-circle me-1"></i>{{ __('Not housed (will be skipped):') }}</label>
                            <div id="checkout-not-housed-list" class="text-muted small"></div>
                        </div>
                        
                        <div id="checkout-will-be-evicted" class="mb-3">
                            <label class="form-label text-danger"><i class="ti ti-door-exit me-1"></i>{{ __('Will be checked out:') }}</label>
                            <div id="checkout-workers-list" class="text-muted small"></div>
                        </div>
                        
                        <div class="alert alert-danger">
                            <i class="ti ti-alert-triangle me-1"></i>
                            {{ __('Workers will be checked out from their current accommodation.') }}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-danger" id="checkout-submit-btn">{{ __('Check Out') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Bulk Document Generation Modal --}}
    <div class="modal fade" id="bulkDocumentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Document Generation') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="bulk-document-form" method="POST" action="{{ route('worker.bulk.generate-documents') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="worker_ids" id="doc-worker-ids">
                        
                        {{-- Worker selection (shown when no bulk selection) --}}
                        <div class="form-group mb-3" id="doc-worker-select-group">
                            <label class="form-label">{{ __('Worker') }} <span class="text-danger">*</span></label>
                            <select name="single_worker_id" id="doc-single-worker" class="form-control">
                                <option value="">{{ __('Select Worker') }}</option>
                                @foreach ($workers as $worker)
                                    <option value="{{ $worker->id }}">{{ $worker->first_name }} {{ $worker->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Selected workers info (shown when bulk selection) --}}
                        <div class="mb-3" id="doc-selected-workers-info" style="display: none;">
                            <label class="form-label text-info"><i class="ti ti-users me-1"></i>{{ __('Selected Workers:') }}</label>
                            <div id="doc-selected-workers-list" class="text-muted small"></div>
                        </div>
                        
                        {{-- Template selection --}}
                        <div class="form-group mb-3">
                            <label class="form-label">{{ __('Document Template') }} <span class="text-danger">*</span></label>
                            <select name="template_id" id="doc-template-select" class="form-control" required>
                                <option value="">{{ __('Select Template') }}</option>
                                @php
                                    $templates = \App\Models\DocumentTemplate::where('created_by', Auth::user()->creatorId())
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->get();
                                @endphp
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Dynamic date fields container --}}
                        <div id="doc-dynamic-fields"></div>
                        
                        {{-- Format selection --}}
                        <div class="form-group">
                            <label class="form-label">{{ __('Format') }} <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="format" id="format-pdf" value="pdf" checked>
                                    <label class="form-check-label" for="format-pdf">
                                        <i class="ti ti-file-type-pdf text-danger me-1"></i>PDF
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="format" id="format-docx" value="docx">
                                    <label class="form-check-label" for="format-docx">
                                        <i class="ti ti-file-type-doc text-primary me-1"></i>Word
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="format" id="format-xlsx" value="xlsx">
                                    <label class="form-check-label" for="format-xlsx">
                                        <i class="ti ti-file-spreadsheet text-success me-1"></i>Excel
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-info" id="doc-generate-btn">
                            <i class="ti ti-download me-1"></i>{{ __('Generate') }}
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
    // Initialize DataTable with first column not sortable
    var workersTable = new simpleDatatables.DataTable("#workers-table", {
        columns: [
            { select: 0, sortable: false } // Disable sorting on checkbox column
        ],
        perPage: 10,
        perPageSelect: [10, 25, 50, 100]
    });

    var selectAllCheckbox = document.getElementById('select-all-checkbox');
    var bulkActionsPanel = document.getElementById('bulk-actions-panel');
    var selectedCountEl = document.getElementById('selected-count');
    
    function getVisibleCheckboxes() {
        // Get only visible rows from DataTable
        var checkboxes = [];
        var rows = document.querySelectorAll('#workers-table tbody tr');
        rows.forEach(function(row) {
            // Check if row is visible (not hidden by DataTable)
            if (row.style.display !== 'none' && !row.classList.contains('hidden')) {
                var cb = row.querySelector('.worker-checkbox');
                if (cb) checkboxes.push(cb);
            }
        });
        return checkboxes;
    }

    function getAllCheckboxes() {
        return document.querySelectorAll('.worker-checkbox');
    }

    function getSelectedWorkers() {
        var selected = [];
        getAllCheckboxes().forEach(function(cb) {
            if (cb.checked) {
                selected.push({
                    id: cb.value,
                    name: cb.dataset.name,
                    isWorking: cb.dataset.isWorking === '1',
                    workPlace: cb.dataset.workPlace || '',
                    isHoused: cb.dataset.isHoused === '1',
                    hotel: cb.dataset.hotel || ''
                });
            }
        });
        return selected;
    }

    function updateBulkPanel() {
        var selected = getSelectedWorkers();
        selectedCountEl.textContent = selected.length;
        bulkActionsPanel.style.display = selected.length > 0 ? 'block' : 'none';
    }

    // Select all checkbox - use event delegation to handle clicks
    selectAllCheckbox.addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent DataTable from capturing the click
        var checkboxes = getVisibleCheckboxes();
        checkboxes.forEach(function(cb) {
            cb.checked = selectAllCheckbox.checked;
        });
        updateBulkPanel();
    });

    // Individual checkboxes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('worker-checkbox')) {
            updateBulkPanel();
            // Update select-all state
            var allCheckboxes = getVisibleCheckboxes();
            var allChecked = allCheckboxes.every(function(cb) { return cb.checked; });
            selectAllCheckbox.checked = allChecked && allCheckboxes.length > 0;
        }
    });

    // Clear selection
    document.getElementById('bulk-clear-btn').addEventListener('click', function() {
        getAllCheckboxes().forEach(function(cb) { cb.checked = false; });
        selectAllCheckbox.checked = false;
        updateBulkPanel();
    });

    // Bulk Assign
    document.getElementById('bulk-assign-btn').addEventListener('click', function() {
        var selected = getSelectedWorkers();
        if (selected.length === 0) return;
        
        var alreadyWorking = selected.filter(function(w) { return w.isWorking; });
        var willBeAssigned = selected.filter(function(w) { return !w.isWorking; });
        
        document.getElementById('assign-worker-ids').value = willBeAssigned.map(function(w) { return w.id; }).join(',');
        
        // Show already working
        var alreadyWorkingDiv = document.getElementById('assign-already-working');
        if (alreadyWorking.length > 0) {
            alreadyWorkingDiv.style.display = 'block';
            document.getElementById('assign-already-working-list').innerHTML = alreadyWorking.map(function(w) { 
                return '• ' + w.name + ' <span class="text-warning">(' + w.workPlace + ')</span>'; 
            }).join('<br>');
        } else {
            alreadyWorkingDiv.style.display = 'none';
        }
        
        // Show will be assigned
        var willBeAssignedDiv = document.getElementById('assign-will-be-assigned');
        if (willBeAssigned.length > 0) {
            willBeAssignedDiv.style.display = 'block';
            document.getElementById('assign-workers-list').innerHTML = willBeAssigned.map(function(w) { return '• ' + w.name; }).join('<br>');
            document.getElementById('assign-submit-btn').disabled = false;
        } else {
            willBeAssignedDiv.style.display = 'none';
            document.getElementById('assign-workers-list').innerHTML = '<span class="text-muted">{{ __("No workers to assign") }}</span>';
            document.getElementById('assign-submit-btn').disabled = true;
        }
        
        new bootstrap.Modal(document.getElementById('bulkAssignModal')).show();
    });

    // Bulk Dismiss
    document.getElementById('bulk-dismiss-btn').addEventListener('click', function() {
        var selected = getSelectedWorkers();
        if (selected.length === 0) return;
        
        var notWorking = selected.filter(function(w) { return !w.isWorking; });
        var willBeFired = selected.filter(function(w) { return w.isWorking; });
        
        document.getElementById('dismiss-worker-ids').value = willBeFired.map(function(w) { return w.id; }).join(',');
        
        // Show not working
        var notWorkingDiv = document.getElementById('dismiss-not-working');
        if (notWorking.length > 0) {
            notWorkingDiv.style.display = 'block';
            document.getElementById('dismiss-not-working-list').innerHTML = notWorking.map(function(w) { return '• ' + w.name; }).join('<br>');
        } else {
            notWorkingDiv.style.display = 'none';
        }
        
        // Show will be fired
        var willBeFiredDiv = document.getElementById('dismiss-will-be-fired');
        if (willBeFired.length > 0) {
            willBeFiredDiv.style.display = 'block';
            document.getElementById('dismiss-workers-list').innerHTML = willBeFired.map(function(w) { 
                return '• ' + w.name + ' <span class="text-warning">(' + w.workPlace + ')</span>'; 
            }).join('<br>');
            document.getElementById('dismiss-submit-btn').disabled = false;
        } else {
            willBeFiredDiv.style.display = 'none';
            document.getElementById('dismiss-workers-list').innerHTML = '<span class="text-muted">{{ __("No workers to dismiss") }}</span>';
            document.getElementById('dismiss-submit-btn').disabled = true;
        }
        
        new bootstrap.Modal(document.getElementById('bulkDismissModal')).show();
    });

    // Bulk Checkout
    document.getElementById('bulk-checkout-btn').addEventListener('click', function() {
        var selected = getSelectedWorkers();
        if (selected.length === 0) return;
        
        var notHoused = selected.filter(function(w) { return !w.isHoused; });
        var willBeEvicted = selected.filter(function(w) { return w.isHoused; });
        
        document.getElementById('checkout-worker-ids').value = willBeEvicted.map(function(w) { return w.id; }).join(',');
        
        // Show not housed
        var notHousedDiv = document.getElementById('checkout-not-housed');
        if (notHoused.length > 0) {
            notHousedDiv.style.display = 'block';
            document.getElementById('checkout-not-housed-list').innerHTML = notHoused.map(function(w) { return '• ' + w.name; }).join('<br>');
        } else {
            notHousedDiv.style.display = 'none';
        }
        
        // Show will be evicted
        var willBeEvictedDiv = document.getElementById('checkout-will-be-evicted');
        if (willBeEvicted.length > 0) {
            willBeEvictedDiv.style.display = 'block';
            document.getElementById('checkout-workers-list').innerHTML = willBeEvicted.map(function(w) { 
                return '• ' + w.name + ' <span class="text-danger">(' + w.hotel + ')</span>'; 
            }).join('<br>');
            document.getElementById('checkout-submit-btn').disabled = false;
        } else {
            willBeEvictedDiv.style.display = 'none';
            document.getElementById('checkout-workers-list').innerHTML = '<span class="text-muted">{{ __("No workers to check out") }}</span>';
            document.getElementById('checkout-submit-btn').disabled = true;
        }
        
        new bootstrap.Modal(document.getElementById('bulkCheckoutModal')).show();
    });

    // Update select-all when DataTable redraws (search, pagination, etc.)
    workersTable.on('datatable.page', function() {
        selectAllCheckbox.checked = false;
    });
    workersTable.on('datatable.search', function() {
        selectAllCheckbox.checked = false;
    });
    workersTable.on('datatable.sort', function() {
        selectAllCheckbox.checked = false;
    });

    // Bulk Document Generation - function to open modal
    function openDocumentModal() {
        var selected = getSelectedWorkers();
        
        var workerSelectGroup = document.getElementById('doc-worker-select-group');
        var selectedWorkersInfo = document.getElementById('doc-selected-workers-info');
        var singleWorkerSelect = document.getElementById('doc-single-worker');
        var workerIdsInput = document.getElementById('doc-worker-ids');
        
        if (selected.length >= 2) {
            // Bulk mode - hide worker select, show selected workers
            workerSelectGroup.style.display = 'none';
            selectedWorkersInfo.style.display = 'block';
            singleWorkerSelect.removeAttribute('required');
            
            document.getElementById('doc-selected-workers-list').innerHTML = selected.map(function(w) { 
                return '• ' + w.name; 
            }).join('<br>');
            
            workerIdsInput.value = selected.map(function(w) { return w.id; }).join(',');
        } else {
            // Single mode - show worker select
            workerSelectGroup.style.display = 'block';
            selectedWorkersInfo.style.display = 'none';
            singleWorkerSelect.setAttribute('required', 'required');
            workerIdsInput.value = '';
            
            // If one worker selected, pre-select in dropdown
            if (selected.length === 1) {
                singleWorkerSelect.value = selected[0].id;
            } else {
                singleWorkerSelect.value = '';
            }
        }
        
        // Reset template and format
        document.getElementById('doc-template-select').value = '';
        document.getElementById('doc-dynamic-fields').innerHTML = '';
        document.getElementById('format-pdf').checked = true;
        
        new bootstrap.Modal(document.getElementById('bulkDocumentModal')).show();
    }
    
    // Header button (top right)
    var bulkDocBtn = document.getElementById('bulk-generate-doc-btn');
    if (bulkDocBtn) {
        bulkDocBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openDocumentModal();
        });
    }
    
    // Bulk panel button (in bulk actions panel)
    document.querySelectorAll('.bulk-generate-doc-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            openDocumentModal();
        });
    });
    
    // Load dynamic fields when template changes
    var templateSelect = document.getElementById('doc-template-select');
    if (templateSelect) {
        templateSelect.addEventListener('change', function() {
            var templateId = this.value;
            var dynamicFieldsContainer = document.getElementById('doc-dynamic-fields');
            dynamicFieldsContainer.innerHTML = '';
            
            if (!templateId) return;
            
            fetch('{{ url("/documents/template-fields") }}/' + templateId)
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.fields && data.fields.length > 0) {
                        var html = '<div class="mb-3"><label class="form-label">{{ __("Additional Fields") }}</label>';
                        data.fields.forEach(function(field) {
                            html += '<div class="mb-2">';
                            html += '<label class="form-label small">' + field.label + '</label>';
                            html += '<input type="date" class="form-control form-control-sm" name="' + field.field_name + '">';
                            html += '</div>';
                        });
                        html += '</div>';
                        dynamicFieldsContainer.innerHTML = html;
                    }
                })
                .catch(function(err) {
                    console.error('Error loading template fields:', err);
                });
        });
    }
});
</script>
@endpush
