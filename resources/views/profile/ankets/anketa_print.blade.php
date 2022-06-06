@extends('layouts.app')

@section('title', $title)
@section('class-page', 'page-anketa anketa-' . $type_anketa)

@section ('custom-styles')
    @isset($_GET['sendMailPdf'])
        <style>
            * {
                font-family: "DejaVu Sans"!important;
            }
        </style>
    @endisset

    <style>
        @media print
        {
            .no-print, .no-print *
            {
                display: none !important;
            }
        }

        .d-flex {
            display: block!important;
        }

        .calculation-result__table {
            max-height: 10000px!important;
            overflow: visible!important;
        }
    </style>
@endsection

@section('content')

    <div class="row no-print d-flex justify-content-center text-center">
        <a href="#" onclick="window.print(); return false;">Скачать PDF (печать)</a>
    </div>

    <div class="row">
        <div class="col-md-1"></div>

        <!-- Анкета -->
        <div class="col-md-10">
            <div class="card ANKETA_PRINT">
                <div class="card-body">

                    <h1>Расчет стоимости работ</h1>


                    <article class="anketa anketa-fields">

                        <calculation-result
                            squere="2"
                            is_print="1"

                            @isset($_GET['sendMailPdf'])
                                send_mail_pdf="true"
                                anketa_id="{{ $id }}"
                            @endisset

                            @isset($_GET['email'])
                                email="{{ $_GET['email'] }}"
                            @endisset

                            @isset($_GET['phone'])
                                phone="{{ $_GET['phone'] }}"
                            @endisset

                            @isset($_GET['fio'])
                                fio="{{ $_GET['fio'] }}"
                            @endisset

                            @isset($_GET['company'])
                                company="{{ $_GET['company'] }}"
                            @endisset

                            @if($not_show_double_calc)
                            not_show_double_calc="{{ $not_show_double_calc }}"
                            @endif

                            :calc="{{ strlen($json_calc) < 500 ? Storage::disk('public')->get($json_calc) : $json_calc }}"
                            :firstCalc="{}"
                        ></calculation-result>
                    </article>

                </div>
            </div>
        </div>

        <div class="col-md-1"></div>

    </div>

    @isset($_GET['triggerPrint'])
        <script type="text/javascript">
            window.print();
        </script>
    @endisset

@endsection
