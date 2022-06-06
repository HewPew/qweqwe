@extends('layouts.app')

@section('title','Обратная связь')
@section('sidebar', 1)

@section('content')

    <div class="row">
        <!-- Form Elements -->
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    @isset($_GET['success'])
                        <div class="alert alert-success">
                            Успешно отправлено!
                        </div>
                    @endisset

                    <form method="POST" action="{{ route('callback.post') }}" class="form-horizontal">
                        @csrf

                        <input type="hidden" value="Заявка на обратную связь">

                        <input type="hidden" name="from" value="{{ auth()->user()->name . ' , ' . auth()->user()->phone }}" placeholder="От кого?" class="form-control">

                        <div class="form-group row">
                            <label class="col-sm-3 form-control-label">Текст обращения:</label>
                            <div class="col-sm-9">
                                <textarea name="message" class="form-control" cols="30" rows="10"></textarea>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-9">
                                <button type="submit" class="btn btn-primary">Отправить</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
