<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="all,follow">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | Advantex</title>

    {{-- Disabled Cache --}}
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,700">

    <!-- Styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/suggestions-jquery@20.3.0/dist/css/suggestions.min.css" rel="stylesheet" />
    <link href="{{ mix('css/app.css') }}?v={{ time() }}" rel="stylesheet" type="text/css">
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css" integrity="sha512-H9jrZiiopUdsLpg94A333EfumgUBpO9MdbxStdeITo+KEIMaNfHNvwyjjDJb+ERPaRS6DpyRlKbvPUasNItRyw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    @auth
        <script type="text/javascript">
            window.API_TOKEN = '{{ Auth::user()->api_token }}';
            window.USER_ROLE = {{ Auth::user()->role }};
        </script>
    @endauth

    @guest
        <script type="text/javascript">
            window.API_TOKEN = '';
            window.USER_ROLE = '';
        </script>
    @endguest

    @yield('custom-styles')

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-zoom/1.2.0/chartjs-plugin-zoom.min.js" integrity="sha512-TT0wAMqqtjXVzpc48sI0G84rBP+oTkBZPgeRYIOVRGUdwJsyS3WPipsNh///ay2LJ+onCM23tipnz6EvEy2/UA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>
<body>

    <div @if(!Route::is('renderElements')) id="app" @endif class="page @yield('class-page')">
        @if(!isset($withoutHeader))
            @include('layouts.header')
        @endif

        <main class="page-content d-flex align-items-stretch">

            @hasSection ('sidebar')
                @auth
                    @include('layouts.sidebar')
                @endauth
            @endif

            @guest
                @yield('content')
            @endguest

            @auth
                <div class="content-inner">
                    @hasSection ('sidebar')
                        <!-- Хэдер-->
                        <header class="page-header">
                            <div class="container-fluid">
                                <h2 class="no-margin-bottom">@yield('title')</h2>
                            </div>
                        </header>
                    @endif

                    <!-- Контент страницы -->
                    <section class="no-padding-bottom">
                        <div class="page-container container-fluid">
                            @yield('content')
                        </div>
                    </section>

                    @if(!isset($withoutHeader))
                        <!-- Подвал -->
                        <footer class="main-footer">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <p class="text-white">Сделано по заказу КТВ Beton Group. 2022 г. Release 7.</p>
                                    </div>
                                </div>
                            </div>
                        </footer>
                    @endif

                </div>
            @endauth
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/suggestions-jquery@20.3.0/dist/js/jquery.suggestions.min.js"></script>
    <script src="https://unpkg.com/xlsx@0.18.0/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js" integrity="sha512-uURl+ZXMBrF4AwGaWmEetzrd+J5/8NRkWAvJx5sbPSSuOb0bZLqf+tOzniObO00BjHa/dD7gub9oCGMLPQHtQA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script src="{{ asset('js/app.js') }}?v={{ time() }}" type="text/javascript"></script>

    @yield('custom-scripts')
</body>
</html>
