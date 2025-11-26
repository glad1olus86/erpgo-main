{{ Form::model($worker, ['route' => ['worker.update', $worker->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('first_name', __('Имя'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('first_name', null, ['class' => 'form-control', 'placeholder' => __('Введите имя'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('last_name', __('Фамилия'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('last_name', null, ['class' => 'form-control', 'placeholder' => __('Введите фамилию'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('dob', __('Дата рождения'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::date('dob', null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('gender', __('Пол'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('gender', ['male' => __('Мужчина'), 'female' => __('Женщина')], null, ['class' => 'form-control select2', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('nationality', __('Национальность'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('nationality', null, ['class' => 'form-control', 'placeholder' => __('Введите национальность'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('registration_date', __('Дата регистрации'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::date('registration_date', null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('phone', __('Телефон'), ['class' => 'form-label']) }}
                {{ Form::text('phone', null, ['class' => 'form-control', 'placeholder' => __('Введите телефон')]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
                {{ Form::email('email', null, ['class' => 'form-control', 'placeholder' => __('Введите email')]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('document_photo', __('Фото документов'), ['class' => 'form-label']) }}
                <div class="choose-file form-group">
                    <label for="document_photo" class="form-label">
                        <input type="file" class="form-control" name="document_photo" id="document_photo"
                            data-filename="document_photo_update">
                    </label>
                    <p class="document_photo_update"></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('photo', __('Фото внешности'), ['class' => 'form-label']) }}
                <div class="choose-file form-group">
                    <label for="photo" class="form-label">
                        <input type="file" class="form-control" name="photo" id="photo"
                            data-filename="photo_update">
                    </label>
                    <p class="photo_update"></p>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Отменить') }}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Сохранить') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}
