@extends('layouts.admin')

@section('page-title', __('navigation.users'))
@section('title', __('users.view'))

@section('content')
<div class="inner-body">
    <div class="view-user-card">
        <div class="view-user-header">
            <div class="view-user-avatar">{{ \Illuminate\Support\Str::of($user->name)->substr(0, 1) }}</div>
            <div class="view-user-heading">
                <h2>{{ $user->name }}</h2>
                <p>{{ $user->email }}</p>
            </div>
            <div class="view-user-actions">
                <button type="button" class="btn btn-danger" id="viewUserDeleteBtn"
                        data-id="{{ $user->id }}" data-name="{{ $user->name }}"
                        data-email="{{ $user->email }}" data-status="{{ $user->status }}">
                    <x-icon name="trash" style="width:15px;height:15px;" />
                    {{ __('users.delete') }}
                </button>
            </div>
        </div>

        <div class="view-user-grid">
            <div class="view-user-field">
                <span class="view-user-label">{{ __('users.id') }}</span>
                <span class="view-user-value">#{{ $user->id }}</span>
            </div>

            <div class="view-user-field">
                <span class="view-user-label">{{ __('users.column_status') }}</span>
                <span class="view-user-value">
                    @if($user->status === 'active')
                        <span class="badge active"><span class="badge-dot"></span>{{ __('users.active') }}</span>
                    @else
                        <span class="badge inactive"><span class="badge-dot"></span>{{ __('users.inactive') }}</span>
                    @endif
                </span>
            </div>

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

            <div class="view-user-field">
                <span class="view-user-label">{{ __('users.column_joined') }}</span>
                <span class="view-user-value">{{ optional($user->created_at)->format('Y-m-d') }}</span>
            </div>

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
    window.usersRoutes = {
        index: @json(route('admin.users.index')),
        destroy: @json(route('admin.users.destroy', ['user' => $user->id])),
    };
    window.usersLabels = {
        cancel: @json(__('common.cancel')),
        delete: @json(__('users.delete')),
        active: @json(__('users.active')),
        inactive: @json(__('users.inactive')),
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