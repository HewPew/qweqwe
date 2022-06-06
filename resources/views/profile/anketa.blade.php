@extends('layouts.app')

@section('title', $title)
@section('sidebar', 1)
@section('class-page', 'page-anketa anketa-' . $type_anketa)

@section('custom-styles')
    @if(auth()->user()->role !== 777)
    <style type="text/css">
        .calculation-result__ks .vue-numeric-input {
            width: 57px!important;
        }

        .calculation-result__ks .numeric-input {
            display: none!important;
        }
    </style>
    @endif
@endsection

@section('content')

<calculation
    @if(isset($anketa_route))
    action="{{ route($anketa_route, $id) }}"
    @else
    action="{{ route('addAnket') }}"
    @endif

    @isset($anketa_id)
        anketa_id="{{ $anketa_id }}"
    @endisset

    title="{{ $title }}"
    default_current_date="{{ $default_current_date }}"
    type_anketa="{{ $type_anketa }}"
></calculation>

@endsection
