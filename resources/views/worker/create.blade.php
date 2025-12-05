{{ Form::open(['url' => 'worker', 'method' => 'post', 'enctype' => 'multipart/form-data', 'id' => 'worker_create_form']) }}
<div class="modal-body">
    {{-- Scan Document Button --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card bg-light border-0">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-1"><i class="ti ti-scan me-2"></i>{{ __('Автозаполнение из документа') }}</h6>
                            <small class="text-muted">{{ __('Загрузите фото паспорта или ID для автоматического заполнения формы') }}</small>
                        </div>
                        <div>
                            <input type="file" id="scan_document_input" accept="image/*" style="display: none;">
                            <button type="button" class="btn btn-info" id="scan_document_btn">
                                <i class="ti ti-camera me-1"></i>{{ __('Сканировать документ') }}
                            </button>
                        </div>
                    </div>
                    <div id="scan_status" class="mt-2" style="display: none;">
                        <div class="d-flex align-items-center">
                            <div class="spinner-border spinner-border-sm text-info me-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span class="text-info">{{ __('Сканирование документа...') }}</span>
                        </div>
                    </div>
                    <div id="scan_result" class="mt-2" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('first_name', __('Имя'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('first_name', null, ['class' => 'form-control', 'placeholder' => __('Введите имя'), 'required' => 'required', 'id' => 'first_name']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('last_name', __('Фамилия'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('last_name', null, ['class' => 'form-control', 'placeholder' => __('Введите фамилию'), 'required' => 'required', 'id' => 'last_name']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('dob', __('Дата рождения'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::date('dob', null, ['class' => 'form-control', 'required' => 'required', 'id' => 'dob']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('gender', __('Пол'), ['class' => 'form-label']) }}<x-required></x-required>
                <select name="gender" id="gender" class="form-control" required>
                    <option value="male">{{ __('Мужчина') }}</option>
                    <option value="female">{{ __('Женщина') }}</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('nationality', __('Национальность'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('nationality', null, ['class' => 'form-control', 'placeholder' => __('Введите национальность'), 'required' => 'required', 'id' => 'nationality']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('registration_date', __('Дата регистрации'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::date('registration_date', date('Y-m-d'), ['class' => 'form-control', 'required' => 'required', 'id' => 'registration_date']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('phone', __('Телефон'), ['class' => 'form-label']) }}
                {{ Form::text('phone', null, ['class' => 'form-control', 'placeholder' => __('Введите телефон'), 'id' => 'phone']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
                {{ Form::email('email', null, ['class' => 'form-control', 'placeholder' => __('Введите email'), 'id' => 'email']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('document_photo', __('Фото документов'), ['class' => 'form-label']) }}
                <input type="hidden" name="scanned_document_path" id="scanned_document_path" value="">
                <div class="choose-file form-group">
                    <label for="document_photo" class="form-label">
                        <input type="file" class="form-control" name="document_photo" id="document_photo"
                            data-filename="document_photo_create">
                    </label>
                    <p class="document_photo_create"></p>
                </div>
                <div id="scanned_document_preview" style="display: none;" class="mt-2">
                    <span class="badge bg-success"><i class="ti ti-check me-1"></i>{{ __('Документ из сканера прикреплён') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('photo', __('Фото внешности'), ['class' => 'form-label']) }}
                <div class="choose-file form-group">
                    <label for="photo" class="form-label">
                        <input type="file" class="form-control" name="photo" id="photo"
                            data-filename="photo_create">
                    </label>
                    <p class="photo_create"></p>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Отменить') }}" class="btn btn-light" data-bs-dismiss="modal">
    <button type="button" class="btn btn-primary" id="worker_submit_btn">{{ __('Сохранить') }}</button>
</div>

{{-- Duplicate Warning Alert --}}
<div id="duplicate_warning" class="modal-body pt-0" style="display: none;">
    <div class="alert alert-warning mb-0">
        <div class="d-flex align-items-start">
            <i class="ti ti-alert-triangle me-2" style="font-size: 24px;"></i>
            <div>
                <strong>{{ __('Внимание! Возможный дубликат!') }}</strong>
                <p class="mb-2" id="duplicate_message"></p>
                <div id="duplicate_list" class="small"></div>
                <p class="mb-0 mt-2 text-muted">
                    <i class="ti ti-info-circle me-1"></i>
                    {{ __('Если вы уверены, нажмите кнопку ещё раз через 5 секунд') }}
                </p>
            </div>
        </div>
    </div>
</div>
{{ Form::close() }}

<script>
(function() {
    // Используем setTimeout чтобы дать время DOM элементам появиться в модальном окне
    setTimeout(function() {
        var scanBtn = document.getElementById('scan_document_btn');
        var scanInput = document.getElementById('scan_document_input');
        var scanStatus = document.getElementById('scan_status');
        var scanResult = document.getElementById('scan_result');
        
        // Duplicate check elements - use form ID for reliable selection
        var form = document.getElementById('worker_create_form');
        var submitBtn = document.getElementById('worker_submit_btn');
        var duplicateWarning = document.getElementById('duplicate_warning');
        var duplicateMessage = document.getElementById('duplicate_message');
        var duplicateList = document.getElementById('duplicate_list');
        
        // State for duplicate check
        var duplicateCheckPassed = false;
        var cooldownActive = false;
        var cooldownTimer = null;
        
        // Debug log
        console.log('Worker create form init:', { form: !!form, submitBtn: !!submitBtn });

        if (!scanBtn || !scanInput) {
            console.error('Scan elements not found');
        }

        scanBtn.onclick = function(e) {
            e.preventDefault();
            scanInput.click();
        };

        scanInput.onchange = function() {
            if (this.files && this.files[0]) {
                var formData = new FormData();
                formData.append('document_image', this.files[0]);
                formData.append('_token', '{{ csrf_token() }}');

                // Show loading
                scanStatus.style.display = 'block';
                scanResult.style.display = 'none';
                scanBtn.disabled = true;

                fetch('{{ route("worker.scan.document") }}', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    scanStatus.style.display = 'none';
                    scanBtn.disabled = false;

                    if (data.success && data.data) {
                        // Fill form fields
                        if (data.data.first_name) {
                            document.getElementById('first_name').value = data.data.first_name;
                        }
                        if (data.data.last_name) {
                            document.getElementById('last_name').value = data.data.last_name;
                        }
                        if (data.data.dob) {
                            document.getElementById('dob').value = data.data.dob;
                        }
                        if (data.data.gender) {
                            document.getElementById('gender').value = data.data.gender;
                        }
                        if (data.data.nationality) {
                            document.getElementById('nationality').value = data.data.nationality;
                        }

                        // Save scanned document path
                        if (data.scanned_document) {
                            document.getElementById('scanned_document_path').value = data.scanned_document;
                            document.getElementById('scanned_document_preview').style.display = 'block';
                        }

                        // Reset duplicate check state when data changes
                        duplicateCheckPassed = false;
                        resetSubmitButton();

                        // Debug: log what was received
                        console.log('Scan result:', data.data);

                        // Show success message
                        scanResult.innerHTML = '<div class="alert alert-success mb-0 py-2"><i class="ti ti-check me-1"></i>{{ __("Данные успешно извлечены! Документ прикреплён автоматически.") }}</div>';
                        scanResult.style.display = 'block';
                    } else if (data.error) {
                        scanResult.innerHTML = '<div class="alert alert-danger mb-0 py-2"><i class="ti ti-alert-circle me-1"></i>' + data.error + '</div>';
                        scanResult.style.display = 'block';
                    }
                })
                .catch(function(error) {
                    scanStatus.style.display = 'none';
                    scanBtn.disabled = false;
                    scanResult.innerHTML = '<div class="alert alert-danger mb-0 py-2"><i class="ti ti-alert-circle me-1"></i>{{ __("Ошибка при сканировании документа") }}</div>';
                    scanResult.style.display = 'block';
                    console.error('Scan error:', error);
                });

                // Reset input
                scanInput.value = '';
            }
        };
        
        // Reset duplicate check when name fields change
        var firstNameInput = document.getElementById('first_name');
        var lastNameInput = document.getElementById('last_name');
        
        if (firstNameInput) {
            firstNameInput.addEventListener('input', function() {
                duplicateCheckPassed = false;
                resetSubmitButton();
            });
        }
        
        if (lastNameInput) {
            lastNameInput.addEventListener('input', function() {
                duplicateCheckPassed = false;
                resetSubmitButton();
            });
        }
        
        function resetSubmitButton() {
            if (cooldownTimer) {
                clearInterval(cooldownTimer);
                cooldownTimer = null;
            }
            cooldownActive = false;
            if (submitBtn) {
                submitBtn.textContent = '{{ __("Сохранить") }}';
                submitBtn.classList.remove('btn-danger');
                submitBtn.classList.add('btn-primary');
                submitBtn.disabled = false;
            }
            if (duplicateWarning) {
                duplicateWarning.style.display = 'none';
            }
        }
        
        function startCooldown() {
            cooldownActive = true;
            var seconds = 5;
            
            submitBtn.disabled = true;
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-danger');
            submitBtn.textContent = '{{ __("Подождите") }} (' + seconds + ')';
            
            cooldownTimer = setInterval(function() {
                seconds--;
                if (seconds > 0) {
                    submitBtn.textContent = '{{ __("Подождите") }} (' + seconds + ')';
                } else {
                    clearInterval(cooldownTimer);
                    cooldownTimer = null;
                    cooldownActive = false;
                    submitBtn.disabled = false;
                    submitBtn.textContent = '{{ __("Создать всё равно") }}';
                    duplicateCheckPassed = true;
                }
            }, 1000);
        }
        
        // Handle submit button click
        if (form && submitBtn) {
            console.log('Attaching click handler to submit button');
            
            submitBtn.onclick = function(e) {
                console.log('Submit button clicked, duplicateCheckPassed:', duplicateCheckPassed, 'cooldownActive:', cooldownActive);
                
                // If duplicate check already passed, submit form
                if (duplicateCheckPassed) {
                    console.log('Duplicate check passed, submitting form');
                    form.submit();
                    return;
                }
                
                // If cooldown is active, do nothing
                if (cooldownActive) {
                    console.log('Cooldown active, ignoring click');
                    return;
                }
                
                // Validate form first
                if (!form.reportValidity()) {
                    console.log('Form validation failed');
                    return;
                }
                
                var firstName = document.getElementById('first_name').value.trim();
                var lastName = document.getElementById('last_name').value.trim();
                
                console.log('Checking duplicates for:', firstName, lastName);
                
                // Check for duplicates
                submitBtn.disabled = true;
                submitBtn.textContent = '{{ __("Проверка...") }}';
                
                fetch('{{ route("worker.check.duplicate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        first_name: firstName,
                        last_name: lastName
                    })
                })
                .then(function(response) { 
                    console.log('Response status:', response.status);
                    return response.json(); 
                })
                .then(function(data) {
                    console.log('Duplicate check result:', data);
                    
                    if (data.has_duplicates) {
                        // Show warning
                        duplicateMessage.textContent = data.message;
                        
                        // Build list of duplicates
                        var listHtml = '<ul class="mb-0 ps-3">';
                        data.duplicates.forEach(function(dup) {
                            listHtml += '<li><strong>' + dup.name + '</strong>';
                            if (dup.dob) listHtml += ' ({{ __("Д.р.") }}: ' + dup.dob + ')';
                            if (dup.nationality) listHtml += ' - ' + dup.nationality;
                            listHtml += ' <span class="text-muted">[{{ __("создан") }}: ' + dup.created_at + ']</span></li>';
                        });
                        listHtml += '</ul>';
                        duplicateList.innerHTML = listHtml;
                        
                        duplicateWarning.style.display = 'block';
                        
                        // Start cooldown
                        startCooldown();
                    } else {
                        // No duplicates, submit form
                        console.log('No duplicates, submitting form');
                        duplicateCheckPassed = true;
                        form.submit();
                    }
                })
                .catch(function(error) {
                    console.error('Duplicate check error:', error);
                    // On error, allow submission
                    duplicateCheckPassed = true;
                    form.submit();
                });
            };
        } else {
            console.error('Form or submit button not found!', { form: form, submitBtn: submitBtn });
        }
    }, 100);
})();
</script>
