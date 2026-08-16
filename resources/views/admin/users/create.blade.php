@extends('layouts.admin')

@section('page-title', __('navigation.create_user'))
@section('title', __('navigation.create_user'))

@section('content')
<div class="inner-body">
    <div class="admin-test-card">
        <h2>{{ __('navigation.create_user') }}</h2>
        <p>{{ __('users.create_placeholder') }}</p>
    </div>
</div>
@endsection
