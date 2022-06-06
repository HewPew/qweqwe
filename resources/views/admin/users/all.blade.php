@extends('layouts.app')

@php
    $isClients = isset($_GET['role']) ? $_GET['role'] === "0" : 0;
    $roleText = isset($_GET['role']) ? ($_GET['role'] === "0" ? 'Клиент' : 'Сотрудник') : 'Сотрудник'
@endphp

@section('title', $isClients ? 'Клиенты' : 'Сотрудники')
@section('sidebar', 1)

@section('content')

    @admin
        @include('admin.users.users_add_modal')
    @endadmin

    <div class="col-md-12">
        <div class="row bg-light p-2">
            <div class="col-md-6">
                @admin
                    @if(!$isClients)
                        <button type="button" data-toggle="modal" data-target="#users-modal-add" class="btn btn-success">Добавить {{ $roleText }}а <i class="fa fa-plus"></i></button>
                    @endif
                @endadmin
            </div>
            <div class="col-md-6 text-right">
                <button type="button" onclick="exportTable('elements-table', '{{ $roleText }}', '{{ $roleText }}.xls')" class="btn btn-dark">Экспорт <i class="fa fa-download"></i></button>
            </div>
        </div>
        <div class="row bg-light p-2">
            <div class="col-md-12">
                <form action="" class="row" method="GET">
                    @csrf
                    <input type="hidden" name="filter" value="1" />

                    <input type="hidden" name="role" value="{{ $isClients ? 0 : 1 }}">

                    <div class="col-md-2 form-group">
                        <input type="text" value="{{ request()->get('name') }}" name="name" placeholder="ФИО" class="form-control">
                    </div>

                    <div class="col-md-2 form-group">
                        <input type="text" name="email" value="{{ request()->get('email') }}" placeholder="E-mail" class="form-control">
                    </div>

                    <div class="col-md-3 form-group">
                        <input type="submit" class="btn btn-success btn-sm" value="Поиск">
                        <a href="{{ route('adminUsers',
                            $isClients ? ['filter' => 1, 'role' => 0] : ['filter' => 1, 'notClients' => 1]
                        ) }}" class="btn btn-danger btn-sm">Сбросить</a>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @include('admin.users.users_table')

            <div>
                {{ $users->appends($_GET)->render() }}

                <p>Количество элементов: {{ count($users) }}</p>

                @if($errors)
                    @foreach($errors as $error)
                        <p class="text-red">{{ $error[0] }}</p>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

@endsection
