@extends('layouts.app')

@section('title', $title)
@section('sidebar', 1)
@section('class-page', 'anketa-' . $type_ankets)

@section('custom-scripts')
    @if($isExport)
        <script type="text/javascript">
            window.onload = function () {
                setTimeout(function () {
                    exportTable('ankets-table')
                }, 1500)
            };
        </script>
    @endif
@endsection

@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">

                    {{--<div class="col-md-12">
                        <div class="row bg-light p-2">
                            <!--<div class="col-md-6">
                                <button type="button" data-toggle-show="#ankets-filters" class="btn btn-info"><i class="fa fa-cog"></i> <span class="toggle-title">Настроить</span> колонки</button>
                            </div>-->

                            <div class="toggle-hidden p-3" id="ankets-filters">
                                <form action="{{ route('home.save-fields', $type_ankets) }}" method="POST" class="ankets-form">
                                    @csrf

                                    @foreach($anketsFields as $fieldKey => $fieldValue)
                                        @isset($fieldsKeys[$fieldValue])

                                            <label>
                                                <input
                                                    @if(session()->exists("fields_$type_ankets"))
                                                        @isset(session()->get("fields_$type_ankets")[$fieldValue])
                                                        checked
                                                        @endisset
                                                    @else
                                                        checked
                                                    @endif

                                                    type="checkbox" name="{{ $fieldValue }}" data-value="{{ $fieldKey+1 }}" />
                                                {{ (isset($fieldsKeys[$fieldValue]['name'])) ? $fieldsKeys[$fieldValue]['name'] : $fieldsKeys[$fieldValue] }} &nbsp;
                                            </label>
                                        @endisset
                                    @endforeach

                                    <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-save"></i> Сохранить в сессию</button>
                                </form>
                            </div>
                        </div>
                    </div>--}}

                    <ul class="nav nav-tabs" id="filter-groups" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="filter-group-1-tab" data-toggle="tab" href="#filter-group-1" role="tab" aria-controls="filter-group-1" aria-selected="true"><i class="fa fa-filter"></i> Фильтры</a>
                        </li>
                    </ul>

                    <form action="" method="GET" class="tab-content ankets-form-filter mb-3 pt-3" id="filter-groupsContent">
                        <div class="text-center">
                            <img src="{{ asset('images/loader.gif') }}" width="30" class="mb-4" />
                        </div>
                    </form>

                    @if(count($ankets) > 0)
                        <table id="ankets-table" class="ankets-table table table-striped table-sm">
                            <caption class="not-export">
                                <a href="?export=1{{ $queryString }}" class="button-default">Экспорт таблицы</a>
                                <a href="#" onclick="exportTable('ankets-table');" class="button-default">Экспорт результатов</a>
                            </caption>
                            <thead>
                                <tr>
                                    <th width="30">ID</th>

                                    @foreach($anketsFields as $field)
                                        @isset($fieldsKeys[$field])
                                            <th @if($field === 'date') style="width: 100px;" @endif data-field-key="{{ $field }}">
                                                {{ (isset($fieldsKeys[$field]['name'])) ? $fieldsKeys[$field]['name'] : $fieldsKeys[$field] }}

                                                <a class="not-export" href="?orderBy={{ $orderBy === 'DESC' ? 'ASC' : 'DESC' }}&orderKey={{ $field . $queryString }}">
                                                    <i class="fa fa-sort"></i>
                                                </a>
                                            </th>
                                        @endisset
                                    @endforeach

                                    <th class="not-export">#</th>
                                    <th class="not-export">#</th>
                                    <th class="not-export">#</th>

                                    @role(['admin'])
                                        <th class="not-export">#</th>
                                    @endrole
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ankets as $anketaKey => $anketa)
                                    <tr data-field="{{ $anketaKey }}">
                                        <td>{{ $anketa->id }}</td>
                                        @foreach($anketsFields as $anketaTDkey)
                                            @if(isset($fieldsKeys[$anketaTDkey]))
                                                <td data-field-key="{{ $anketaTDkey }}">
                                                    @if($anketaTDkey === 'date' || strpos($anketaTDkey, '_at') > 0)
                                                        {{ date('d-m-Y', strtotime($anketa[$anketaTDkey])) }}
                                                    @elseif($anketaTDkey === 'photos')

                                                        @if($anketa[$anketaTDkey])
                                                            @php $photos = explode(',', $anketa[$anketaTDkey]); @endphp

                                                            @foreach($photos as $phI => $ph)
                                                                @if($phI == 0)
                                                                    <a href="{{ Storage::url($ph) }}" data-fancybox="gallery_{{ $anketa->id }}"><i class="fa fa-search"></i> ({{ count($photos) }})</a>
                                                                @else
                                                                    <a href="{{ Storage::url($ph) }}" data-fancybox="gallery_{{ $anketa->id }}"></a>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    @elseif(preg_match('/coefs/', $anketaTDkey))
                                                        <pre>{{ $anketa[$anketaTDkey] }}</pre>
                                                    @elseif($anketaTDkey === 'user_id')
                                                        {{ app("App\\User")->getName($anketa[$anketaTDkey])  }}
                                                    @else
                                                        @if(gettype($anketa[$anketaTDkey]) == 'integer')
                                                            {{ in_array($anketaTDkey, ['sum_ks', 'sum_kd', 'kd', 'kd_final']) ? number_format($anketa[$anketaTDkey], 2, '.', '') : $anketa[$anketaTDkey] }}
                                                        @else
                                                            {{ mb_strimwidth($anketa[$anketaTDkey], 0, 50, '...') }}
                                                        @endif
                                                    @endif
                                                </td>
                                            @endif
                                        @endforeach

                                        {{-- @role(['admin', 'manager', $currentRole])
                                            <td class="td-option">
                                                <a href="{{ route('forms.get', $anketa->id) }}" class="btn btn-info"><i class="fa fa-edit"></i></a>
                                            </td>
                                        @endrole--}}

                                        <td class="td-option not-export">
                                            <a href="{{ route('forms.print', [
                                            'id' => $anketa->id,
                                            'not_show_double_calc' => 1
                                        ]) }}" target="_blank">Скачать КП</a>
                                        </td>

                                        <td class="td-option not-export">
                                            <a onclick="if(!confirm('Отправить калькуляцию на почту?')) return false;" href="{{ route('forms.print', [
                                            'id' => $anketa->id,
                                            'not_show_double_calc' => 1,
                                            'sendMailPdf' => 1
                                        ]) }}" target="_blank">
                                                Отправить протокол на почту
                                            </a>
                                        </td>


                                        <td class="td-option not-export">
                                            @if(auth()->user()->name === $anketa->user_name || auth()->user()->isAdmin())
                                                <a href="{{ route('forms.get', [
                                                    'id' => $anketa->id
                                                ]) }}" target="_blank">Редактировать</a>
                                            @endif
                                        </td>

                                        @role(['admin'])
                                            <td class="td-option not-export">
                                                <form action="{{ route('forms.delete', $anketa->id) }}" onsubmit="if(!confirm('Хотите удалить?')) return false;" method="POST">
                                                    @csrf
                                                    {{ method_field('DELETE') }}
                                                    <button type="submit" class="btn btn-danger"><i class="fa fa-remove"></i></button>
                                                </form>
                                            </td>
                                        @endrole

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <hr>

                    @if(count($ankets) > 0)
                        {{ $ankets->appends($_GET)->render() }}
                    @endif
                @endif

                @include('templates.take_form')

                @isset($_GET['filter'])
                    <p class="text-success">Найдено: <b>{{ $anketasCountResult }}</b></p>
                @endisset

                <p>Всего: <b>{{ $totalCountForms }}</b></p>
            </div>

        </div>
    </div>

@endsection
