{{ Form::model($hotel, ['route' => ['hotel.update', $hotel->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">

    <div class="row ">
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('name', __('Название'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Введите название отеля'), 'required' => 'required']) }}
                @error('name')
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('address', __('Адрес'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('address', null, ['class' => 'form-control', 'placeholder' => __('Введите адрес отеля'), 'required' => 'required']) }}
                @error('address')
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('rating', __('Рейтинг'), ['class' => 'form-label']) }}
                {{ Form::number('rating', null, ['class' => 'form-control', 'placeholder' => __('Рейтинг (0-5)'), 'min' => '0', 'max' => '5']) }}
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Отмена') }}" class="btn  btn-secondary" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Обновить') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}
