{{ Form::model($room, ['route' => ['room.update', $room->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('hotel_id', __('Отель'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('hotel_id', $hotels, null, ['class' => 'form-control select', 'placeholder' => __('Выберите отель'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('room_number', __('Номер комнаты'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('room_number', null, ['class' => 'form-control', 'placeholder' => __('Введите номер'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('capacity', __('Вместимость'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::number('capacity', null, ['class' => 'form-control', 'placeholder' => __('Кол-во мест'), 'required' => 'required', 'min' => 1]) }}
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('monthly_price', __('Цена за месяц'), ['class' => 'form-label']) }}<x-required></x-required>
                <div class="input-group">
                    {{ Form::number('monthly_price', null, ['class' => 'form-control', 'placeholder' => __('Введите цену'), 'step' => '0.01', 'required' => 'required', 'min' => 0]) }}
                    <span class="input-group-text">€</span>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('payment_type', __('Кто платит'), ['class' => 'form-label']) }}<x-required></x-required>
                <div class="d-flex flex-column gap-2">
                    <div class="form-check">
                        <input type="radio" name="payment_type" value="worker" class="form-check-input" id="edit-payment-worker" {{ $room->payment_type == 'worker' ? 'checked' : '' }}>
                        <label class="form-check-label" for="edit-payment-worker">{{ __('Платит сам') }}</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" name="payment_type" value="agency" class="form-check-input" id="edit-payment-agency" {{ $room->payment_type == 'agency' ? 'checked' : '' }}>
                        <label class="form-check-label" for="edit-payment-agency">{{ __('Платит агенство') }}</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" name="payment_type" value="partial" class="form-check-input" id="edit-payment-partial" {{ $room->payment_type == 'partial' ? 'checked' : '' }}>
                        <label class="form-check-label" for="edit-payment-partial">{{ __('Платит частично') }}</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12" id="edit-partial-amount-container" style="{{ $room->payment_type == 'partial' ? '' : 'display: none;' }}">
            <div class="form-group">
                {{ Form::label('partial_amount', __('Сумма которую платит работник'), ['class' => 'form-label']) }}<x-required></x-required>
                <div class="input-group">
                    {{ Form::number('partial_amount', null, ['class' => 'form-control', 'placeholder' => __('Сумма'), 'step' => '0.01', 'min' => 0, 'id' => 'edit-partial-amount-input']) }}
                    <span class="input-group-text">€</span>
                </div>
                <small class="text-muted">{{ __('Остальное платит агенство') }}</small>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Отмена') }}" class="btn btn-secondary" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Обновить') }}" class="btn btn-primary">
</div>
{{ Form::close() }}

<script>
(function() {
    var partialRadio = document.getElementById('edit-payment-partial');
    var partialContainer = document.getElementById('edit-partial-amount-container');
    var partialInput = document.getElementById('edit-partial-amount-input');
    var radios = document.querySelectorAll('input[name="payment_type"]');

    function togglePartialAmount() {
        if (partialRadio.checked) {
            partialContainer.style.display = 'block';
            partialInput.required = true;
        } else {
            partialContainer.style.display = 'none';
            partialInput.required = false;
        }
    }

    radios.forEach(function(radio) {
        radio.addEventListener('change', togglePartialAmount);
    });

    togglePartialAmount();
})();
</script>
