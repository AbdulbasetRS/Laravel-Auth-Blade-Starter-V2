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
    $editUrl = route('admin.users.edit', $user);
@endphp

@section('content')
<div class="inner-body wizard-shell ushow">

    {{-- Toolbar --}}
    <div class="ushow-toolbar">
        <a href="{{ route('admin.users.index') }}" class="ushow-back">
            <x-icon name="chevron-down" class="ushow-back-icon" />
            <span>{{ __('users.back_to_list') }}</span>
        </a>
        <div class="ushow-toolbar-actions">
            <a href="{{ $editUrl }}" class="btn btn-primary btn-sm">
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

    {{-- Identity header (wizard-progress card) --}}
    <div class="wizard-progress ushow-identity">
        <div class="ushow-identity-row">
            <div class="ushow-avatar {{ $avatarUrl ? 'has-image' : '' }}">
                @if($avatarUrl)
                    <img src="{{ $avatarUrl }}" alt="{{ $displayName }}">
                @else
                    <span>{{ $initial }}</span>
                @endif
            </div>

            <div class="ushow-identity-meta">
                <p class="ushow-eyebrow">#{{ $user->id }} · {{ $user->slug }}</p>
                <h2 class="wizard-progress-title ushow-name">{{ $displayName }}</h2>
                <p class="ushow-username">{{ '@' . $user->username }}</p>
                <p class="wizard-progress-subtitle ushow-subtitle">{{ __('users.show_subtitle') }}</p>

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
    </div>

    {{-- Detail body (wizard-card + review sections) --}}
    <div class="wizard-card ushow-card">
        <div class="wizard-card-inner">

            {{-- Personal --}}
            <div class="review-section">
                <div class="review-section-header">
                    <span class="review-section-title">{{ __('users.section_personal') }}</span>
                </div>
                @if($profile)
                    <div class="review-grid">
                        <div class="review-field">
                            <span class="review-label">{{ __('users.title_field') }}</span>
                            <span class="review-value {{ $profile->title ? '' : 'empty' }}">{{ $profile->title ?: $empty }}</span>
                        </div>
                        <div class="review-field">
                            <span class="review-label">{{ __('users.gender') }}</span>
                            <span class="review-value {{ $genderLabel ? '' : 'empty' }}">{{ $genderLabel ?: $empty }}</span>
                        </div>
                        <div class="review-field">
                            <span class="review-label">{{ __('users.first_name') }}</span>
                            <span class="review-value {{ $profile->first_name ? '' : 'empty' }}">{{ $profile->first_name ?: $empty }}</span>
                        </div>
                        <div class="review-field">
                            <span class="review-label">{{ __('users.middle_name') }}</span>
                            <span class="review-value {{ $profile->middle_name ? '' : 'empty' }}">{{ $profile->middle_name ?: $empty }}</span>
                        </div>
                        <div class="review-field">
                            <span class="review-label">{{ __('users.last_name') }}</span>
                            <span class="review-value {{ $profile->last_name ? '' : 'empty' }}">{{ $profile->last_name ?: $empty }}</span>
                        </div>
                        <div class="review-field">
                            <span class="review-label">{{ __('users.date_of_birth') }}</span>
                            <span class="review-value {{ $profile->date_of_birth ? '' : 'empty' }}">
                                {{ optional($profile->date_of_birth)->format('Y-m-d') ?: $empty }}
                            </span>
                        </div>
                        <div class="review-field">
                            <span class="review-label">{{ __('users.avatar') }}</span>
                            @if($avatarUrl)
                                <img class="review-avatar-thumb" src="{{ $avatarUrl }}" alt="{{ $displayName }}">
                            @else
                                <span class="review-value empty">{{ $empty }}</span>
                            @endif
                        </div>
                    </div>
                @else
                    <p class="ushow-empty-note">{{ __('users.no_profile') }}</p>
                @endif
            </div>

            {{-- Contact & Identity --}}
            <div class="review-section">
                <div class="review-section-header">
                    <span class="review-section-title">{{ __('users.section_contact') }}</span>
                </div>
                <div class="review-grid">
                    <div class="review-field ushow-field-wide">
                        <span class="review-label">{{ __('users.email') }}</span>
                        <span class="review-value">
                            <a class="ushow-link" href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                        </span>
                    </div>
                    <div class="review-field">
                        <span class="review-label">{{ __('users.mobile_number') }}</span>
                        <span class="review-value {{ $user->mobile_number ? '' : 'empty' }}">{{ $user->mobile_number ?: $empty }}</span>
                    </div>
                    <div class="review-field">
                        <span class="review-label">{{ __('users.whatsapp') }}</span>
                        <span class="review-value {{ $profile?->whatsapp ? '' : 'empty' }}">{{ $profile?->whatsapp ?: $empty }}</span>
                    </div>
                    <div class="review-field">
                        <span class="review-label">{{ __('users.telegram') }}</span>
                        <span class="review-value {{ $profile?->telegram ? '' : 'empty' }}">{{ $profile?->telegram ?: $empty }}</span>
                    </div>
                    <div class="review-field">
                        <span class="review-label">{{ __('users.nationality') }}</span>
                        <span class="review-value {{ $user->nationality ? '' : 'empty' }}">{{ $user->nationality ?: $empty }}</span>
                    </div>
                    <div class="review-field">
                        <span class="review-label">{{ __('users.national_id') }}</span>
                        <span class="review-value {{ $user->national_id ? '' : 'empty' }}">{{ $user->national_id ?: $empty }}</span>
                    </div>
                    <div class="review-field">
                        <span class="review-label">{{ __('users.passport_number') }}</span>
                        <span class="review-value {{ $user->passport_number ? '' : 'empty' }}">{{ $user->passport_number ?: $empty }}</span>
                    </div>
                    <div class="review-field ushow-field-wide">
                        <span class="review-label">{{ __('users.address') }}</span>
                        <span class="review-value {{ $profile?->address ? '' : 'empty' }}">{{ $profile?->address ?: $empty }}</span>
                    </div>
                </div>
            </div>

            {{-- Account & Access --}}
            <div class="review-section">
                <div class="review-section-header">
                    <span class="review-section-title">{{ __('users.section_account') }}</span>
                </div>
                <div class="review-grid">
                    <div class="review-field">
                        <span class="review-label">{{ __('users.username') }}</span>
                        <span class="review-value">{{ $user->username }}</span>
                    </div>
                    <div class="review-field">
                        <span class="review-label">{{ __('users.role_id') }}</span>
                        <span class="review-value {{ $user->role_id ? '' : 'empty' }}">{{ $user->role_id ?: $empty }}</span>
                    </div>
                    <div class="review-field">
                        <span class="review-label">{{ __('users.column_type') }}</span>
                        <span class="review-value">
                            @if($typeEnum)
                                <span class="badge {{ $typeEnum->color() }}">{{ $typeEnum->label() }}</span>
                            @else
                                {{ $user->type ?? $empty }}
                            @endif
                        </span>
                    </div>
                    <div class="review-field">
                        <span class="review-label">{{ __('users.column_status') }}</span>
                        <span class="review-value">
                            @if($statusEnum)
                                <span class="badge {{ $statusEnum->color() }}">
                                    <span class="badge-dot"></span>{{ $statusEnum->label() }}
                                </span>
                            @else
                                {{ $user->status ?? $empty }}
                            @endif
                        </span>
                    </div>
                    <div class="review-field">
                        <span class="review-label">{{ __('users.credits') }}</span>
                        <span class="review-value">{{ number_format((int) $user->credits) }}</span>
                    </div>
                    <div class="review-field">
                        <span class="review-label">{{ __('users.can_login') }}</span>
                        <span class="review-value {{ $user->can_login ? 'ok' : '' }}">
                            {{ $user->can_login ? __('users.yes') : __('users.no') }}
                        </span>
                    </div>
                    <div class="review-field">
                        <span class="review-label">{{ __('users.column_joined') }}</span>
                        <span class="review-value {{ $user->created_at ? '' : 'empty' }}">
                            {{ optional($user->created_at)->format('Y-m-d') ?: $empty }}
                        </span>
                    </div>
                    <div class="review-field">
                        <span class="review-label">{{ __('users.updated_at') }}</span>
                        <span class="review-value {{ $user->updated_at ? '' : 'empty' }}">
                            {{ optional($user->updated_at)->format('Y-m-d') ?: $empty }}
                        </span>
                    </div>
                    <div class="review-field">
                        <span class="review-label">{{ __('users.created_by') }}</span>
                        <span class="review-value {{ $user->createdBy ? '' : 'empty' }}">
                            {{ $user->createdBy?->username ?: $empty }}
                        </span>
                    </div>
                    <div class="review-field">
                        <span class="review-label">{{ __('users.updated_by') }}</span>
                        <span class="review-value {{ $user->updatedBy ? '' : 'empty' }}">
                            {{ $user->updatedBy?->username ?: $empty }}
                        </span>
                    </div>
                    @if($user->status_details)
                        <div class="review-field ushow-field-wide">
                            <span class="review-label">{{ __('users.status_details') }}</span>
                            <span class="review-value">{{ $user->status_details }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Notes --}}
            @if($profile?->note)
                <div class="review-section">
                    <div class="review-section-header">
                        <span class="review-section-title">{{ __('users.section_notes') }}</span>
                        <a href="{{ $editUrl }}" class="review-section-edit">{{ __('users.edit') }}</a>
                    </div>
                    <div class="ushow-note-body">{{ $profile->note }}</div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/user-wizard.css') }}">
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
