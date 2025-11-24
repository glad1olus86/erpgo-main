{{ Form::model($room, ['route' => ['room.update', $room->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row ">
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('hotel_id', __('Отель'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('hotel_id', $hotels, null, ['class' => 'form-control select', 'placeholder' => __('Выберите отель'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('room_number', __('Номер комнаты'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('room_number', null, ['class' => 'form-control', 'placeholder' => __('Введите номер комнаты'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('capacity', __('Вместимость'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::number('capacity', null, ['class' => 'form-control', 'placeholder' => __('Введите вместимость'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('price', __('Цена за ночь'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::number('price', null, ['class' => 'form-control', 'placeholder' => __('Введите цену'), 'step' => '0.01', 'required' => 'required']) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Отмена') }}" class="btn  btn-secondary" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Обновить') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}
