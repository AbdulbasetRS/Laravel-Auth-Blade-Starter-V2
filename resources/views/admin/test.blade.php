@extends('layouts.admin')

@section('page-title', __('navigation.dashboard'))
@section('title', __('navigation.dashboard'))

@section('content')
<div class="admin-test-card">
    <h2>{{ __('common.admin_test_title') }}</h2>
    <p>{{ __('common.admin_test_body') }}</p>
</div>
@endsection
