{{-- Distribute Modal --}}
<div class="modal fade" id="distributeModal" tabindex="-1" aria-labelledby="distributeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="distributeModalLabel">{{ __('Выдать деньги') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="distributeForm">
                @csrf
                <input type="hidden" name="period_id" value="{{ $period->id }}">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Тип выдачи') }} <span class="text-danger">*</span></label>
                        <select name="distribution_type" id="distributionType" class="form-control" required>
                            <option value="">{{ __('Выберите тип выдачи') }}</option>
                            <option value="salary">{{ __('Зарплата сотруднику') }}</option>
                            <option value="transfer">{{ __('Передача средств') }}</option>
                        </select>
                        <small class="text-muted" id="distributionTypeHint"></small>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Получатель') }} <span class="text-danger">*</span></label>
                        <select name="recipient" class="form-control" required>
                            <option value="">{{ __('Выберите получателя') }}</option>
                            @foreach($recipients as $recipient)
                                @if(!isset($recipient['is_self']))
                                    <option value="{{ $recipient['type'] === 'App\\Models\\Worker' ? 'worker' : 'user' }}_{{ $recipient['id'] }}" data-role="{{ $recipient['role'] }}">
                                        {{ $recipient['name'] }} 
                                        @if($recipient['role'] === 'manager')
                                            ({{ __('Менеджер') }})
                                        @elseif($recipient['role'] === 'curator')
                                            ({{ __('Куратор') }})
                                        @else
                                            ({{ __('Работник') }})
                                        @endif
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Сумма') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required placeholder="0.00">
                            <span class="input-group-text">€</span>
                        </div>
                        <small class="text-muted">
                            {{ __('Доступно:') }} 
                            <span id="availableBalance">{{ number_format($balance['received'] - $balance['sent'], 2, ',', ' ') }}</span> €
                        </small>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Задача') }}</label>
                        <input type="text" name="task" class="form-control" placeholder="{{ __('Описание задачи...') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('Комментарий') }}</label>
                        <textarea name="comment" class="form-control" rows="2" placeholder="{{ __('Дополнительная информация...') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Отмена') }}</button>
                    <button type="submit" class="btn btn-success">{{ __('Выдать') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const distributionTypeSelect = document.getElementById('distributionType');
    const distributionTypeHint = document.getElementById('distributionTypeHint');
    
    if (distributionTypeSelect) {
        distributionTypeSelect.addEventListener('change', function() {
            const value = this.value;
            if (value === 'salary') {
                distributionTypeHint.textContent = '{{ __("Конечная выдача зарплаты. Транзакция будет сразу завершена.") }}';
                distributionTypeHint.className = 'text-muted';
            } else if (value === 'transfer') {
                distributionTypeHint.textContent = '{{ __("Передача денег для дальнейшего распределения другим сотрудникам.") }}';
                distributionTypeHint.className = 'text-muted';
            } else {
                distributionTypeHint.textContent = '';
            }
        });
    }
});
</script>
