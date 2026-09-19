@extends('layouts.admin')

@section('page-title', __('users.create_title'))
@section('title', __('users.create_title'))

@section('content')
<div class="inner-body wizard-shell">

    {{-- ═══════════════════════════════════════════════════════════
         WIZARD DATA ELEMENT (passes PHP → JS)
    ═══════════════════════════════════════════════════════════ --}}
    <div id="createUserWizard"
         data-store-url="{{ route('admin.users.store', absolute: false) }}"
         data-check-url="{{ route('admin.users.check-availability', absolute: false) }}"
         data-show-url="{{ route('admin.users.show', ['user' => '__ID__'], absolute: false) }}">

        {{-- ──────────────────────────────────────────────────────
             PROGRESS HEADER
        ────────────────────────────────────────────────────── --}}
        <div class="wizard-progress">
            <h2 class="wizard-progress-title">{{ __('users.create_title') }}</h2>
            <p class="wizard-progress-subtitle">{{ __('users.create_subtitle') }}</p>

            <div class="wizard-steps-track" role="list">
                <div class="wizard-step-item active" data-step="1" role="listitem">
                    <div class="wizard-step-bubble" aria-label="Step 1: Personal Information">
                        <span class="step-num">1</span>
                        <svg class="step-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M20 6L9 17l-5-5"/></svg>
                    </div>
                    <span class="wizard-step-label">Personal Info</span>
                </div>
                <div class="wizard-step-item" data-step="2" role="listitem">
                    <div class="wizard-step-bubble" aria-label="Step 2: Contact & Identity">
                        <span class="step-num">2</span>
                        <svg class="step-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M20 6L9 17l-5-5"/></svg>
                    </div>
                    <span class="wizard-step-label">Contact & Identity</span>
                </div>
                <div class="wizard-step-item" data-step="3" role="listitem">
                    <div class="wizard-step-bubble" aria-label="Step 3: Account & Access">
                        <span class="step-num">3</span>
                        <svg class="step-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M20 6L9 17l-5-5"/></svg>
                    </div>
                    <span class="wizard-step-label">Account & Access</span>
                </div>
                <div class="wizard-step-item" data-step="4" role="listitem">
                    <div class="wizard-step-bubble" aria-label="Step 4: Review">
                        <span class="step-num">4</span>
                        <svg class="step-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M20 6L9 17l-5-5"/></svg>
                    </div>
                    <span class="wizard-step-label">Review</span>
                </div>
            </div>
        </div>

        {{-- ──────────────────────────────────────────────────────
             WIZARD CARD
        ────────────────────────────────────────────────────── --}}
        <div class="wizard-card">

            {{-- ══════════════════════════════════════════════════
                 STEP 1 — Personal Information
            ══════════════════════════════════════════════════ --}}
            <div class="wizard-panel active" data-step="1" role="tabpanel" aria-label="Step 1: Personal Information">
                <div class="wizard-card-inner">
                    <p class="wizard-panel-title">Personal Information</p>
                    <p class="wizard-panel-subtitle">Enter the user's basic personal details.</p>

                    <div class="wiz-grid">

                        {{-- Title --}}
                        <div class="fl-field">
                            <label class="fl-label" for="profile_title">Title</label>
                            <input class="fl-input" type="text" id="profile_title" name="profile[title]" data-field-name="profile.title" autocomplete="honorific-prefix" maxlength="50">
                            <span class="fl-error-msg"></span>
                        </div>

                        {{-- Gender --}}
                        <div class="fl-field">
                            <label class="fl-label" for="profile_gender">Gender</label>
                            <select class="fl-input fl-select" id="profile_gender" name="profile[gender]" data-field-name="profile.gender">
                                <option value=""></option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                            <span class="fl-error-msg"></span>
                        </div>

                        {{-- First Name --}}
                        <div class="fl-field">
                            <label class="fl-label" for="profile_first_name">First Name <span style="color:var(--error)">*</span></label>
                            <input class="fl-input" type="text" id="profile_first_name" name="profile[first_name]" data-field-name="profile.first_name" autocomplete="given-name" maxlength="100" required>
                            <span class="fl-error-msg"></span>
                        </div>

                        {{-- Last Name --}}
                        <div class="fl-field">
                            <label class="fl-label" for="profile_last_name">Last Name <span style="color:var(--error)">*</span></label>
                            <input class="fl-input" type="text" id="profile_last_name" name="profile[last_name]" data-field-name="profile.last_name" autocomplete="family-name" maxlength="100" required>
                            <span class="fl-error-msg"></span>
                        </div>

                        {{-- Middle Name --}}
                        <div class="fl-field wiz-full">
                            <label class="fl-label" for="profile_middle_name">Middle Name</label>
                            <input class="fl-input" type="text" id="profile_middle_name" name="profile[middle_name]" data-field-name="profile.middle_name" autocomplete="additional-name" maxlength="100">
                            <span class="fl-error-msg"></span>
                        </div>

                        {{-- Date of Birth --}}
                        <div class="fl-field">
                            <label class="fl-label" for="profile_date_of_birth">Date of Birth</label>
                            <input class="fl-input" type="date" id="profile_date_of_birth" name="profile[date_of_birth]" data-field-name="profile.date_of_birth" max="{{ date('Y-m-d') }}">
                            <span class="fl-error-msg"></span>
                        </div>

                        {{-- Spacer for grid alignment on desktop --}}
                        <div style="display:none;" aria-hidden="true"></div>

                    </div>

                    {{-- Avatar Upload --}}
                    <div class="fl-field wiz-full" style="margin-top:4px;">
                        <span style="font-size:13px;font-weight:600;color:var(--text);display:block;margin-bottom:10px;">Profile Photo</span>
                        <div class="avatar-upload-area">
                            <div class="avatar-preview-wrap" id="avatarPreviewWrap">
                                <svg class="avatar-placeholder-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="32" height="32"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                                <img id="avatarPreviewImg" src="" alt="Avatar preview">
                            </div>
                            <div class="avatar-upload-controls">
                                <p class="avatar-upload-hint">JPEG, PNG, JPG, GIF, or WebP<br>Max size: 2MB</p>
                                <div class="avatar-upload-actions">
                                    <button type="button" class="avatar-btn-choose" onclick="document.getElementById('avatar').click()">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                        Choose Photo
                                    </button>
                                    <button type="button" class="avatar-btn-remove" id="avatarRemoveBtn">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                        Remove
                                    </button>
                                </div>
                                <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" style="display:none;">
                                <span id="avatarErrorMsg" style="font-size:12px;color:var(--error);display:none;"></span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ══════════════════════════════════════════════════
                 STEP 2 — Contact & Identity
            ══════════════════════════════════════════════════ --}}
            <div class="wizard-panel" data-step="2" role="tabpanel" aria-label="Step 2: Contact & Identity">
                <div class="wizard-card-inner">
                    <p class="wizard-panel-title">Contact & Identity</p>
                    <p class="wizard-panel-subtitle">Enter contact details and identity documents.</p>

                    <div class="wiz-grid">

                        {{-- Email --}}
                        <div class="fl-field wiz-full">
                            <label class="fl-label" for="email">Email Address <span style="color:var(--error)">*</span></label>
                            <div class="fl-input-wrap has-avail-indicator" id="email_wrap">
                                <input class="fl-input" type="email" id="email" name="email" data-field-name="email" autocomplete="email" maxlength="255" required>
                                <div class="fl-avail-indicator" aria-live="polite">
                                    <span class="avail-spinner"></span>
                                    <svg class="avail-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                                    <svg class="avail-cross" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                </div>
                            </div>
                            <span class="fl-avail-msg" aria-live="polite"></span>
                            <span class="fl-error-msg"></span>
                        </div>

                        {{-- Mobile Number --}}
                        <div class="fl-field">
                            <label class="fl-label" for="mobile_number">Mobile Number <span style="color:var(--error)">*</span></label>
                            <div class="fl-input-wrap has-avail-indicator" id="mobile_number_wrap">
                                <input class="fl-input" type="tel" id="mobile_number" name="mobile_number" data-field-name="mobile_number" autocomplete="tel" maxlength="30" required>
                                <div class="fl-avail-indicator" aria-live="polite">
                                    <span class="avail-spinner"></span>
                                    <svg class="avail-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                                    <svg class="avail-cross" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                </div>
                            </div>
                            <span class="fl-avail-msg" aria-live="polite"></span>
                            <span class="fl-error-msg"></span>
                        </div>

                        {{-- WhatsApp --}}
                        <div class="fl-field">
                            <label class="fl-label" for="profile_whatsapp">WhatsApp</label>
                            <input class="fl-input" type="tel" id="profile_whatsapp" name="profile[whatsapp]" data-field-name="profile.whatsapp" maxlength="30">
                            <span class="fl-error-msg"></span>
                        </div>

                        {{-- Telegram --}}
                        <div class="fl-field">
                            <label class="fl-label" for="profile_telegram">Telegram</label>
                            <input class="fl-input" type="text" id="profile_telegram" name="profile[telegram]" data-field-name="profile.telegram" placeholder="" maxlength="100">
                            <span class="fl-error-msg"></span>
                        </div>

                        {{-- Nationality --}}
                        <div class="fl-field">
                            <label class="fl-label" for="nationality">Nationality</label>
                            <input class="fl-input" type="text" id="nationality" name="nationality" data-field-name="nationality" maxlength="100">
                            <span class="fl-error-msg"></span>
                        </div>

                        {{-- National ID --}}
                        <div class="fl-field">
                            <label class="fl-label" for="national_id">National ID</label>
                            <div class="fl-input-wrap has-avail-indicator" id="national_id_wrap">
                                <input class="fl-input" type="text" id="national_id" name="national_id" data-field-name="national_id" maxlength="50">
                                <div class="fl-avail-indicator" aria-live="polite">
                                    <span class="avail-spinner"></span>
                                    <svg class="avail-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                                    <svg class="avail-cross" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                </div>
                            </div>
                            <span class="fl-avail-msg" aria-live="polite"></span>
                            <span class="fl-error-msg"></span>
                        </div>

                        {{-- Passport Number --}}
                        <div class="fl-field">
                            <label class="fl-label" for="passport_number">Passport Number</label>
                            <div class="fl-input-wrap has-avail-indicator" id="passport_number_wrap">
                                <input class="fl-input" type="text" id="passport_number" name="passport_number" data-field-name="passport_number" maxlength="50">
                                <div class="fl-avail-indicator" aria-live="polite">
                                    <span class="avail-spinner"></span>
                                    <svg class="avail-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                                    <svg class="avail-cross" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                </div>
                            </div>
                            <span class="fl-avail-msg" aria-live="polite"></span>
                            <span class="fl-error-msg"></span>
                        </div>

                        {{-- Address --}}
                        <div class="fl-field wiz-full fl-field-textarea">
                            <label class="fl-label" for="profile_address">Address</label>
                            <textarea class="fl-input fl-textarea" id="profile_address" name="profile[address]" data-field-name="profile.address" rows="3" maxlength="500"></textarea>
                            <span class="fl-error-msg"></span>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════
                 STEP 3 — Account & Access
            ══════════════════════════════════════════════════ --}}
            <div class="wizard-panel" data-step="3" role="tabpanel" aria-label="Step 3: Account & Access">
                <div class="wizard-card-inner">
                    <p class="wizard-panel-title">Account & Access</p>
                    <p class="wizard-panel-subtitle">Configure the user's login credentials and permissions.</p>

                    <div class="wiz-grid">

                        {{-- Username --}}
                        <div class="fl-field">
                            <label class="fl-label" for="username">Username <span style="color:var(--error)">*</span></label>
                            <div class="fl-input-wrap has-avail-indicator" id="username_wrap">
                                <input class="fl-input" type="text" id="username" name="username" data-field-name="username" autocomplete="username" maxlength="100" required>
                                <div class="fl-avail-indicator" aria-live="polite">
                                    <span class="avail-spinner"></span>
                                    <svg class="avail-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                                    <svg class="avail-cross" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                </div>
                            </div>
                            <span class="fl-avail-msg" aria-live="polite"></span>
                            <span class="fl-error-msg"></span>
                        </div>

                        {{-- Role --}}
                        <div class="fl-field">
                            <label class="fl-label" for="role_id">Role</label>
                            <input class="fl-input" type="text" id="role_id" name="role_id" data-field-name="role_id" maxlength="100">
                            <span class="fl-error-msg"></span>
                        </div>

                        {{-- Password --}}
                        <div class="fl-field">
                            <label class="fl-label" for="password">Password <span style="color:var(--error)">*</span></label>
                            <div class="fl-input-wrap">
                                <input class="fl-input" type="password" id="password" name="password" data-field-name="password" autocomplete="new-password" minlength="8" required>
                                <button type="button" class="fl-pw-toggle" data-target="password">Show</button>
                            </div>
                            <div class="pw-strength-wrap">
                                <div class="pw-strength-bar-track"><div class="pw-strength-bar-fill" id="pwStrengthFill"></div></div>
                                <div class="pw-strength-label" id="pwStrengthLabel"></div>
                            </div>
                            <span class="fl-error-msg"></span>
                        </div>

                        {{-- Password Confirmation --}}
                        <div class="fl-field">
                            <label class="fl-label" for="password_confirmation">Confirm Password <span style="color:var(--error)">*</span></label>
                            <div class="fl-input-wrap">
                                <input class="fl-input" type="password" id="password_confirmation" name="password_confirmation" data-field-name="password_confirmation" autocomplete="new-password" required>
                                <button type="button" class="fl-pw-toggle" data-target="password_confirmation">Show</button>
                            </div>
                            <span class="fl-error-msg"></span>
                        </div>

                        {{-- User Type --}}
                        <div class="fl-field">
                            <label class="fl-label" for="type">User Type <span style="color:var(--error)">*</span></label>
                            <select class="fl-input fl-select" id="type" name="type" data-field-name="type" required>
                                <option value=""></option>
                                @foreach(\App\Enums\UserType::cases() as $userType)
                                    <option value="{{ $userType->value }}">{{ $userType->label() }}</option>
                                @endforeach
                            </select>
                            <span class="fl-error-msg"></span>
                        </div>

                        {{-- Status --}}
                        <div class="fl-field">
                            <label class="fl-label" for="status">Status <span style="color:var(--error)">*</span></label>
                            <select class="fl-input fl-select" id="status" name="status" data-field-name="status" required>
                                <option value=""></option>
                                @foreach(\App\Enums\UserStatus::cases() as $userStatus)
                                    <option value="{{ $userStatus->value }}" {{ $userStatus->value === 'pending' ? 'selected' : '' }}>{{ $userStatus->label() }}</option>
                                @endforeach
                            </select>
                            <span class="fl-error-msg"></span>
                        </div>

                        {{-- Credits --}}
                        <div class="fl-field">
                            <label class="fl-label" for="credits">Credits</label>
                            <input class="fl-input" type="number" id="credits" name="credits" data-field-name="credits" min="0" value="0">
                            <span class="fl-error-msg"></span>
                        </div>

                        {{-- Spacer --}}
                        <div aria-hidden="true"></div>

                        {{-- Can Login Toggle --}}
                        <div class="wiz-full" style="margin-bottom:20px;">
                            <div class="wiz-toggle-field" role="group" aria-labelledby="canLoginLabel">
                                <div>
                                    <div class="wiz-toggle-label" id="canLoginLabel">Can Login</div>
                                    <div class="wiz-toggle-desc">Allow this user to log in to the system.</div>
                                </div>
                                <button type="button" class="wiz-toggle-switch on" data-target="can_login" aria-pressed="true" id="canLoginToggle"></button>
                            </div>
                            <input type="hidden" id="can_login" name="can_login" value="1">
                        </div>

                        {{-- Status Details --}}
                        <div class="fl-field wiz-full fl-field-textarea">
                            <label class="fl-label" for="status_details">Status Details / Notes</label>
                            <textarea class="fl-input fl-textarea" id="status_details" name="status_details" data-field-name="status_details" rows="3" maxlength="1000"></textarea>
                            <span class="fl-error-msg"></span>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════
                 STEP 4 — Review
            ══════════════════════════════════════════════════ --}}
            <div class="wizard-panel" data-step="4" role="tabpanel" aria-label="Step 4: Review">
                <div class="wizard-card-inner">
                    <p class="wizard-panel-title">Review & Confirm</p>
                    <p class="wizard-panel-subtitle">Please review all information before creating the user.</p>

                    {{-- Personal Information --}}
                    <div class="review-section">
                        <div class="review-section-header">
                            <span class="review-section-title">Personal Information</span>
                            <button type="button" class="review-section-edit" data-edit-step="1">Edit</button>
                        </div>
                        <div class="review-grid" id="reviewPersonal"></div>
                    </div>

                    {{-- Contact & Identity --}}
                    <div class="review-section">
                        <div class="review-section-header">
                            <span class="review-section-title">Contact & Identity</span>
                            <button type="button" class="review-section-edit" data-edit-step="2">Edit</button>
                        </div>
                        <div class="review-grid" id="reviewContact"></div>
                    </div>

                    {{-- Account & Access --}}
                    <div class="review-section">
                        <div class="review-section-header">
                            <span class="review-section-title">Account & Access</span>
                            <button type="button" class="review-section-edit" data-edit-step="3">Edit</button>
                        </div>
                        <div class="review-grid" id="reviewAccount"></div>
                    </div>

                </div>
            </div>

            {{-- ──────────────────────────────────────────────────────
                 WIZARD FOOTER NAV
            ────────────────────────────────────────────────────── --}}
            <div class="wizard-footer">
                <div class="wizard-footer-left">
                    <button type="button" id="wizBackBtn" class="btn-ghost" style="display:none;">
                        ← Back
                    </button>
                    <a href="{{ route('admin.users.index') }}" id="wizCancelBtn" class="btn-ghost">Cancel</a>
                </div>
                <div class="wizard-footer-right">
                    <span class="wizard-step-hint" id="wizStepHint">Step 1 of 4</span>
                    <button type="button" id="wizNextBtn" class="btn btn-primary">
                        Next →
                    </button>
                    <button type="button" id="wizSubmitBtn" class="btn btn-primary" style="display:none;">
                        <span class="wiz-spinner" style="display:none;margin-inline-end:6px;"></span>
                        <span class="btn-text">Create User</span>
                    </button>
                </div>
            </div>

        </div>{{-- end .wizard-card --}}
    </div>{{-- end #createUserWizard --}}

</div>{{-- end .inner-body --}}
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/user-wizard.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/user-create-wizard.js') }}"></script>
@endpush