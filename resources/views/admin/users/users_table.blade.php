@php
    $strUsers = $isClients ? '&filter=1&role=0' : '&filter=1&notClients=1';
@endphp

<table id="elements-table" class="table table-striped table-sm">
    <thead>
    <tr>
        <th width="60">ID
            <a href="?orderBy={{ $orderBy === 'DESC' ? 'ASC' : 'DESC' }}&orderKey=id{{ $strUsers }}">
                <i class="fa fa-sort"></i>
            </a>
        </th>
        <th>ФИО
            <a href="?orderBy={{ $orderBy === 'DESC' ? 'ASC' : 'DESC' }}&orderKey=name{{ $strUsers }}">
                <i class="fa fa-sort"></i>
            </a>
        </th>
        <th>E-mail
            <a href="?orderBy={{ $orderBy === 'DESC' ? 'ASC' : 'DESC' }}&orderKey=email{{ $strUsers }}">
                <i class="fa fa-sort"></i>
            </a>
        </th>
        <th>Телефон
            <a href="?orderBy={{ $orderBy === 'DESC' ? 'ASC' : 'DESC' }}&orderKey=phone{{ $strUsers }}">
                <i class="fa fa-sort"></i>
            </a>
        </th>
        <th>Компания
            <a href="?orderBy={{ $orderBy === 'DESC' ? 'ASC' : 'DESC' }}&orderKey=company{{ $strUsers }}">
                <i class="fa fa-sort"></i>
            </a>
        </th>
        <th width="60">Роль
            <a href="?orderBy={{ $orderBy === 'DESC' ? 'ASC' : 'DESC' }}&orderKey=role{{ $strUsers }}">
                <i class="fa fa-sort"></i>
            </a>
        </th>

        @admin
            <th>#</th>
        @endadmin
    </tr>
    </thead>
    <tbody>
    @foreach ($users as $user)
        <tr>
            <td>{{ $user->id }}</td>
            <td>
                <a href="" data-toggle="modal" data-target="#users-modal-edit-{{ $user->id }}">{{ $user->name }}</a>

                @admin
                    <!-- Редактирование элемента -->
                    <div id="users-modal-edit-{{ $user->id }}" tabindex="-1" role="dialog" aria-hidden="true" class="modal fade text-left">
                        <div role="document" class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Редактирование пользователя "{{ $user->name }}"</h4>
                                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
                                </div>

                                <form method="POST" enctype="multipart/form-data" action="{{ route('adminUpdateUser', $user->id) }}">
                                    @csrf

                                    <div class="modal-body">

                                        <div class="form-group">
                                            <input type="text" value="{{ $user->name }}" name="name" placeholder="Ваше имя" class="form-control">
                                        </div>

                                        <div class="form-group">
                                            <input type="email" value="{{ $user->email }}" name="email" placeholder="E-mail" class="form-control">
                                        </div>

                                        <div class="form-group">
                                            <input type="tel" value="{{ $user->phone }}" name="phone" placeholder="Телефон (+7_______)" class="form-control MASK_PHONE">
                                        </div>

                                        <div class="form-group">
                                            <input type="text" data-field="Company_name" value="{{ $user->company }}" name="company" placeholder="Компания" class="form-control">
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
                                                    <option disabled selected value="{{ $user->role }}">{{ \App\Http\Controllers\ProfileController::getUserRole(true, $user->id) }}</option>
                                                    <option value="1">Сотрудник</option>
                                                    <option value="777">Администратор</option>
                                                    <option value="778">Эксперт</option>
                                                @endif
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Заблокирован</label>
                                            <select name="blocked">
                                                <option
                                                    @if($user->blocked === 0) selected @endif
                                                value="0">Нет</option>
                                                <option
                                                    @if($user->blocked === 1) selected @endif
                                                value="1">Да</option>
                                            </select>
                                        </div>

                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success">Сохранить</button>
                                        <button type="button" data-dismiss="modal" class="btn btn-secondary">Закрыть</button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                @endadmin
            </td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->phone }}</td>
            <td>{{ $user->company }}</td>
            <td>{{ \App\Http\Controllers\ProfileController::getUserRole(true, $user->id) }}</td>

            @admin
                <td class="td-option"><a href="{{ route('adminDeleteUser', $user->id) }}" class="ACTION_DELETE btn btn-danger"><i class="fa fa-trash"></i></a></td>
            @endadmin
        </tr>
    @endforeach
    </tbody>
</table>
