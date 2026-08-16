@extends('layouts.admin')

@section('page-title', 'Documentation')
@section('title', 'Documentation')

@section('content')
<div class="inner-body">
    <div class="docs-layout">
        <nav class="docs-nav">
            <a href="#overview" class="active">{{ __('docs.overview') }}</a>
            <a href="#stack">{{ __('docs.stack') }}</a>
            <a href="#structure">{{ __('docs.structure') }}</a>
            <a href="#design-system">{{ __('docs.design_system') }}</a>
            <a href="#localization">{{ __('docs.localization') }}</a>
            <a href="#validation">{{ __('docs.validation') }}</a>
        </nav>
        <div class="docs-content">
            <h2 id="overview">{{ __('docs.overview') }}</h2>
            <p>{{ __('docs.overview_body') }}</p>

            <h2 id="stack">{{ __('docs.stack') }}</h2>
            <ul>
                <li>Laravel 12 / PHP 8.2+</li>
                <li>Blade + XMLHttpRequest ({{ __('docs.no_vite') }})</li>
                <li><code>mcamara/laravel-localization</code></li>
                <li>Repository Pattern + Service Layer {{ __('docs.when_needed') }}</li>
            </ul>

            <h2 id="structure">{{ __('docs.structure') }}</h2>
            <p>{{ __('docs.structure_body') }}</p>

            <h2 id="design-system">{{ __('docs.design_system') }}</h2>
            <p>{!! __('docs.design_system_body') !!}</p>

            <h2 id="localization">{{ __('docs.localization') }}</h2>
            <p>{{ __('docs.localization_body') }}</p>

            <h2 id="validation">{{ __('docs.validation') }}</h2>
            <p>{{ __('docs.validation_body') }}</p>

            <div class="docs-note">{{ __('docs.living_doc_note') }}</div>
        </div>
    </div>
</div>
@endsection
