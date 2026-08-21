<?php
return [
    'search_placeholder' => 'Search by username, email or mobile...',
    'filters'            => 'Filters',

    // ─── Status ──────────────────────────────────────────────────────────────
    'status'       => 'Status',
    'all_statuses' => 'All statuses',
    'active'       => 'Active',
    'inactive'     => 'Inactive',
    'suspended'    => 'Suspended',
    'banned'       => 'Banned',
    'pending'      => 'Pending',
    'deleted'      => 'Deleted',

    // ─── Type ────────────────────────────────────────────────────────────────
    'type'      => 'Type',
    'all_types' => 'All types',
    'type_user'     => 'User',
    'type_admin'    => 'Admin',
    'type_it'       => 'IT',
    'type_tester'   => 'Tester',
    'type_employee' => 'Employee',

    // ─── Verification ────────────────────────────────────────────────────────
    'verified_status' => 'Verification status',
    'all'             => 'All',
    'verified'        => 'Verified',
    'unverified'      => 'Unverified',

    // ─── Date filters ────────────────────────────────────────────────────────
    'date_from'   => 'From date',
    'date_to'     => 'To date',
    'choose_date' => 'Choose a date',
    'clear_date'  => 'Clear date',
    'weekday_sun' => 'Su', 'weekday_mon' => 'Mo', 'weekday_tue' => 'Tu',
    'weekday_wed' => 'We', 'weekday_thu' => 'Th', 'weekday_fri' => 'Fr', 'weekday_sat' => 'Sa',

    // ─── Sort / pagination ───────────────────────────────────────────────────
    'sort'          => 'Sort',
    'sort_newest'   => 'Newest first',
    'sort_oldest'   => 'Oldest first',
    'sort_name_asc' => 'Username A-Z',
    'sort_name_desc'=> 'Username Z-A',
    'per_page'      => 'Rows per page',
    'custom_number' => 'Custom number...',
    'apply_filters' => 'Apply filters',
    'reset_filters' => 'Reset',

    // ─── Toolbar ─────────────────────────────────────────────────────────────
    'export'  => 'Export',
    'print'   => 'Print',
    'columns' => 'Columns',

    // ─── Table columns ───────────────────────────────────────────────────────
    'column_user'     => 'User',
    'column_status'   => 'Status',
    'column_type'     => 'Type',
    'column_verified' => 'Verified?',
    'column_joined'   => 'Joined',

    // ─── Table states ────────────────────────────────────────────────────────
    'loading'       => 'Loading...',
    'empty'         => 'No records found.',
    'error'         => 'Unable to load data.',
    'retry'         => 'Retry',
    'showing_count' => 'Showing :from–:to of :total users',

    // ─── Actions ─────────────────────────────────────────────────────────────
    'yes'    => 'Yes',
    'no'     => 'No',
    'edit'   => 'Edit',
    'view'   => 'View',
    'delete' => 'Delete',

    // ─── Delete modal ────────────────────────────────────────────────────────
    'id'                     => 'ID',
    'confirm_delete_title'   => 'Delete User',
    'confirm_delete_message' => 'Are you sure you want to delete this user? This action cannot be undone.',
    'delete_success'         => 'User deleted successfully.',
    'delete_error'           => 'Unable to delete user.',

    // ─── Show page ───────────────────────────────────────────────────────────
    'updated_at'     => 'Last Updated',
    'back_to_list'   => 'Back to Users',
    'mobile_number'  => 'Mobile Number',
    'national_id'    => 'National ID',
    'passport_number'=> 'Passport Number',
    'nationality'    => 'Nationality',
    'credits'        => 'Credits',
    'can_login'      => 'Can Login',
    'status_details' => 'Status Details',
    'role_id'        => 'Role',

    // ─── Edit page ───────────────────────────────────────────────────────────
    'edit_title'    => 'Edit User — :name',
    'edit_subtitle' => 'Update this user\'s details below.',
    'username'      => 'Username',
    'email'         => 'Email',
    'password_new'  => 'New Password',
    'password_hint' => 'Leave blank to keep the current password.',
    'save'          => 'Save changes',
    'update_success'=> 'User updated successfully.',
    'update_error'  => 'Unable to update the user.',

    // ─── Create page ─────────────────────────────────────────────────────────
    'create_title'   => 'Create User',
    'create_subtitle'=> 'Add a new user to the system.',
    'password_label' => 'Password',
    'create_user'    => 'Create user',
    'create_success' => 'User created successfully.',
    'create_error'   => 'Unable to create the user.',
];
