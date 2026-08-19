@extends('layouts.admin')

@section('page-title', __('navigation.users'))
@section('title', __('navigation.users'))

@section('content')
<div class="inner-body">

    <div class="filters-bar">
        <div class="filters-top-row">
            <div class="search-input">
                <x-icon name="search" />
                <input type="text" placeholder="{{ __('users.search_placeholder') }}">
            </div>

            <button type="button" class="gdropdown-trigger light filters-toggle-btn" id="filtersToggleBtn" aria-expanded="false" aria-controls="filtersAccordion">
                <x-icon name="columns" style="width:15px;height:15px;" />
                <span>{{ __('users.filters') }}</span>
                <span class="filter-badge-count">2</span>
                <x-icon name="chevron-down" class="chev" />
            </button>
        </div>

        <div class="filters-accordion" id="filtersAccordion">
            <div class="filters-grid">

                <div class="filter-field">
                    <label>{{ __('users.status') }}</label>
                    <x-dropdown variant="light" :select-style="true" data-filter="status">
                        <x-slot:trigger><span class="select-value">{{ __('users.all_statuses') }}</span></x-slot:trigger>
                        <x-dropdown-item selected data-value="all">{{ __('users.all_statuses') }}</x-dropdown-item>
                        <x-dropdown-item data-value="active">{{ __('users.active') }}</x-dropdown-item>
                        <x-dropdown-item data-value="inactive">{{ __('users.inactive') }}</x-dropdown-item>
                    </x-dropdown>
                </div>

                <div class="filter-field">
                    <label>{{ __('users.verified_status') }}</label>
                    <x-dropdown variant="light" :select-style="true" data-filter="verified">
                        <x-slot:trigger><span class="select-value">{{ __('users.all') }}</span></x-slot:trigger>
                        <x-dropdown-item selected data-value="">{{ __('users.all') }}</x-dropdown-item>
                        <x-dropdown-item data-value="yes">{{ __('users.verified') }}</x-dropdown-item>
                        <x-dropdown-item data-value="no">{{ __('users.unverified') }}</x-dropdown-item>
                    </x-dropdown>
                </div>

                <div class="filter-field">
                    <label>{{ __('users.date_from') }}</label>
                    @include('admin.users._date-field', ['filterKey' => 'date_from'])
                </div>

                <div class="filter-field">
                    <label>{{ __('users.date_to') }}</label>
                    @include('admin.users._date-field', ['filterKey' => 'date_to'])
                </div>

                <div class="filter-field">
                    <label>{{ __('users.sort') }}</label>
                    <x-dropdown variant="light" :select-style="true" data-filter="sort">
                        <x-slot:trigger><span class="select-value">{{ __('users.sort_newest') }}</span></x-slot:trigger>
                        <x-dropdown-item selected data-value="newest">{{ __('users.sort_newest') }}</x-dropdown-item>
                        <x-dropdown-item data-value="oldest">{{ __('users.sort_oldest') }}</x-dropdown-item>
                        <x-dropdown-item data-value="name_asc">{{ __('users.sort_name_asc') }}</x-dropdown-item>
                        <x-dropdown-item data-value="name_desc">{{ __('users.sort_name_desc') }}</x-dropdown-item>
                    </x-dropdown>
                </div>

                <div class="filter-field">
                    <label>{{ __('users.per_page') }}</label>
                    <x-dropdown variant="light" :select-style="true" data-filter="per_page">
                        <x-slot:trigger><span class="select-value">10</span></x-slot:trigger>
                        <x-dropdown-item selected data-value="10">10</x-dropdown-item>
                        <x-dropdown-item data-value="25">25</x-dropdown-item>
                        <x-dropdown-item data-value="50">50</x-dropdown-item>
                        <x-dropdown-item data-value="100">100</x-dropdown-item>
                        <x-dropdown-divider />
                        <div class="gdropdown-item custom-number-item" role="menuitem">
                            <x-icon name="plus" />
                            <input type="number" class="page-size-custom-input" min="1" placeholder="{{ __('users.custom_number') }}">
                        </div>
                    </x-dropdown>
                </div>
            </div>

            <div class="filters-actions">
                <button type="button" class="btn btn-primary btn-sm" id="applyFiltersBtn">{{ __('users.apply_filters') }}</button>
                <button type="button" class="btn-ghost" id="resetFiltersBtn">{{ __('users.reset_filters') }}</button>
            </div>
        </div>
    </div>

    <div class="table-toolbar">
        <span class="result-count" id="resultCount"></span>

        <div class="table-toolbar-actions">
            <x-dropdown variant="light" role="export">
                <x-slot:trigger><x-icon name="download" style="width:15px;height:15px;" /><span>{{ __('users.export') }}</span></x-slot:trigger>
                <a href="{{ route('admin.users.export', 'excel') }}" data-export="excel" class="gdropdown-item"><x-icon name="file-spreadsheet" /> Excel</a>
                <a href="{{ route('admin.users.export', 'csv') }}" data-export="csv" class="gdropdown-item"><x-icon name="file-text" /> CSV</a>
                <div class="gdropdown-item" role="menuitem" onclick="window.print()"><x-icon name="printer" /> {{ __('users.print') }}</div>
            </x-dropdown>

            <x-dropdown variant="light" role="columns">
                <x-slot:trigger><x-icon name="columns" style="width:15px;height:15px;" /><span>{{ __('users.columns') }}</span></x-slot:trigger>
                <label class="gdropdown-item checkbox-item"><input type="checkbox" checked data-column="user"> {{ __('users.column_user') }}</label>
                <label class="gdropdown-item checkbox-item"><input type="checkbox" checked data-column="status"> {{ __('users.column_status') }}</label>
                <label class="gdropdown-item checkbox-item"><input type="checkbox" checked data-column="verified"> {{ __('users.column_verified') }}</label>
                <label class="gdropdown-item checkbox-item"><input type="checkbox" checked data-column="joined"> {{ __('users.column_joined') }}</label>
            </x-dropdown>
        </div>
    </div>

    <div class="print-only">
        <h1>{{ __('navigation.users') }}</h1>
        <p>{{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th data-col="user">{{ __('users.column_user') }}</th>
                    <th data-col="status">{{ __('users.column_status') }}</th>
                    <th data-col="verified">{{ __('users.column_verified') }}</th>
                    <th data-col="joined">{{ __('users.column_joined') }}</th>
                    <th data-col="actions"></th>
                </tr>
            </thead>
            <tbody id="usersTableBody">
                <tr class="table-state-row"><td colspan="5">{{ __('users.loading') }}</td></tr>
            </tbody>
        </table>
        <div class="table-foot">
            <div class="pagination" id="usersPagination"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.usersRoutes = {
        data: @json(route('admin.users.data')),
        destroy: @json(route('admin.users.destroy', ['user' => '__ID__'])),
        exportExcel: @json(route('admin.users.export', 'excel')),
        exportCsv: @json(route('admin.users.export', 'csv')),
    };
    window.usersLabels = {
        loading: @json(__('users.loading')),
        empty: @json(__('users.empty')),
        error: @json(__('users.error')),
        retry: @json(__('users.retry')),
        yes: @json(__('users.yes')),
        no: @json(__('users.no')),
        edit: @json(__('users.edit')),
        view: @json(__('users.view')),
        delete: @json(__('users.delete')),
        cancel: @json(__('common.cancel')),
        active: @json(__('users.active')),
        inactive: @json(__('users.inactive')),
        idLabel: @json(__('users.id')),
        columnStatus: @json(__('users.column_status')),
        showingCount: @json(__('users.showing_count')),
        confirmDeleteTitle: @json(__('users.confirm_delete_title')),
        confirmDeleteMessage: @json(__('users.confirm_delete_message')),
        deleteSuccess: @json(__('users.delete_success')),
        deleteError: @json(__('users.delete_error')),
    };
</script>
<script src="{{ asset('assets/js/users-table.js') }}"></script>
<script src="{{ asset('assets/js/users-table.js') }}"></script>
@endpush