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

            <x-form.field name="name" :label="__('users.name')" autofocus required />
            <x-form.field name="email" type="email" :label="__('users.email')" required />
            <x-form.field name="password" type="password" :label="__('users.password_label')" toggle required />

            <div class="field">
                <label for="status">{{ __('users.status') }}</label>
                <x-dropdown id="createStatusDropdown" variant="light" :select-style="true" class="open-up">
                    <x-slot:trigger>
                        <span class="select-value">{{ old('status', 'active') === 'active' ? __('users.active') : __('users.inactive') }}</span>
                    </x-slot:trigger>
                    <x-dropdown-item data-value="active" :selected="old('status', 'active') === 'active'">{{ __('users.active') }}</x-dropdown-item>
                    <x-dropdown-item data-value="inactive" :selected="old('status', 'active') === 'inactive'">{{ __('users.inactive') }}</x-dropdown-item>
                </x-dropdown>
                <input type="hidden" name="status" id="status" value="{{ old('status', 'active') }}">
            </div>

            <div class="edit-user-actions">
                <button type="submit" class="btn btn-primary">
                    {{-- <x-icon name="plus" style="width:15px;height:15px;" /> --}}
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
        var statusDropdown = document.getElementById('createStatusDropdown');
        var statusInput = document.getElementById('status');
        if (statusDropdown && statusInput) {
            statusDropdown.addEventListener('select-change', function (e) {
                statusInput.value = e.detail.value;
            });
        }

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