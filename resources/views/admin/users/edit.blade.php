@extends('layouts.admin')

@section('page-title', __('navigation.users'))
@section('title', __('users.edit'))

@php
    // Safely resolve enums — old DB rows may have null/0 instead of a valid string
    $statusEnum = ($user->status instanceof \App\Enums\UserStatus)
        ? $user->status
        : \App\Enums\UserStatus::tryFrom((string) $user->status);

    $typeEnum = ($user->type instanceof \App\Enums\UserType)
        ? $user->type
        : \App\Enums\UserType::tryFrom((string) $user->type);

    $currentStatus = old('status', $statusEnum?->value ?? '');
    $currentType   = old('type',   $typeEnum?->value   ?? '');
@endphp

@section('content')
<div class="inner-body">
    <form method="POST" action="{{ route('admin.users.update', $user) }}" id="editUserForm" novalidate>
        @csrf
        @method('PUT')

        <div class="view-user-card">
            <div class="view-user-header">
                <div class="view-user-avatar">{{ \Illuminate\Support\Str::of($user->username ?? '?')->substr(0, 1)->upper() }}</div>
                <div class="view-user-heading">
                    <h2>{{ __('users.edit_title', ['name' => $user->username]) }}</h2>
                    <p>{{ __('users.edit_subtitle') }}</p>
                </div>
            </div>

            {{-- ─── Account fields ──────────────────────────────────────────── --}}
            <x-form.field name="username" :label="__('users.username')" :value="$user->username" required autofocus />
            <x-form.field name="email" type="email" :label="__('users.email')" :value="$user->email" required />
            <x-form.field name="mobile_number" :label="__('users.mobile_number')" :value="$user->mobile_number" required />

            {{-- ─── Status ────────────────────────────────────────────────────────── --}}
            <div class="field">
                <label for="status">{{ __('users.status') }}</label>
                <x-dropdown id="editStatusDropdown" variant="light" :select-style="true">
                    <x-slot:trigger>
                        <span class="select-value">{{ $statusEnum?->label() ?? $currentStatus }}</span>
                    </x-slot:trigger>
                    @foreach(\App\Enums\UserStatus::cases() as $status)
                        <x-dropdown-item
                            data-value="{{ $status->value }}"
                            :selected="$currentStatus === $status->value">
                            {{ $status->label() }}
                        </x-dropdown-item>
                    @endforeach
                </x-dropdown>
                <input type="hidden" name="status" id="status" value="{{ $currentStatus }}">
            </div>

            {{-- ─── Type ────────────────────────────────────────────────────────── --}}
            <div class="field">
                <label for="type">{{ __('users.type') }}</label>
                <x-dropdown id="editTypeDropdown" variant="light" :select-style="true">
                    <x-slot:trigger>
                        <span class="select-value">{{ $typeEnum?->label() ?? $currentType }}</span>
                    </x-slot:trigger>
                    @foreach(\App\Enums\UserType::cases() as $type)
                        <x-dropdown-item
                            data-value="{{ $type->value }}"
                            :selected="$currentType === $type->value">
                            {{ $type->label() }}
                        </x-dropdown-item>
                    @endforeach
                </x-dropdown>
                <input type="hidden" name="type" id="type" value="{{ $currentType }}">
            </div>

            {{-- ─── Optional fields ─────────────────────────────────────────── --}}
            <x-form.field name="national_id" :label="__('users.national_id')" :value="$user->national_id" />
            <x-form.field name="nationality" :label="__('users.nationality')" :value="$user->nationality" />
            <x-form.field name="passport_number" :label="__('users.passport_number')" :value="$user->passport_number" />

            {{-- ─── Password ────────────────────────────────────────────────── --}}
            <x-form.field name="password" type="password" :label="__('users.password_new')" toggle />
            <p class="edit-user-hint">{{ __('users.password_hint') }}</p>

            <div class="edit-user-actions">
                <button type="submit" class="btn btn-primary">
                    {{ __('users.save') }}
                </button>
                <a href="{{ route('admin.users.show', $user) }}" class="btn-ghost">{{ __('common.cancel') }}</a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .edit-user-hint{font-size:12px;color:var(--muted);margin:-12px 0 0;}
    .edit-user-actions{display:flex;gap:10px;margin-top:8px;}
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Status dropdown
        var statusDropdown = document.getElementById('editStatusDropdown');
        var statusInput = document.getElementById('status');
        if (statusDropdown && statusInput) {
            statusDropdown.addEventListener('select-change', function (e) {
                statusInput.value = e.detail.value;
            });
        }

        // Type dropdown
        var typeDropdown = document.getElementById('editTypeDropdown');
        var typeInput = document.getElementById('type');
        if (typeDropdown && typeInput) {
            typeDropdown.addEventListener('select-change', function (e) {
                typeInput.value = e.detail.value;
            });
        }

        // Password toggle
        document.querySelectorAll('.pw-toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.getAttribute('data-toggle-target'));
                if (!input) return;
                var isPw = input.type === 'password';
                input.type = isPw ? 'text' : 'password';
                btn.textContent = isPw ? btn.getAttribute('data-hide-label') : btn.getAttribute('data-show-label');
            });
        });
    });

    @if(session('toast_success'))
        document.addEventListener('DOMContentLoaded', function () {
            if (window.Toast) Toast.success(@json(session('toast_success')));
        });
    @endif
</script>
@endpush