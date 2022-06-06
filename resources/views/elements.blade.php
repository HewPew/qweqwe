@extends('layouts.app')

@section('title', $title)
@section('sidebar', 1)

@section('custom-scripts')
    @if($isExport)
        <script type="text/javascript">
            window.onload = function () {
                setTimeout(function () {
                    exportTable('elements-table')
                }, 1500)
            };
        </script>
    @endif
@endsection

@section('content')

<!-- Добавление элемента -->
<div id="elements-modal-add" tabindex="-1" role="dialog" aria-labelledby="elements-modal-label" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Добавление {{ $popupTitle }}</h4>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
            </div>

            <form action="{{ route('addElement', $model) }}" enctype="multipart/form-data" method="POST">
                @csrf

                <div class="modal-body">
                    <p>Заполните форму внимательно и нажмите кнопку "Добавить"</p>

                    @foreach ($fields as $k => $v)
                        @php $is_required = isset($v['noRequired']) ? '' : 'required' @endphp
                        @php $default_value = isset($v['defaultValue']) ? $v['defaultValue'] : '' @endphp

                        @if($k !== 'id' )
                            <div class="form-group" data-field="{{ $k }}">
                                <label>
                                    @if($is_required) <b class="text-danger text-bold">*</b> @endif

                                    {{ $v['label'] }}</label>

                                @include('templates.elements_field')
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Добавить</button>
                    <button type="button" data-dismiss="modal" class="btn btn-secondary">Закрыть</button>
                </div>
            </form>

        </div>
    </div>
</div>

