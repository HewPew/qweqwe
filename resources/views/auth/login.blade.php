@extends('layouts.app')

@section('title', 'Авторизация')
@section('class-page', 'login-page')

@section('content')

<div class="container d-flex align-items-center">
    <div class="form-holder">
        <div class="row">
            <!-- Form Panel    -->
            <div class="col-lg-12 bg-white">
                <div class="form d-flex align-items-center">
                    <div class="content">
                        <form class="m-center" method="POST" action="{{ route('login') }}">
                            @csrf

                            <h2>{{ __('Пожалуйста, авторизуйтесь') }}</h2>
                            <hr>

                            <div class="form-group">
                                <p>{{ __('E-Mail') }}</p>
                                <input id="email" type="email" placeholder="Email..." class="input-material @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <p>{{ __('Пароль') }}</p>

                                <div class="field field--password">
                                    <i class="fa fa-eye-slash"></i>
                                    <input data-toggle="password" id="password" type="password" placeholder="Пароль..." class="input-material @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                                </div>

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group terms-conditions">
                                <input id="remember" name="remember" type="checkbox" {{ old('remember') ? 'checked' : '' }} data-msg="Данное поле обязательно" class="checkbox-template">
                                <label for="remember">{{ __('Запомнить меня') }}</label>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                {{ __('Войти') }}
                            </button>

                            @if (Route::has('password.request'))
                                <a hidden class="btn btn-link" href="{{ route('password.request') }}">
                                    {{ __('Забыли пароль?') }}
                                </a>
                            @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
