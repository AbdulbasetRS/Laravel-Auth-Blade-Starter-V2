@extends('layouts.admin')

@section('page-title', __('navigation.users'))
@section('title', __('users.view') . ' — ' . $user->username)

@php
    $statusEnum = ($user->status instanceof \App\Enums\UserStatus)
        ? $user->status
        : \App\Enums\UserStatus::tryFrom((string) $user->status);

    $typeEnum = ($user->type instanceof \App\Enums\UserType)
        ? $user->type
        : \App\Enums\UserType::tryFrom((string) $user->type);

    $profile = $user->profile;
    $displayName = $profile?->full_name ?: $user->username;
    $initial = \Illuminate\Support\Str::of($displayName)->substr(0, 1)->upper();
    $avatarUrl = ($profile?->avatar && ! str_starts_with($profile->avatar, 'http'))
        ? asset('storage/' . ltrim($profile->avatar, '/'))
        : ($profile?->avatar ?: null);

    $genderLabel = match ($profile?->gender) {
        'male' => __('users.gender_male'),
        'female' => __('users.gender_female'),
        default => null,
    };

    $empty = __('users.empty_value');
@endphp

@section('content')
<div class="inner-body ushow">

    {{-- Toolbar --}}
    <div class="ushow-toolbar">
        <a href="{{ route('admin.users.index') }}" class="ushow-back">
            <x-icon name="chevron-down" class="ushow-back-icon" />
            <span>{{ __('users.back_to_list') }}</span>
        </a>
        <div class="ushow-toolbar-actions">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm">
                <x-icon name="pencil" style="width:14px;height:14px;" />
                {{ __('users.edit') }}
            </a>
            <button type="button" class="btn btn-danger btn-sm" id="viewUserDeleteBtn"
                    data-id="{{ $user->id }}"
                    data-name="{{ $user->username }}"
                    data-email="{{ $user->email }}"
                    data-status="{{ $statusEnum?->value ?? $user->status }}">
                <x-icon name="trash" style="width:14px;height:14px;" />
                {{ __('users.delete') }}
            </button>
        </div>
    </div>

    {{-- Hero --}}
    <header class="ushow-hero">
        <div class="ushow-hero-bg" aria-hidden="true"></div>
        <div class="ushow-hero-body">
            <div class="ushow-avatar {{ $avatarUrl ? 'has-image' : '' }}">
                @if($avatarUrl)
                    <img src="{{ $avatarUrl }}" alt="{{ $displayName }}">
                @else
                    <span>{{ $initial }}</span>
                @endif
            </div>

            <div class="ushow-hero-meta">
                <p class="ushow-eyebrow">#{{ $user->id }} · {{ $user->slug }}</p>
                <h1 class="ushow-name">{{ $displayName }}</h1>
                <p class="ushow-username">{{ '@' . $user->username }}</p>

                <div class="ushow-badges">
                    @if($statusEnum)
                        <span class="badge {{ $statusEnum->color() }}">
                            <span class="badge-dot"></span>{{ $statusEnum->label() }}
                        </span>
                    @else
                        <span class="badge secondary">{{ $user->status ?? $empty }}</span>
                    @endif

                    @if($typeEnum)
                        <span class="badge {{ $typeEnum->color() }}">{{ $typeEnum->label() }}</span>
                    @else
                        <span class="badge secondary">{{ $user->type ?? $empty }}</span>
                    @endif

                    @if($user->email_verified_at)
                        <span class="ushow-chip ok">
                            <x-icon name="check" style="width:13px;height:13px;" />
                            {{ __('users.verified') }}
                        </span>
                    @else
                        <span class="ushow-chip muted">
                            <x-icon name="x" style="width:13px;height:13px;" />
                            {{ __('users.unverified') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="ushow-stats">
            <div class="ushow-stat">
                <span class="ushow-stat-label">{{ __('users.credits') }}</span>
                <span class="ushow-stat-value">{{ number_format((int) $user->credits) }}</span>
            </div>
            <div class="ushow-stat">
                <span class="ushow-stat-label">{{ __('users.can_login') }}</span>
                <span class="ushow-stat-value {{ $user->can_login ? 'ok' : 'no' }}">
                    {{ $user->can_login ? __('users.yes') : __('users.no') }}
                </span>
            </div>
            <div class="ushow-stat">
                <span class="ushow-stat-label">{{ __('users.column_joined') }}</span>
                <span class="ushow-stat-value">{{ optional($user->created_at)->format('Y-m-d') ?: $empty }}</span>
            </div>
            <div class="ushow-stat">
                <span class="ushow-stat-label">{{ __('users.updated_at') }}</span>
                <span class="ushow-stat-value">{{ optional($user->updated_at)->format('Y-m-d') ?: $empty }}</span>
            </div>
        </div>
    </header>

    {{-- Sections --}}
    <div class="ushow-layout">

        <section class="ushow-panel" style="--i:0">
            <div class="ushow-panel-head">
                <x-icon name="user" class="ushow-panel-icon" />
                <h2>{{ __('users.section_personal') }}</h2>
            </div>
            @if($profile)
                <div class="ushow-grid">
                    <div class="ushow-field">
                        <span class="ushow-label">{{ __('users.title_field') }}</span>
                        <span class="ushow-value {{ $profile->title ? '' : 'empty' }}">{{ $profile->title ?: $empty }}</span>
                    </div>
                    <div class="ushow-field">
                        <span class="ushow-label">{{ __('users.gender') }}</span>
                        <span class="ushow-value {{ $genderLabel ? '' : 'empty' }}">{{ $genderLabel ?: $empty }}</span>
                    </div>
                    <div class="ushow-field">
                        <span class="ushow-label">{{ __('users.first_name') }}</span>
                        <span class="ushow-value {{ $profile->first_name ? '' : 'empty' }}">{{ $profile->first_name ?: $empty }}</span>
                    </div>
                    <div class="ushow-field">
                        <span class="ushow-label">{{ __('users.last_name') }}</span>
                        <span class="ushow-value {{ $profile->last_name ? '' : 'empty' }}">{{ $profile->last_name ?: $empty }}</span>
                    </div>
                    <div class="ushow-field">
                        <span class="ushow-label">{{ __('users.middle_name') }}</span>
                        <span class="ushow-value {{ $profile->middle_name ? '' : 'empty' }}">{{ $profile->middle_name ?: $empty }}</span>
                    </div>
                    <div class="ushow-field">
                        <span class="ushow-label">{{ __('users.date_of_birth') }}</span>
                        <span class="ushow-value {{ $profile->date_of_birth ? '' : 'empty' }}">
                            {{ optional($profile->date_of_birth)->format('Y-m-d') ?: $empty }}
                        </span>
                    </div>
                </div>
            @else
                <p class="ushow-empty-note">{{ __('users.no_profile') }}</p>
            @endif
        </section>

        <section class="ushow-panel" style="--i:1">
            <div class="ushow-panel-head">
                <x-icon name="globe" class="ushow-panel-icon" />
                <h2>{{ __('users.section_contact') }}</h2>
            </div>
            <div class="ushow-grid">
                <div class="ushow-field ushow-field-wide">
                    <span class="ushow-label">{{ __('users.email') }}</span>
                    <span class="ushow-value">
                        <a class="ushow-link" href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                    </span>
                </div>
                <div class="ushow-field">
                    <span class="ushow-label">{{ __('users.mobile_number') }}</span>
                    <span class="ushow-value {{ $user->mobile_number ? '' : 'empty' }}">{{ $user->mobile_number ?: $empty }}</span>
                </div>
                <div class="ushow-field">
                    <span class="ushow-label">{{ __('users.whatsapp') }}</span>
                    <span class="ushow-value {{ $profile?->whatsapp ? '' : 'empty' }}">{{ $profile?->whatsapp ?: $empty }}</span>
                </div>
                <div class="ushow-field">
                    <span class="ushow-label">{{ __('users.telegram') }}</span>
                    <span class="ushow-value {{ $profile?->telegram ? '' : 'empty' }}">{{ $profile?->telegram ?: $empty }}</span>
                </div>
                <div class="ushow-field">
                    <span class="ushow-label">{{ __('users.nationality') }}</span>
                    <span class="ushow-value {{ $user->nationality ? '' : 'empty' }}">{{ $user->nationality ?: $empty }}</span>
                </div>
                <div class="ushow-field">
                    <span class="ushow-label">{{ __('users.national_id') }}</span>
                    <span class="ushow-value {{ $user->national_id ? '' : 'empty' }}">{{ $user->national_id ?: $empty }}</span>
                </div>
                <div class="ushow-field">
                    <span class="ushow-label">{{ __('users.passport_number') }}</span>
                    <span class="ushow-value {{ $user->passport_number ? '' : 'empty' }}">{{ $user->passport_number ?: $empty }}</span>
                </div>
                <div class="ushow-field ushow-field-wide">
                    <span class="ushow-label">{{ __('users.address') }}</span>
                    <span class="ushow-value {{ $profile?->address ? '' : 'empty' }}">{{ $profile?->address ?: $empty }}</span>
                </div>
            </div>
        </section>

        <section class="ushow-panel" style="--i:2">
            <div class="ushow-panel-head">
                <x-icon name="shield" class="ushow-panel-icon" />
                <h2>{{ __('users.section_account') }}</h2>
            </div>
            <div class="ushow-grid">
                <div class="ushow-field">
                    <span class="ushow-label">{{ __('users.username') }}</span>
                    <span class="ushow-value">{{ $user->username }}</span>
                </div>
                <div class="ushow-field">
                    <span class="ushow-label">{{ __('users.role_id') }}</span>
                    <span class="ushow-value {{ $user->role_id ? '' : 'empty' }}">{{ $user->role_id ?: $empty }}</span>
                </div>
                <div class="ushow-field">
                    <span class="ushow-label">{{ __('users.column_type') }}</span>
                    <span class="ushow-value">
                        @if($typeEnum)
                            <span class="badge {{ $typeEnum->color() }}">{{ $typeEnum->label() }}</span>
                        @else
                            {{ $user->type ?? $empty }}
                        @endif
                    </span>
                </div>
                <div class="ushow-field">
                    <span class="ushow-label">{{ __('users.column_status') }}</span>
                    <span class="ushow-value">
                        @if($statusEnum)
                            <span class="badge {{ $statusEnum->color() }}">
                                <span class="badge-dot"></span>{{ $statusEnum->label() }}
                            </span>
                        @else
                            {{ $user->status ?? $empty }}
                        @endif
                    </span>
                </div>
                <div class="ushow-field">
                    <span class="ushow-label">{{ __('users.created_by') }}</span>
                    <span class="ushow-value {{ $user->createdBy ? '' : 'empty' }}">
                        {{ $user->createdBy?->username ?: $empty }}
                    </span>
                </div>
                <div class="ushow-field">
                    <span class="ushow-label">{{ __('users.updated_by') }}</span>
                    <span class="ushow-value {{ $user->updatedBy ? '' : 'empty' }}">
                        {{ $user->updatedBy?->username ?: $empty }}
                    </span>
                </div>
                @if($user->status_details)
                    <div class="ushow-field ushow-field-wide">
                        <span class="ushow-label">{{ __('users.status_details') }}</span>
                        <span class="ushow-value">{{ $user->status_details }}</span>
                    </div>
                @endif
            </div>
        </section>

        @if($profile?->note)
            <section class="ushow-panel" style="--i:3">
                <div class="ushow-panel-head">
                    <x-icon name="file-text" class="ushow-panel-icon" />
                    <h2>{{ __('users.section_notes') }}</h2>
                </div>
                <p class="ushow-note-body">{{ $profile->note }}</p>
            </section>
        @endif

    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/user-show.css') }}">
@endpush

@push('scripts')
<script>
    @if(session('toast_success'))
        document.addEventListener('DOMContentLoaded', function () {
            if (window.Toast) Toast.success(@json(session('toast_success')));
        });
    @endif
</script>
<script>
    window.usersRoutes = {
        index: @json(route('admin.users.index', absolute: false)),
        destroy: @json(route('admin.users.destroy', ['user' => $user->id], absolute: false)),
    };
    window.usersLabels = {
        cancel: @json(__('common.cancel')),
        delete: @json(__('users.delete')),
        idLabel: @json(__('users.id')),
        columnStatus: @json(__('users.column_status')),
        active: @json(__('users.active')),
        inactive: @json(__('users.inactive')),
        confirmDeleteTitle: @json(__('users.confirm_delete_title')),
        confirmDeleteMessage: @json(__('users.confirm_delete_message')),
        deleteSuccess: @json(__('users.delete_success')),
        deleteError: @json(__('users.delete_error')),
    };
</script>
<script src="{{ asset('assets/js/user-show.js') }}"></script>
@endpush
