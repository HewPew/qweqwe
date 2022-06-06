@php $photoInputId = sha1(time()); @endphp

<!-- Добавление элемента -->
<div id="users-modal-add" tabindex="-1" role="dialog" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Добавление пользователя</h4>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
            </div>

            <form method="POST" enctype="multipart/form-data" action="{{ route('adminCreateUser') }}">
                @csrf

                <div class="modal-body">
                    <p>Заполните форму внимательно и нажмите кнопку "Добавить"</p>

                    <div class="form-group">
                        <input type="text" required name="name" placeholder="Ваше имя" class="form-control">
                    </div>

                    <div class="form-group">
                        <input type="email" required name="email" placeholder="E-mail" class="form-control">
                    </div>

                    <div class="form-group">
                        <input type="tel" name="phone" placeholder="Телефон (+7_______)" class="form-control MASK_PHONE">
                    </div>

                    <div class="form-group">
                        <input type="text" data-field="Company_name" name="company" placeholder="Компания" class="form-control">
                    </div>

                    <div class="form-group">
                        <div class="field field--password">
                            <i class="fa fa-eye-slash"></i>
                            <input data-toggle="password" id="password" type="password" placeholder="Пароль..." class="form-control" name="password"  autocomplete="current-password">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Роль</label>
                        <select name="role" required class="form-control">
                            @if($isClients)
                                <option value="0">Клиент</option>
                            @else
                                <option value="1">Сотрудник</option>
                                <option value="777">Администратор</option>
                                <option value="778">Эксперт</option>
                            @endif
                        </select>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Добавить</button>
                    <button type="button" data-dismiss="modal" class="btn btn-secondary">Закрыть</button>
                </div>
            </form>

        </div>
    </div>
</div>
