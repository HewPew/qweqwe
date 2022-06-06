@extends('layouts.app')

@section('title','Профиль')
@section('sidebar', 1)

@section('content')

<div class="row">
    <!-- Form Elements -->
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
            <form action="{{ route('updateProfile') }}" method="POST" class="form-horizontal">
                @csrf

                <p class="text"><strong>#{{ $user->id }}</strong><br/></p>

                <div class="form-group row">
                    <label class="col-sm-3 form-control-label">ФИО:</label>
                    <div class="col-sm-9">
                        <input type="text" value="{{ $user->name }}" name="name" class="form-control">
                    </div>
                </div>

                <div class="line"></div>
                <div class="form-group row">
                    <label class="col-sm-3 form-control-label">E-mail:</label>
                    <div class="col-sm-9">
                        <input  type="text" value="{{ $user->email }}" name="email" class="form-control">
                    </div>
                </div>

                <div class="line"></div>
                <div class="form-group row">
                    <label class="col-sm-3 form-control-label">Телефон:</label>
                    <div class="col-sm-9">
                        <input type="tel" value="{{ $user->phone }}" placeholder="Телефон (+7__________)" name="phone" class="form-control MASK_PHONE">
                    </div>
                </div>

                <div class="line"></div>
                <div class="form-group row">
                    <label class="col-sm-3 form-control-label">Компания:</label>
                    <div class="col-sm-9">
                        <input data-field="Company_name" type="text" class="form-control" name="company" value="{{ $user->company }}">
                    </div>
                </div>

                <div class="line"></div>
                <div class="form-group row">
                    <div class="col-sm-4 offset-sm-3">
                        <a href="{{ route('home') }}" class="btn btn-info"><i class="fa fa-arrow-left"></i> Назад</a>

                        <button type="submit" class="btn btn-success">Сохранить</button>
                    </div>
                </div>

            </form>
            </div>
        </div>
    </div>
</div>

@endsection
