@extends('layouts.admin')

@section('page-title', __('navigation.create_user'))
@section('title', __('navigation.create_user'))

@section('content')
<div class="inner-body">
    <form method="POST" action="{{ route('admin.users.store') }}" id="createUserForm" novalidate>
        @csrf

        <div class="view-user-card">
            <div class="view-user-header">
                <div class="view-user-heading">
                    <h2>{{ __('users.create_title') }}</h2>
                    <p>{{ __('users.create_subtitle') }}</p>
                </div>
            </div>

            {{-- ─── Account fields ──────────────────────────────────────────── --}}
            <x-form.field name="username" :label="__('users.username')" autofocus required />
            <x-form.field name="email" type="email" :label="__('users.email')" required />
            <x-form.field name="mobile_number" :label="__('users.mobile_number')" required />
            <x-form.field name="password" type="password" :label="__('users.password_label')" toggle required />

            {{-- ─── Status ──────────────────────────────────────────────────── --}}
            <div class="field">
                <label for="status">{{ __('users.status') }}</label>
                <x-dropdown id="createStatusDropdown" variant="light" :select-style="true">
                    <x-slot:trigger>
                        <span class="select-value">{{ \App\Enums\UserStatus::PENDING->label() }}</span>
                    </x-slot:trigger>
                    @foreach(\App\Enums\UserStatus::cases() as $status)
                        <x-dropdown-item
                            data-value="{{ $status->value }}"
                            :selected="old('status', \App\Enums\UserStatus::PENDING->value) === $status->value">
                            {{ $status->label() }}
                        </x-dropdown-item>
                    @endforeach
                </x-dropdown>
                <input type="hidden" name="status" id="status" value="{{ old('status', \App\Enums\UserStatus::PENDING->value) }}">
            </div>

            {{-- ─── Type ────────────────────────────────────────────────────── --}}
            <div class="field">
                <label for="type">{{ __('users.type') }}</label>
                <x-dropdown id="createTypeDropdown" variant="light" :select-style="true">
                    <x-slot:trigger>
                        <span class="select-value">{{ \App\Enums\UserType::USER->label() }}</span>
                    </x-slot:trigger>
                    @foreach(\App\Enums\UserType::cases() as $type)
                        <x-dropdown-item
                            data-value="{{ $type->value }}"
                            :selected="old('type', \App\Enums\UserType::USER->value) === $type->value">
                            {{ $type->label() }}
                        </x-dropdown-item>
                    @endforeach
                </x-dropdown>
                <input type="hidden" name="type" id="type" value="{{ old('type', \App\Enums\UserType::USER->value) }}">
            </div>

            {{-- ─── Optional fields ─────────────────────────────────────────── --}}
            <x-form.field name="national_id" :label="__('users.national_id')" />
            <x-form.field name="nationality" :label="__('users.nationality')" />
            <x-form.field name="passport_number" :label="__('users.passport_number')" />

            <div class="edit-user-actions">
                <button type="submit" class="btn btn-primary">
                    {{ __('users.create_user') }}
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn-ghost">{{ __('common.cancel') }}</a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .edit-user-actions{display:flex;gap:10px;margin-top:8px;}
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Status dropdown
        var statusDropdown = document.getElementById('createStatusDropdown');
        var statusInput = document.getElementById('status');
        if (statusDropdown && statusInput) {
            statusDropdown.addEventListener('select-change', function (e) {
                statusInput.value = e.detail.value;
            });
        }

        // Type dropdown
        var typeDropdown = document.getElementById('createTypeDropdown');
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
</script>
@endpush