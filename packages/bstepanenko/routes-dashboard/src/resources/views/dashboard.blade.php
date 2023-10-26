@extends('routes-dashboard::layouts.app')

@section('content')
    @include('routes-dashboard::parts.statistic')
    @include('routes-dashboard::parts.missed-routes-table')
@endsection
