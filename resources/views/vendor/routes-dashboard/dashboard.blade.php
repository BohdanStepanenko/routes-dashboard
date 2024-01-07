@extends('routes-dashboard::layouts.app')
@include('routes-dashboard::parts.modals.generate-modal')
@include('routes-dashboard::parts.modals.health-modal')

@section('content')
    @include('routes-dashboard::parts.statistic')
    @include('routes-dashboard::parts.tables.api-routes-table')
    @include('routes-dashboard::parts.tables.diff-routes-table')
    @include('routes-dashboard::parts.tables.missed-routes-table')
@endsection
