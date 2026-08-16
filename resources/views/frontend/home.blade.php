@extends('layouts.frontend')

@section('title', __('navigation.home'))

@section('content')
<div class="fe-hero">
    <p class="eyebrow">{{ __('navigation.home') }}</p>
    <h1>{{ __('common.frontend_test_title') }}</h1>
    <p>{{ __('common.frontend_test_body') }}</p>
</div>
@endsection
