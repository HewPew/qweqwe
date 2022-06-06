@php $user_role_text = \App\Http\Controllers\ProfileController::getUserRole(); @endphp
@php $user_role = \App\Http\Controllers\ProfileController::getUserRole(false); @endphp

<!-- Side Navbar -->
<nav class="side-navbar">
    <!-- Sidebar Header-->
    <div class="sidebar-header d-flex align-items-center">
        <div class="title">
            <article>
                <div class="badge badge-rounded bg-green">
                    {{ $user_role_text }}
                </div>
            </article>
        </div>
    </div>
    <!-- Sidebar Navidation Menus-->
    <span class="heading">
        Меню
    </span>
    <ul class="list-unstyled">
        <li>
            <a href="{{ route('forms', ['type' => 'protokol']) }}" class="bg-primary text-white"><i class="fa fa-plus"></i> Сделать расчет</a>
        </li>

        @role(['admin'])
            <li><a href="{{ route('renderElements', 'ListKs') }}"><i class="icon-padnote"></i>Реестр ед. расценок</a></li>
        @endrole

        <li><a href="{{ route('home', 'protokol') }}"><i class="icon-padnote"></i>Реестр калькуляций</a></li>

        {{-- Если пользователь Админ --}}
        <li>
            <a href="#" data-btn-collapse="#phoenic" role="button"> <i class="icon-interface-windows"></i>Справочники</a>
            <ul id="phoenic" class="collapse list-unstyle">
                @role(['admin', 'expert', 'manager'])
                    <li><a href="{{ route('renderElements', 'Direction') }}">Направления</a></li>
                    <li><a href="{{ route('renderElements', 'TypeJob') }}">Типы работ</a></li>
                    <li><a href="{{ route('renderElements', 'Product') }}">Виды работ</a></li>
                    <li><a href="{{ route('renderElements', 'NumIndicator') }}">Количественные показатели</a></li>

                    <li><a href="{{ route('renderElements', 'ConstructSystem') }}">Конструктивные системы</a></li>
                    <li><a href="{{ route('renderElements', 'Lvl') }}">Усложняющие факторы</a></li>
                    <li><a href="{{ route('renderElements', 'Obj') }}">Назначения объектов</a></li>
                @endrole

                <li><a href="{{ route('renderElements', 'Template') }}">Мои Шаблоны</a></li>
            </ul>
        </li>

        @role(['admin', 'expert', 'manager'])
            <li>
                <a href="#" data-btn-collapse="#spis-pol" role="button"> <i class="icon-grid"></i>Настройки</a>
                <ul id="spis-pol" class="collapse list-unstyle">
                    <li><a href="{{ route('adminUsers', ['notClients' => 1]) }}">Сотрудники</a></li>
                    <li><a href="{{ route('adminUsers', [
                                'filter' => 1, 'role' => 0
                            ]) }}">Клиенты</a></li>
                </ul>
            </li>
        @endrole

        <li>
            <a href="{{ route('callback') }}"><i class="fa fa-phone"></i> Обратная связь</a>
        </li>

    </ul>
</nav>
