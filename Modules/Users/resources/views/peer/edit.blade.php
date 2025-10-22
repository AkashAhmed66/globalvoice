@extends('smsconfig::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('smsconfig.name') !!}</p>
@endsection
