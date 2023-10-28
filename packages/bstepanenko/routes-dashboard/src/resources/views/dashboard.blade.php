@extends('routes-dashboard::layouts.app')

@section('content')
    @include('routes-dashboard::parts.statistic')
    @include('routes-dashboard::parts.tables.api-routes-table')
    @include('routes-dashboard::parts.tables.diff-routes-table')
    @include('routes-dashboard::parts.tables.missed-routes-table')
@endsection
