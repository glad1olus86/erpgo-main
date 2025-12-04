@extends('layouts.admin')

@section('page-title')
    {{ __('Шаблоны документов') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Шаблоны документов') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('document_template_create')
            <a href="{{ route('documents.create') }}"
                class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Создать шаблон') }}">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table" id="templates-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Название') }}</th>
                                    <th>{{ __('Описание') }}</th>
                                    <th>{{ __('Переменных') }}</th>
                                    <th>{{ __('Статус') }}</th>
                                    <th>{{ __('Создан') }}</th>
                                    <th>{{ __('Действие') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($templates as $template)
                                    <tr>
                                        <td>
                                            <span class="fw-medium">{{ $template->name }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ Str::limit($template->description, 50) }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $template->variables_count }}</span>
                                        </td>
                                        <td>
                                            @if($template->is_active)
                                                <span class="badge bg-success">{{ __('Активен') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Неактивен') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $template->formatted_created_at }}</td>
                                        <td class="Action">
                                            <span>
                                                @can('document_template_edit')
                                                    <div class="action-btn me-2">
                                                        <a href="{{ route('documents.edit', $template->id) }}"
                                                            class="mx-3 btn btn-sm align-items-center bg-info"
                                                            data-bs-toggle="tooltip" title="{{ __('Редактировать') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('document_template_delete')
                                                    <div class="action-btn">
                                                        {!! Form::open([
                                                            'method' => 'DELETE',
                                                            'route' => ['documents.destroy', $template->id],
                                                            'id' => 'delete-form-' . $template->id,
                                                        ]) !!}
                                                        <a href="#"
                                                            class="mx-3 btn btn-sm align-items-center bs-pass-para bg-danger"
                                                            data-bs-toggle="tooltip" title="{{ __('Удалить') }}"
                                                            data-confirm="{{ __('Вы уверены?') . '|' . __('Это действие нельзя отменить. Вы хотите продолжить?') }}"
                                                            data-confirm-yes="document.getElementById('delete-form-{{ $template->id }}').submit();">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="ti ti-file-text" style="font-size: 48px;"></i>
                                            <p class="mt-2">{{ __('Шаблоны документов не найдены') }}</p>
                                            @can('document_template_create')
                                                <a href="{{ route('documents.create') }}" class="btn btn-primary btn-sm">
                                                    <i class="ti ti-plus me-1"></i>{{ __('Создать первый шаблон') }}
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($templates->count() > 0)
    new simpleDatatables.DataTable("#templates-table", {
        perPage: 10,
        perPageSelect: [10, 25, 50, 100]
    });
    @endif
});
</script>
@endpush
