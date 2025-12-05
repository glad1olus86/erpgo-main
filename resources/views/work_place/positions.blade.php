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
                                                <a href="#" 
                                                   data-url="{{ route('positions.workers', $position->id) }}"
                                                   data-ajax-popup="true"
                                                   data-title="{{ __('Сотрудники') }}: {{ $position->name }}"
                                                   data-size="lg"
                                                   class="text-primary fw-medium text-decoration-none">
                                                    <i class="ti ti-briefcase me-2"></i>
                                                    {{ $position->name }}
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info">{{ $position->workers_count }}</span>
                                            </td>
                                            <td class="text-end">
                                                <a href="#" 
                                                   data-url="{{ route('positions.workers', $position->id) }}"
                                                   data-ajax-popup="true"
                                                   data-title="{{ __('Сотрудники') }}: {{ $position->name }}"
                                                   data-size="lg"
                                                   class="btn btn-sm btn-success">
                                                    <i class="ti ti-user-plus"></i> {{ __('Трудоустроить') }}
                                                </a>
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

@endsection
