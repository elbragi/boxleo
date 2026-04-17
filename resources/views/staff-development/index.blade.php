@extends('layouts.app')

@section('page-header')
    <div class="row align-items-center">
        <div class="col">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Staff Development</li>
            </ul>
        </div>
    </div>
@endsection

@section('content')
    <staff-development :user-id="{{ auth()->id() }}" :is-admin="{{ auth()->user()->hasRole('admin') ? 'true' : 'false' }}"/>
@endsection
