@extends('layouts.admin')

@section('page-title', 'System Design')
@section('title', 'System Design')

@section('content')
<div class="inner-body">

    <div class="sd-section">
        <h2>{{ __('sysdesign.colors') }}</h2>
        <div class="sd-swatches">
            <div class="sd-swatch"><div class="sd-swatch-color" style="background:var(--primary);"></div><div class="sd-swatch-label">--primary</div></div>
            <div class="sd-swatch"><div class="sd-swatch-color" style="background:var(--ink);"></div><div class="sd-swatch-label">--ink</div></div>
            <div class="sd-swatch"><div class="sd-swatch-color" style="background:var(--error);"></div><div class="sd-swatch-label">--error</div></div>
            <div class="sd-swatch"><div class="sd-swatch-color" style="background:var(--success);"></div><div class="sd-swatch-label">--success</div></div>
            <div class="sd-swatch"><div class="sd-swatch-color" style="background:var(--muted);"></div><div class="sd-swatch-label">--muted</div></div>
            <div class="sd-swatch"><div class="sd-swatch-color" style="background:var(--surface-alt);border-bottom:1px solid var(--line);"></div><div class="sd-swatch-label">--surface-alt</div></div>
        </div>
    </div>

    <div class="sd-section">
        <h2>{{ __('sysdesign.typography') }}</h2>
        <div class="sd-type-row"><span class="tag-label">Display / AR</span><span style="font-family:'Tajawal';font-weight:800;font-size:22px;">عنوان رئيسي — Tajawal 800</span></div>
        <div class="sd-type-row"><span class="tag-label">Body / AR</span><span style="font-family:'Cairo';font-size:14px;">نص أساسي — Cairo 400/600</span></div>
        <div class="sd-type-row"><span class="tag-label">Display / EN</span><span style="font-family:'Manrope';font-weight:800;font-size:22px;">Primary Heading — Manrope 800</span></div>
        <div class="sd-type-row"><span class="tag-label">Body / EN</span><span style="font-family:'Manrope';font-size:14px;">Body text — Manrope 500/600</span></div>
        <div class="sd-type-row"><span class="tag-label">Mono</span><span style="font-family:'IBM Plex Mono';font-size:13px;">10:42:35 • Africa/Cairo</span></div>
    </div>

    <div class="sd-section">
        <h2>{{ __('sysdesign.buttons') }}</h2>
        <div class="sd-row">
            <button class="btn btn-primary">Primary</button>
            <button class="btn-ghost">Ghost</button>
            <button class="btn btn-primary" disabled>Disabled</button>
        </div>
    </div>

    <div class="sd-section">
        <h2>Badges</h2>
        <div class="sd-row">
            <span class="badge active"><span class="badge-dot"></span>Active</span>
            <span class="badge inactive"><span class="badge-dot"></span>Inactive</span>
        </div>
    </div>

    <div class="sd-section">
        <h2>{{ __('sysdesign.global_dropdown') }}</h2>
        <div class="sd-row">
            <x-dropdown variant="light" :select-style="true">
                <x-slot:trigger><span class="select-value">Sort by</span></x-slot:trigger>
                <x-dropdown-item selected data-value="newest">Newest</x-dropdown-item>
                <x-dropdown-item data-value="oldest">Oldest</x-dropdown-item>
            </x-dropdown>
        </div>
    </div>

</div>
@endsection