{{--NAVBAR--}}
@if((!(count($elements) >= $max) || !$max))
    <div class="col-md-12">
        <div class="row bg-light p-2">
            @role(['admin'])
                <div class="col-md-2">
                    <button type="button" data-toggle="modal" data-target="#elements-modal-add" class="@isset($_GET['continue']) TRIGGER_CLICK @endisset btn btn-success">Добавить <i class="fa fa-plus"></i></button>
                </div>
            @endrole

            <div class="col-md-10">
                <button type="button" data-toggle-show="#elements-filters" class="btn btn-info"><i class="fa fa-filter"></i> <span class="toggle-title">Показать</span> фильтры</button>
            </div>

            <!--<div class="col-md-8 text-right">
                <a href="?{{ request()->getQueryString() }}&exportTable=1" class="btn btn-dark">Экспорт таблицы <i class="fa fa-download"></i></a>
            </div>-->

            <div class="col-md-3 text-right">
                <table style="display:none;" id="export-elements-table" class="table table-striped table-sm">
                    <thead>
                        <tr>
                            @foreach ($fields as $k => $v)
                                <th>{{ $k }}</th>
                            @endforeach
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="toggle-hidden col-md-12" id="elements-filters">
                <form action="" method="GET" class="elements-form-filter">
                    <input type="hidden" name="filter" value="1">
                    <hr>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>ID</label>
                                <input
                                    type="number"
                                    value="{{ request()->get('id') }}" name="id" class="form-control" />
                            </div>
                        </div>

                        @foreach($fields as $fk => $fv)
                            @php $fv['multiple'] = true; @endphp

                            @if(!in_array($fk, ['photo']))
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>{{ $fv['label'] }}</label>

                                        @include('templates.elements_field', [
                                            'v' => $fv,
                                            'k' => $fk,
                                            'is_required' => 0,
                                            'default_value' => request()->get($fk)
                                        ])

                                    </div>
                                </div>
                            @endif
                        @endforeach

                        <div class="col-md-12">
                            <button type="submit" class="btn btn-info">Поиск</button>
                            <a href="?" class="btn btn-danger">Сбросить</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <table id="elements-table" class="table table-striped table-sm">
            <caption class="not-export">
                <a href="?export=1{{ $queryString }}" class="button-default">Экспорт таблицы</a>
                <a href="#" onclick="exportTable('elements-table');" class="button-default">Экспорт результатов</a>
            </caption>
            <thead>
                <tr>
                    {{--HASH_ID--}}
                    <th title="Ключ: id" width="60">
                        ID
                        <a class="not-export" href="?orderBy={{ $orderBy === 'DESC' ? 'ASC' : 'DESC' }}&orderKey=id">
                            <i class="fa fa-sort"></i>
                        </a>
                    </th>

                    @if($model == 'Product')
                        <th class="not-export">График</th>
                    @endif

                    @foreach ($fields as $k => $v)
                        @if(!isset($v['hidden']))
                            <th title="Ключ: {{ $k }}" data-key="{{ $k }}">
                                {{ $v['label'] }}

                                <a class="not-export" href="?orderBy={{ $orderBy === 'DESC' ? 'ASC' : 'DESC' }}&orderKey={{ $k }}">
                                    <i class="fa fa-sort"></i>
                                </a>
                            </th>
                        @endif
                    @endforeach

                    @role($rolesDelete)
                        {{--УДАЛЕНИЕ--}}
                        <th class="not-export" width="60">#</th>
                    @endrole
                </tr>
            </thead>
            <tbody>
            @if(count($elements) > 0)

                @foreach ($elements as $el)
                    <tr>
                        <td class="td-option">{{ $el->id }}</td>

                        {{-- <ГРАФИК> --}}
                        @if($model == 'Product')
                            <td class="not-export">
                                <a data-toggle="modal" data-target="#chart-modal-{{ $el->id }}-add" href="#">
                                    <canvas width="150" height="100" id="chart-{{ $el->id }}"></canvas>
                                </a>

                                <div id="chart-modal-{{ $el->id }}-add" tabindex="-1" role="dialog" aria-hidden="true" class="modal not-export fade text-left">
                                    <div role="document" class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title">График</h4>
                                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
                                            </div>

                                            <div class="modal-body">
                                                <ul>
                                                    <li>
                                                        <b>{{ app('App\Direction')->getName( \App\TypeJob::find($el->type_job_id)->direction_id ) }}</b>
                                                        <ul>
                                                            <li>
                                                                {{ app('App\TypeJob')->getName($el->type_job_id) }}

                                                                <ul>
                                                                    <li>{{ $el->name }}</li>
                                                                </ul>
                                                            </li>
                                                        </ul>
                                                    </li>

                                                    <p>Ед.изм: {{ \App\NumIndicator::find($el->num_indicator_id)->unit }}</p>
                                                </ul>

                                                <canvas width="100%" height="100" id="chart-modal-{{ $el->id }}"></canvas>
                                            </div>

                                            <div class="modal-footer">
                                                <a href="#" id="chart-btn-calc-{{ $el->id }}" target="_blank" class="btn btn-sm btn-success btn-disabled">Перейти в калькуляцию</a>
                                                <a href="#" id="chart-btn-listks-{{ $el->id }}" target="_blank" class="btn btn-sm btn-success btn-disabled">Перейти к расценке</a>
                                                <button type="button" data-dismiss="modal" class="btn btn-sm btn-secondary">Закрыть</button>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                @php
                                    $y = \App\ListKs::where('product_id', $el->id)->where('active', 1)->orderBy('count_indicator', 'asc')->get()->unique('count_indicator');
                                    $x = \App\ListKs::where('product_id', $el->id)->where('active', 1)->orderBy('count_indicator', 'asc')->get()->unique('count_indicator');
                                @endphp

                                <script type="text/javascript">
                                    let ctx{{ $el->id }} = document.querySelectorAll('#chart-{{ $el->id }}, #chart-modal-{{ $el->id }}');

                                    ctx{{ $el->id }}.forEach(ctx => {
                                        let isCtxSmall = ctx.id == 'chart-{{ $el->id }}'

                                        ctx = ctx.getContext('2d')

                                        let listKsData = @json(\App\ListKs::where('product_id', $el->id)->get()),
                                            btnChart = document.getElementById('chart-btn-calc-{{ $el->id }}'),
                                            btnChartListks = document.getElementById('chart-btn-listks-{{ $el->id }}')

                                        let myChart = new Chart(ctx, {
                                            type: 'line',
                                            data: {
                                                labels: [ // X
                                                    @foreach($x as $ListKsProduct)
                                                        {{ $ListKsProduct->count_indicator }},
                                                    @endforeach
                                                ],
                                                datasets: [{
                                                    label: 'Ед.расц.',
                                                    backgroundColor: 'rgb(255, 99, 132)',
                                                    borderColor: 'rgb(255, 99, 132)',
                                                    borderWidth: 1,
                                                    pointBorderWidth: 1,
                                                    pointRadius: isCtxSmall ? 0 : 2,
                                                    data: [ // Y
                                                        @foreach($y as $ListKsProduct)
                                                            {{ $ListKsProduct->ks_natur }},
                                                        @endforeach
                                                    ],
                                                    fill: false,
                                                }]
                                            },
                                            options: {
                                                scales: {
                                                    y: {
                                                        beginAtZero: true
                                                    }
                                                },
                                                plugins: {
                                                    legend: {
                                                        display: false
                                                    },
                                                    zoom: {
                                                        zoom: {
                                                            wheel: {
                                                                enabled: !isCtxSmall,
                                                            },
                                                            pinch: {
                                                                enabled: !isCtxSmall
                                                            },
                                                            drag: {
                                                                enabled: true,
                                                            },
                                                            mode: 'y',
                                                        }
                                                    },
                                                    tooltip: {
                                                        callbacks: {
                                                            label (context) {
                                                                let label = context.dataset.label || '';
                                                                let count = Number(context.label), price = context.parsed.y

                                                                let listKsId = listKsData.filter(item => item.count_indicator === count && item.ks_natur === price)

                                                                console.log(listKsId)

                                                                if(label) {
                                                                    label += ': '
                                                                }

                                                                label += context.parsed.y

                                                                if(listKsId.length) {
                                                                    label += `(#${listKsId[0].id})`

                                                                    $(btnChartListks)
                                                                        .removeClass('btn-disabled')
                                                                        .text('Перейти к расценке (#'+listKsId[0].id+')')
                                                                        .attr('href', '/elements/ListKs?filter=1&id=' + listKsId[0].id)

                                                                    $(btnChart)
                                                                        .removeClass('btn-disabled')
                                                                        .text('Перейти в калькуляцию (#'+listKsId[0].anketa_id+')')
                                                                        .attr('href', '/anketa/' + listKsId[0].anketa_id)
                                                                }

                                                                return label;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        });
                                    })
                                </script>
                            </td>
                        @endif
                        {{-- </ГРАФИК> --}}

                        @foreach ($el->fillable as $elIndex => $elK)
                            @if(!(isset($notShowHashId) && $elK === 'hash_id') && !isset($fields[$elK]['hidden']))
                                <td class="td-option">
                                    @if($elK === $editOnField)
                                        {{-- Если пользователь Менеджер --}}
                                        @role($otherRoles)
                                            <a href="" data-toggle="modal" data-target="#elements-modal-{{ $el->id }}-add" class="text-info">
                                        @endrole
                                                @endif

                                                @if($elK === 'products_id' || $elK === 'user_id' || $elK === 'direction_id' || $elK === 'type_job_id' || $elK === 'num_indicator_id')
                                                    @if($elK === 'direction_id')
                                                        {{ app('App\Direction')->getName($el->$elK) }}
                                                    @elseif ($elK === 'user_id')
                                                        {{ app('App\User')->getName($el->$elK) }}
                                                    @elseif ($elK == 'products_id')
                                                        {{ app('App\Product')->getName($el->$elK) }}
                                                    @elseif ($elK == 'type_job_id')
                                                        {{ app('App\TypeJob')->getName($el->$elK) }}
                                                    @elseif ($elK == 'num_indicator_id')
                                                        {{ app('App\NumIndicator')->getName($el->$elK) }}
                                                    @endif
                                                @else
                                                    @if($elK === 'date' || strpos($elK, '_at') > 0)
                                                        {{ date('d-m-Y H:i:s', strtotime($el[$elK])) }}
                                                    @else
                                                        <span class="not-export">
                                                            @switch($elK)
                                                                @case('round_ceil')
                                                                    {{ $el[$elK] === 0 ? 'Нет' : 'Да' }}
                                                                @break

                                                                @default
                                                                    {{ mb_strimwidth($el[$elK], 0, 50, '...') }}
                                                                @break
                                                            @endswitch
                                                        </span>
                                                        <span class="to-export">
                                                            {{ $el[$elK] }}
                                                        </span>
                                                    @endif
                                                @endif

                                                @if($elK === $editOnField)
                                                    {{-- Если пользователь Менеджер --}}
                                            @role($otherRoles)
                                            </a>

                                            <div id="elements-modal-{{ $el->id }}-add" tabindex="-1" role="dialog" aria-hidden="true" class="modal not-export fade text-left">
                                                <div role="document" class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Редактирование {{ $popupTitle }}</h4>
                                                            <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
                                                        </div>

                                                        <form action="{{ route('updateElement', ['type' => $model, 'id' => $el->id ]) }}" enctype="multipart/form-data" method="POST">
                                                            @csrf

                                                            <div class="modal-body">
                                                                @foreach ($fields as $k => $v)
                                                                    @php $is_required = isset($v['noRequired']) ? '' : 'required' @endphp

                                                                    @if($k !== 'id' && !isset($v['hidden']))
                                                                        <div class="form-group" data-field="{{ $k }}">
                                                                            <label>
                                                                                @if($is_required) <b class="text-danger text-bold">*</b> @endif
                                                                                {{ $v['label'] }}</label>

                                                                            @include('templates.elements_field', [
                                                                                'v' => $v,
                                                                                'k' => $k,
                                                                                'default_value' => $el[$k]
                                                                            ])

                                                                            {{--Синхронизация полей--}}
                                                                            @isset($v['syncData'])
                                                                                @foreach($v['syncData'] as $syncData)
                                                                                    <a href="{{ route('syncDataElement', [
                                                                                            'model' => $syncData['model'],
                                                                                            'fieldFind' => $syncData['fieldFind'],
                                                                                            'fieldFindId' => $el['id'],
                                                                                            'fieldSync' => $k,
                                                                                            'fieldSyncValue' => $el[$k]
                                                                                        ]) }}" target="_blank" class="text-info btn-link"><i class="fa fa-spinner"></i> Синхронизация с: {{ $syncData['text'] }}</a>
                                                                                @endforeach
                                                                            @endisset
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="submit" class="btn btn-success">Сохранить</button>
                                                                <button type="button" data-dismiss="modal" class="btn btn-secondary">Закрыть</button>
                                                            </div>
                                                        </form>

                                                    </div>
                                                </div>
                                            </div>
                                        @endrole
                                    @endif

                                </td>
                            @endif
                        @endforeach

                        @role($rolesDelete)
                            <td class="not-export td-option">
                                <a href="{{ route('removeElement', ['type' => $model, 'id' => $el->id ]) }}" class="ACTION_DELETE btn btn-danger"><i class="fa fa-trash"></i></a>
                            </td>
                        @endrole

                    </tr>
                @endforeach

            @else
                <tr class="not-export text-center">
                    <td colspan="{{ count($fields)+4 }}">Элементы не найдены</td>
                </tr>
            @endif
            </tbody>
        </table>

        <div>
            @if(count($elements) > 1)
                {{ $elements->appends($_GET)->render() }}
            @endif

            @include('templates.take_form')

            @if(auth()->user()->isAdmin())
                <p>Элементов всего: {{ $elements_count_all }}</p>
            @else
                <p>Элементов: {{ $elements->total() }}</p>
            @endif
        </div>
    </div>
</div>

@endsection
