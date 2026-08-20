@extends('layouts.admin')

@section('page-title', __('navigation.users'))
@section('title', __('users.edit'))

@section('content')
<div class="inner-body">
    <form method="POST" action="{{ route('admin.users.update', $user) }}" id="editUserForm" novalidate>
        @csrf
        @method('PUT')

        <div class="view-user-card">
            <div class="view-user-header">
                <div class="view-user-avatar">{{ \Illuminate\Support\Str::of($user->name)->substr(0, 1) }}</div>
                <div class="view-user-heading">
                    <h2>{{ __('users.edit_title', ['name' => $user->name]) }}</h2>
                    <p>{{ __('users.edit_subtitle') }}</p>
                </div>
            </div>

            <x-form.field name="name" :label="__('users.name')" :value="$user->name" required autofocus />
            <x-form.field name="email" type="email" :label="__('users.email')" :value="$user->email" required />

            <div class="field">
                <label for="status">{{ __('users.status') }}</label>
                <x-dropdown id="editStatusDropdown" variant="light" :select-style="true" class="open-up">
                    <x-slot:trigger>
                        <span class="select-value">{{ old('status', $user->status) === 'active' ? __('users.active') : __('users.inactive') }}</span>
                    </x-slot:trigger>
                    <x-dropdown-item data-value="active" :selected="old('status', $user->status) === 'active'">{{ __('users.active') }}</x-dropdown-item>
                    <x-dropdown-item data-value="inactive" :selected="old('status', $user->status) === 'inactive'">{{ __('users.inactive') }}</x-dropdown-item>
                </x-dropdown>
                <input type="hidden" name="status" id="status" value="{{ old('status', $user->status) }}">
            </div>

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
        var statusDropdown = document.getElementById('editStatusDropdown');
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

    @if(session('toast_success'))
        document.addEventListener('DOMContentLoaded', function () {
            if (window.Toast) Toast.success(@json(session('toast_success')));
        });
    @endif
</script>
@endpush