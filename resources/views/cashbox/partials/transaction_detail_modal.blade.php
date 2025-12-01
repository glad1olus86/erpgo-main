{{-- Transaction Detail Modal --}}
<div class="modal fade" id="transactionDetailModal" tabindex="-1" aria-labelledby="transactionDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionDetailModalLabel">{{ __('Детали транзакции') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="transactionDetailContent">
                {{-- Content loaded dynamically via JavaScript --}}
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">{{ __('Загрузка...') }}</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="transactionDetailFooter">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Закрыть') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Template for transaction detail content (used by JavaScript) --}}
<template id="transactionDetailTemplate">
    <div class="row">
        <div class="col-6 mb-3">
            <label class="text-muted small d-block">{{ __('Тип операции') }}</label>
            <span class="fw-bold transaction-type"></span>
        </div>
        <div class="col-6 mb-3">
            <label class="text-muted small d-block">{{ __('Статус') }}</label>
            <span class="badge transaction-status"></span>
        </div>
    </div>
    
    <div class="transaction-sender mb-3" style="display: none;">
        <label class="text-muted small d-block">{{ __('Отправитель') }}</label>
        <span class="sender-name"></span>
    </div>
    
    <div class="mb-3">
        <label class="text-muted small d-block">{{ __('Получатель') }}</label>
        <span class="recipient-name"></span>
    </div>
    
    <div class="mb-3">
        <label class="text-muted small d-block">{{ __('Сумма') }}</label>
        <span class="fw-bold text-success fs-5 transaction-amount"></span>
    </div>
    
    <div class="transaction-task mb-3" style="display: none;">
        <label class="text-muted small d-block">{{ __('Задача') }}</label>
        <span class="task-text"></span>
    </div>
    
    <div class="transaction-comment mb-3" style="display: none;">
        <label class="text-muted small d-block">{{ __('Комментарий') }}</label>
        <span class="comment-text"></span>
    </div>
    
    <div class="mb-3">
        <label class="text-muted small d-block">{{ __('Дата') }}</label>
        <span class="transaction-date"></span>
    </div>
</template>
