{{ Form::open(['route' => ['notification-rules.update', $notificationRule->id], 'method' => 'PUT', 'id' => 'rule-form']) }}
<div class="modal-body">
    {{-- Rule Name --}}
    <div class="row mb-3">
        <div class="col-12">
            <label class="form-label">{{ __('Название правила') }} <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ $notificationRule->name }}" required>
        </div>
    </div>

    {{-- Rule Builder --}}
    <div class="card bg-light mb-3">
        <div class="card-body p-3">
            <h6 class="mb-3"><i class="ti ti-puzzle me-1"></i>{{ __('Конструктор правила') }}</h6>
            
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3" id="rule-preview">
                <span class="badge bg-dark fs-6">{{ __('ЕСЛИ') }}</span>
                <span class="badge bg-primary fs-6" id="preview-entity">{{ $entityTypes[$notificationRule->entity_type] ?? '' }}</span>
            </div>

            {{-- Entity Type --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Сущность') }} <span class="text-danger">*</span></label>
                    <select name="entity_type" id="entity-type" class="form-control" required>
                        <option value="">{{ __('Выберите...') }}</option>
                        @foreach($entityTypes as $value => $label)
                            <option value="{{ $value }}" {{ $notificationRule->entity_type == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Conditions Container --}}
            <div id="conditions-container">
                <label class="form-label">{{ __('Условия') }} <span class="text-danger">*</span></label>
                <div id="conditions-list" class="mb-2"></div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="add-condition-btn">
                    <i class="ti ti-plus me-1"></i>{{ __('Добавить условие') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Period --}}
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">{{ __('Период от (дней)') }}</label>
            <input type="number" name="period_from" id="period-from" class="form-control" value="{{ $notificationRule->period_from }}" min="0">
            <small class="text-muted">{{ __('0 = сразу') }}</small>
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ __('Период до (дней)') }}</label>
            <input type="number" name="period_to" id="period-to" class="form-control" value="{{ $notificationRule->period_to }}" placeholder="{{ __('Не ограничено') }}" min="1">
            <small class="text-muted">{{ __('Оставьте пустым для "от X дней и более"') }}</small>
        </div>
    </div>

    {{-- Severity --}}
    <div class="row mb-3">
        <div class="col-md-8">
            <label class="form-label">{{ __('Тип уведомления') }} <span class="text-danger">*</span></label>
            <div class="d-flex gap-3 flex-wrap">
                @foreach($severityLevels as $value => $info)
                    <div class="form-check">
                        <input type="radio" name="severity" value="{{ $value }}" 
                            class="form-check-input" id="severity-{{ $value }}" {{ $notificationRule->severity == $value ? 'checked' : '' }}>
                        <label class="form-check-label" for="severity-{{ $value }}">
                            <span class="badge bg-{{ $info['color'] }}">
                                <i class="ti {{ $info['icon'] }} me-1"></i>{{ $info['label'] }}
                            </span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('Группировка') }}</label>
            <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" name="is_grouped" id="is-grouped" value="1" {{ $notificationRule->is_grouped ? 'checked' : '' }}>
                <label class="form-check-label" for="is-grouped">{{ __('Группировать') }}</label>
            </div>
            <small class="text-muted">{{ __('Объединить все совпадения в одно уведомление') }}</small>
        </div>
    </div>

    {{-- Final Preview --}}
    <div class="alert alert-secondary mb-0">
        <strong>{{ __('Итоговое правило:') }}</strong>
        <div id="final-preview" class="mt-2 d-flex flex-wrap align-items-center gap-1"></div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Отмена') }}</button>
    <button type="submit" class="btn btn-primary" id="submit-btn">
        <i class="ti ti-check me-1"></i>{{ __('Сохранить') }}
    </button>
</div>
{{ Form::close() }}

