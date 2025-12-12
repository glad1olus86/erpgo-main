{{ Form::model($room, ['route' => ['room.update', $room->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate', 'id' => 'room_edit_form']) }}
<input type="hidden" name="redirect_to" id="redirect_to_field" value="{{ request('redirect_to', '') }}">
<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('hotel_id', __('Hotel'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('hotel_id', $hotels, null, ['class' => 'form-control select', 'placeholder' => __('Select Hotel'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('room_number', __('Room Number'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('room_number', null, ['class' => 'form-control', 'placeholder' => __('Enter number'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('capacity', __('Capacity'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::number('capacity', null, ['class' => 'form-control', 'placeholder' => __('Number of beds'), 'required' => 'required', 'min' => 1]) }}
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('monthly_price', __('Monthly Price'), ['class' => 'form-label']) }}<x-required></x-required>
                <div class="input-group">
                    {{ Form::number('monthly_price', null, ['class' => 'form-control', 'placeholder' => __('Enter price'), 'step' => '0.01', 'required' => 'required', 'min' => 0]) }}
                    <span class="input-group-text">€</span>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('payment_type', __('Who Pays'), ['class' => 'form-label']) }}<x-required></x-required>
                <div class="d-flex flex-column gap-2">
                    <div class="form-check">
                        <input type="radio" name="payment_type" value="worker" class="form-check-input" id="edit-payment-worker" {{ $room->payment_type == 'worker' ? 'checked' : '' }}>
                        <label class="form-check-label" for="edit-payment-worker">{{ __('Worker pays') }}</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" name="payment_type" value="agency" class="form-check-input" id="edit-payment-agency" {{ $room->payment_type == 'agency' ? 'checked' : '' }}>
                        <label class="form-check-label" for="edit-payment-agency">{{ __('Agency pays') }}</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" name="payment_type" value="partial" class="form-check-input" id="edit-payment-partial" {{ $room->payment_type == 'partial' ? 'checked' : '' }}>
                        <label class="form-check-label" for="edit-payment-partial">{{ __('Partial payment') }}</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12" id="edit-partial-amount-container" style="{{ $room->payment_type == 'partial' ? '' : 'display: none;' }}">
            <div class="form-group">
                {{ Form::label('partial_amount', __('Amount worker pays'), ['class' => 'form-label']) }}<x-required></x-required>
                <div class="input-group">
                    {{ Form::number('partial_amount', null, ['class' => 'form-control', 'placeholder' => __('Amount'), 'step' => '0.01', 'min' => 0, 'id' => 'edit-partial-amount-input']) }}
                    <span class="input-group-text">€</span>
                </div>
                <small class="text-muted">{{ __('The rest is paid by the agency') }}</small>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-secondary" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}

<script>
(function() {
    // Detect mobile page and set redirect field
    setTimeout(function() {
        var redirectField = document.getElementById('redirect_to_field');
        if (redirectField && !redirectField.value) {
            if (window.location.pathname.indexOf('/mobile') === 0) {
                redirectField.value = 'mobile';
            }
        }
    }, 100);

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
