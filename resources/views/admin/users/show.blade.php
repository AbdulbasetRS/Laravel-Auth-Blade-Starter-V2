@extends('layouts.admin')

@section('page-title', __('navigation.users'))
@section('title', __('users.view'))

@php
    $statusEnum = ($user->status instanceof \App\Enums\UserStatus)
        ? $user->status
        : \App\Enums\UserStatus::tryFrom((string) $user->status);

    $typeEnum = ($user->type instanceof \App\Enums\UserType)
        ? $user->type
        : \App\Enums\UserType::tryFrom((string) $user->type);
@endphp

@section('content')
<div class="inner-body">
    <div class="view-user-card">
        <div class="view-user-header">
            <div class="view-user-avatar">{{ \Illuminate\Support\Str::of($user->username)->substr(0, 1)->upper() }}</div>
            <div class="view-user-heading">
                <h2>{{ $user->username }}</h2>
                <p>{{ $user->email }}</p>
            </div>
            <div class="view-user-actions">
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                    <x-icon name="pencil" style="width:15px;height:15px;" />
                    {{ __('users.edit') }}
                </a>
                <button type="button" class="btn btn-danger" id="viewUserDeleteBtn"
                        data-id="{{ $user->id }}"
                        data-name="{{ $user->username }}"
                        data-email="{{ $user->email }}"
                        data-status="{{ $user->status instanceof \App\Enums\UserStatus ? $user->status->value : $user->status }}">
                    <x-icon name="trash" style="width:15px;height:15px;" />
                    {{ __('users.delete') }}
                </button>
            </div>
        </div>

        <div class="view-user-grid">
            {{-- ID --}}
            <div class="view-user-field">
                <span class="view-user-label">{{ __('users.id') }}</span>
                <span class="view-user-value">#{{ $user->id }}</span>
            </div>

            {{-- Status --}}
            <div class="view-user-field">
                <span class="view-user-label">{{ __('users.column_status') }}</span>
                <span class="view-user-value">
                    @if($statusEnum)
                        <span class="badge {{ $statusEnum->color() }}">
                            <span class="badge-dot"></span>{{ $statusEnum->label() }}
                        </span>
                    @else
                        <span class="badge secondary">{{ $user->status ?? '—' }}</span>
                    @endif
                </span>
            </div>

            {{-- Type --}}
            <div class="view-user-field">
                <span class="view-user-label">{{ __('users.column_type') }}</span>
                <span class="view-user-value">
                    @if($typeEnum)
                        <span class="badge {{ $typeEnum->color() }}">{{ $typeEnum->label() }}</span>
                    @else
                        <span class="badge secondary">{{ $user->type ?? '—' }}</span>
                    @endif
                </span>
            </div>

            {{-- Mobile --}}
            <div class="view-user-field">
                <span class="view-user-label">{{ __('users.mobile_number') }}</span>
                <span class="view-user-value">{{ $user->mobile_number ?? '—' }}</span>
            </div>

            {{-- National ID --}}
            @if($user->national_id)
            <div class="view-user-field">
                <span class="view-user-label">{{ __('users.national_id') }}</span>
                <span class="view-user-value">{{ $user->national_id }}</span>
            </div>
            @endif

            {{-- Nationality --}}
            @if($user->nationality)
            <div class="view-user-field">
                <span class="view-user-label">{{ __('users.nationality') }}</span>
                <span class="view-user-value">{{ $user->nationality }}</span>
            </div>
            @endif

            {{-- Credits --}}
            <div class="view-user-field">
                <span class="view-user-label">{{ __('users.credits') }}</span>
                <span class="view-user-value">{{ number_format($user->credits) }}</span>
            </div>

            {{-- Can Login --}}
            <div class="view-user-field">
                <span class="view-user-label">{{ __('users.can_login') }}</span>
                <span class="view-user-value">
                    @if($user->can_login)
                        <span class="verified-yes"><x-icon name="check" style="width:14px;height:14px;" />{{ __('users.yes') }}</span>
                    @else
                        <span class="verified-no"><x-icon name="x" style="width:14px;height:14px;" />{{ __('users.no') }}</span>
                    @endif
                </span>
            </div>

            {{-- Verified --}}
            <div class="view-user-field">
                <span class="view-user-label">{{ __('users.column_verified') }}</span>
                <span class="view-user-value">
                    @if($user->email_verified_at)
                        <span class="verified-yes"><x-icon name="check" style="width:14px;height:14px;" />{{ __('users.yes') }}</span>
                    @else
                        <span class="verified-no"><x-icon name="x" style="width:14px;height:14px;" />{{ __('users.no') }}</span>
                    @endif
                </span>
            </div>

            {{-- Status Details --}}
            @if($user->status_details)
            <div class="view-user-field" style="grid-column: 1 / -1;">
                <span class="view-user-label">{{ __('users.status_details') }}</span>
                <span class="view-user-value">{{ $user->status_details }}</span>
            </div>
            @endif

            {{-- Joined --}}
            <div class="view-user-field">
                <span class="view-user-label">{{ __('users.column_joined') }}</span>
                <span class="view-user-value">{{ optional($user->created_at)->format('Y-m-d') }}</span>
            </div>

            {{-- Updated At --}}
            <div class="view-user-field">
                <span class="view-user-label">{{ __('users.updated_at') }}</span>
                <span class="view-user-value">{{ optional($user->updated_at)->format('Y-m-d') }}</span>
            </div>
        </div>

        <a href="{{ route('admin.users.index') }}" class="btn-ghost view-user-back">
            {{ __('users.back_to_list') }}
        </a>
    </div>
</div>
@endsection

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
        index: @json(route('admin.users.index')),
        destroy: @json(route('admin.users.destroy', ['user' => $user->id])),
    };
    window.usersLabels = {
        cancel: @json(__('common.cancel')),
        delete: @json(__('users.delete')),
        idLabel: @json(__('users.id')),
        columnStatus: @json(__('users.column_status')),
        confirmDeleteTitle: @json(__('users.confirm_delete_title')),
        confirmDeleteMessage: @json(__('users.confirm_delete_message')),
        deleteSuccess: @json(__('users.delete_success')),
        deleteError: @json(__('users.delete_error')),
    };
</script>
<script src="{{ asset('assets/js/user-show.js') }}"></script>
@endpush