<script>
(function() {
    var entityTypeSelect = document.getElementById('entity-type');
    var conditionsContainer = document.getElementById('conditions-container');
    var conditionsList = document.getElementById('conditions-list');
    var addConditionBtn = document.getElementById('add-condition-btn');
    var availableConditions = {};
    var conditionIndex = 0;
    
    var existingConditions = @json($notificationRule->conditions ?? []);

    var entityLabels = @json($entityTypes);
    var severityLabels = @json($severityLevels);

    // Load conditions on init
    loadConditions(entityTypeSelect.value, true);

    // Load conditions when entity type changes
    entityTypeSelect.addEventListener('change', function() {
        loadConditions(this.value, false);
    });

    function loadConditions(entityType, loadExisting) {
        if (!entityType) {
            conditionsContainer.style.display = 'none';
            conditionsList.innerHTML = '';
            updatePreview();
            return;
        }

        fetch('{{ route("notification-rules.conditions") }}?entity_type=' + entityType)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                availableConditions = data;
                conditionsContainer.style.display = 'block';
                conditionsList.innerHTML = '';
                conditionIndex = 0;
                
                if (loadExisting && existingConditions.length > 0) {
                    existingConditions.forEach(function(cond) {
                        addCondition(cond.field, cond.value);
                    });
                } else {
                    addCondition();
                }
                
                updatePreview();
            });
    }

    addConditionBtn.addEventListener('click', function() {
        addCondition();
    });

    function addCondition(selectedField, selectedValue) {
        var conditionHtml = '<div class="condition-row d-flex gap-2 align-items-center mb-2" data-index="' + conditionIndex + '">';
        
        if (conditionIndex > 0) {
            conditionHtml += '<span class="badge bg-warning text-dark">{{ __("И") }}</span>';
        }
        
        conditionHtml += '<select name="conditions[' + conditionIndex + '][field]" class="form-control form-control-sm condition-field" required>';
        conditionHtml += '<option value="">{{ __("Выберите условие") }}</option>';
        
        for (var key in availableConditions) {
            var selected = (selectedField === key) ? ' selected' : '';
            conditionHtml += '<option value="' + key + '" data-type="' + availableConditions[key].type + '"' + selected + '>' + availableConditions[key].label + '</option>';
        }
        
        conditionHtml += '</select>';
        
        var showValue = selectedField && availableConditions[selectedField] && availableConditions[selectedField].type === 'number';
        conditionHtml += '<input type="number" name="conditions[' + conditionIndex + '][value]" class="form-control form-control-sm condition-value" style="width: 80px; display: ' + (showValue ? 'block' : 'none') + ';" placeholder="0" value="' + (selectedValue || '') + '">';
        
        if (conditionIndex > 0) {
            conditionHtml += '<button type="button" class="btn btn-sm btn-outline-danger remove-condition"><i class="ti ti-x"></i></button>';
        }
        
        conditionHtml += '</div>';
        
        conditionsList.insertAdjacentHTML('beforeend', conditionHtml);
        conditionIndex++;
        
        bindConditionEvents();
    }

    function bindConditionEvents() {
        document.querySelectorAll('.condition-field').forEach(function(select) {
            select.removeEventListener('change', onConditionFieldChange);
            select.addEventListener('change', onConditionFieldChange);
        });

        document.querySelectorAll('.remove-condition').forEach(function(btn) {
            btn.removeEventListener('click', onRemoveCondition);
            btn.addEventListener('click', onRemoveCondition);
        });

        document.querySelectorAll('.condition-value').forEach(function(input) {
            input.removeEventListener('input', updatePreview);
            input.addEventListener('input', updatePreview);
        });
    }

    function onConditionFieldChange() {
        var row = this.closest('.condition-row');
        var valueInput = row.querySelector('.condition-value');
        var selectedOption = this.options[this.selectedIndex];
        var type = selectedOption.dataset.type;
        
        if (type === 'number') {
            valueInput.style.display = 'block';
            valueInput.required = true;
        } else {
            valueInput.style.display = 'none';
            valueInput.required = false;
            valueInput.value = '';
        }
        
        updatePreview();
    }

    function onRemoveCondition() {
        this.closest('.condition-row').remove();
        updatePreview();
    }

    function updatePreview() {
        var entityType = entityTypeSelect.value;
        var periodFrom = document.getElementById('period-from').value || 0;
        var periodTo = document.getElementById('period-to').value;
        var severity = document.querySelector('input[name="severity"]:checked').value;
        
        var preview = document.getElementById('final-preview');
        var html = '';
        
        html += '<span class="badge bg-dark">{{ __("ЕСЛИ") }}</span> ';
        
        if (entityType) {
            html += '<span class="badge bg-primary">' + entityLabels[entityType] + '</span> ';
            
            var conditions = [];
            document.querySelectorAll('.condition-row').forEach(function(row) {
                var field = row.querySelector('.condition-field').value;
                var value = row.querySelector('.condition-value').value;
                if (field && availableConditions[field]) {
                    var label = availableConditions[field].label;
                    if (availableConditions[field].type === 'number' && value) {
                        label += ': ' + value + (availableConditions[field].suffix || '');
                    }
                    conditions.push(label);
                }
            });
            
            if (conditions.length > 0) {
                html += '<span class="badge bg-secondary">(</span> ';
                conditions.forEach(function(cond, i) {
                    if (i > 0) html += '<span class="badge bg-warning text-dark">{{ __("И") }}</span> ';
                    html += '<span class="badge bg-info">' + cond + '</span> ';
                });
                html += '<span class="badge bg-secondary">)</span> ';
            }
        }
        
        if (periodFrom > 0 || periodTo) {
            var periodText = '';
            if (periodTo) {
                periodText = periodFrom + '-' + periodTo + ' {{ __("дней") }}';
            } else if (periodFrom > 0) {
                periodText = '{{ __("от") }} ' + periodFrom + ' {{ __("дней") }}';
            }
            if (periodText) {
                html += '<span class="badge bg-secondary">' + periodText + '</span> ';
            }
        }
        
        html += '<span class="badge bg-dark">→</span> ';
        html += '<span class="badge bg-' + severityLabels[severity].color + '">';
        html += '<i class="ti ' + severityLabels[severity].icon + ' me-1"></i>' + severityLabels[severity].label;
        html += '</span>';
        
        preview.innerHTML = html;
        
        document.getElementById('preview-entity').textContent = entityType ? entityLabels[entityType] : '{{ __("Выберите сущность") }}';
        document.getElementById('preview-entity').className = 'badge fs-6 ' + (entityType ? 'bg-primary' : 'bg-secondary');
    }

    document.getElementById('period-from').addEventListener('input', updatePreview);
    document.getElementById('period-to').addEventListener('input', updatePreview);
    document.querySelectorAll('input[name="severity"]').forEach(function(radio) {
        radio.addEventListener('change', updatePreview);
    });
})();
</script>
