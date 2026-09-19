/**
 * Create User — 4-Step Form Wizard
 * Uses raw XMLHttpRequest (project convention — no Axios / fetch).
 * Floating labels, real-time validation, async availability checks,
 * cross-step 422 error routing, avatar preview, password strength.
 */
(function () {
  'use strict';

  /* ────────────────────────────────────────────────────────────────
     Configuration
  ──────────────────────────────────────────────────────────────── */
  var TOTAL_STEPS = 4;

  // Map field names → step index (1-based)
  var FIELD_STEP_MAP = {
    // Step 1 — Personal
    'profile.first_name':    1, 'profile.middle_name': 1, 'profile.last_name': 1,
    'profile.title':         1, 'profile.gender':       1, 'profile.date_of_birth': 1,
    'avatar':                1,
    // Step 2 — Contact & Identity
    'email':                 2, 'mobile_number':        2, 'profile.whatsapp':      2,
    'profile.telegram':      2, 'profile.address':      2, 'national_id':           2,
    'nationality':           2, 'passport_number':      2,
    // Step 3 — Account & Access
    'username':              3, 'password':             3, 'password_confirmation': 3,
    'type':                  3, 'role_id':              3, 'status':                3,
    'credits':               3, 'can_login':            3, 'status_details':        3,
  };

  // Required fields per step
  var REQUIRED_BY_STEP = {
    1: ['profile.first_name', 'profile.last_name'],
    2: ['email', 'mobile_number'],
    3: ['username', 'password', 'password_confirmation', 'status', 'type'],
  };

  // Fields that need async uniqueness checking
  var UNIQUE_FIELDS = ['username', 'email', 'mobile_number', 'national_id', 'passport_number'];

  /* ────────────────────────────────────────────────────────────────
     State
  ──────────────────────────────────────────────────────────────── */
  var currentStep = 1;
  var touched = {}; // fieldName → true once blurred
  var availCache = {}; // fieldName → 'ok' | 'taken' | null
  var availTimers = {};
  var isSubmitting = false;

  // Collected data across all steps
  var formData = {
    profile: {}
  };

  var avatarFile = null; // File | null

  /* ────────────────────────────────────────────────────────────────
     DOM references
  ──────────────────────────────────────────────────────────────── */
  var wizardEl = document.getElementById('createUserWizard');
  if (!wizardEl) return;

  var storeUrl        = wizardEl.dataset.storeUrl;
  var checkUrl        = wizardEl.dataset.checkUrl;
  var showUrlTemplate = wizardEl.dataset.showUrl;

  function getStepPanels()  { return wizardEl.querySelectorAll('.wizard-panel'); }
  function getStepItems()   { return wizardEl.querySelectorAll('.wizard-step-item'); }
  function getPanel(n)      { return wizardEl.querySelector('.wizard-panel[data-step="' + n + '"]'); }
  function getStepItem(n)   { return wizardEl.querySelector('.wizard-step-item[data-step="' + n + '"]'); }
  var nextBtn       = document.getElementById('wizNextBtn');
  var backBtn       = document.getElementById('wizBackBtn');
  var submitBtn     = document.getElementById('wizSubmitBtn');
  var cancelBtn     = document.getElementById('wizCancelBtn');

  /* ────────────────────────────────────────────────────────────────
     Helpers
  ──────────────────────────────────────────────────────────────── */
  var ICON_ERROR =
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
    '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12.5"/><circle cx="12" cy="16" r="0.5" fill="currentColor"/>' +
    '</svg>';
  var ICON_SUCCESS =
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
    '<circle cx="12" cy="12" r="10"/><path d="M8.5 12.5l2.5 2.5 4.5-5"/>' +
    '</svg>';

  function getField(name) {
    // For profile.* fields, the input uses id like "profile_first_name"
    var id = name.replace(/\./g, '_');
    return wizardEl.querySelector('#' + id);
  }

  function getFlField(name) {
    var el = getField(name);
    return el ? el.closest('.fl-field') : null;
  }

  function ensureInputWrap(fl) {
    var input = fl.querySelector('.fl-input');
    if (!input) return null;
    var wrap = input.closest('.fl-input-wrap');
    if (wrap) return wrap;

    wrap = document.createElement('div');
    wrap.className = 'fl-input-wrap';
    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);

    // Keep password toggle inside the wrap if it was a sibling
    var toggle = fl.querySelector('.fl-pw-toggle');
    if (toggle && toggle.parentNode === fl) wrap.appendChild(toggle);

    return wrap;
  }

  function ensureValidationUi(fl) {
    fl.classList.add('vfield');
    var wrap = ensureInputWrap(fl);
    if (!wrap) return null;

    var btn = wrap.querySelector('.vfield-icon');
    if (!btn) {
      btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'vfield-icon';
      btn.setAttribute('aria-label', 'Show validation message');
      btn.innerHTML = ICON_ERROR;
      wrap.appendChild(btn);
    }

    var pop = fl.querySelector('.vfield-popover');
    if (!pop) {
      pop = document.createElement('div');
      pop.className = 'vfield-popover';
      pop.setAttribute('role', 'tooltip');
      pop.innerHTML = '<p></p>';
      fl.appendChild(pop);
    }

    if (window.GlobalValidation) window.GlobalValidation.initField(fl);
    return { btn: btn, pop: pop };
  }

  function setFieldMessage(name, msg, type) {
    var fl = getFlField(name);
    if (!fl) return;

    fl.classList.remove('has-error', 'has-success', 'popover-open');
    fl.dataset.pinned = '';

    // Hide legacy under-field text slots
    var legacyErr = fl.querySelector('.fl-error-msg');
    if (legacyErr) legacyErr.textContent = '';
    var legacyAvail = fl.querySelector('.fl-avail-msg');
    if (legacyAvail) legacyAvail.textContent = '';

    if (!msg) return;

    var ui = ensureValidationUi(fl);
    if (!ui) return;

    fl.classList.add(type === 'success' ? 'has-success' : 'has-error');
    ui.btn.innerHTML = type === 'success' ? ICON_SUCCESS : ICON_ERROR;
    var p = ui.pop.querySelector('p');
    if (p) p.textContent = msg;
    if (type === 'error') shakeField(fl);
  }

  function setError(name, msg) {
    setFieldMessage(name, msg || '', 'error');
  }

  function clearError(name) {
    var fl = getFlField(name);
    if (!fl) return;
    fl.classList.remove('has-error', 'has-success', 'popover-open');
    fl.dataset.pinned = '';
    var legacyErr = fl.querySelector('.fl-error-msg');
    if (legacyErr) legacyErr.textContent = '';
    var legacyAvail = fl.querySelector('.fl-avail-msg');
    if (legacyAvail) legacyAvail.textContent = '';
  }

  function setSuccess(name, msg) {
    setFieldMessage(name, msg || '', 'success');
  }

  function shakeField(fl) {
    if (!fl) return;
    fl.classList.remove('shake');
    void fl.offsetWidth; // reflow
    fl.classList.add('shake');
    fl.addEventListener('animationend', function () { fl.classList.remove('shake'); }, { once: true });
  }

  function getValue(name) {
    var el = getField(name);
    if (!el) return '';
    if (el.type === 'checkbox') return el.checked;
    return el.value.trim();
  }

  /* ────────────────────────────────────────────────────────────────
     Floating Label Logic
  ──────────────────────────────────────────────────────────────── */
  function updateFloatState(input) {
    var fl = input.closest('.fl-field');
    if (!fl) return;
    var hasVal = input.value !== '' || (input.tagName === 'SELECT' && input.value !== '');
    fl.classList.toggle('has-value', hasVal);
  }

  function initFloatingLabels() {
    wizardEl.querySelectorAll('.fl-input').forEach(function (inp) {
      updateFloatState(inp);

      inp.addEventListener('focus', function () {
        var fl = inp.closest('.fl-field');
        if (fl) fl.classList.add('focused');
      });
      inp.addEventListener('blur', function () {
        var fl = inp.closest('.fl-field');
        if (fl) fl.classList.remove('focused');
        updateFloatState(inp);
        var name = inp.dataset.fieldName;
        if (name) {
          touched[name] = true;
          validateField(name);
        }
      });
      inp.addEventListener('input', function () {
        updateFloatState(inp);
        var name = inp.dataset.fieldName;
        if (name && touched[name]) {
          validateField(name);
        }
        if (name && UNIQUE_FIELDS.indexOf(name) !== -1) {
          scheduleAvailabilityCheck(name, inp.value.trim());
        }
        if (name === 'password') {
          updatePasswordStrength(inp.value);
        }
      });
      inp.addEventListener('change', function () {
        updateFloatState(inp);
        var name = inp.dataset.fieldName;
        if (name && touched[name]) {
          validateField(name);
        }
      });
    });
  }

  /* ────────────────────────────────────────────────────────────────
     Password Strength
  ──────────────────────────────────────────────────────────────── */
  var pwStrengthFill  = document.getElementById('pwStrengthFill');
  var pwStrengthLabel = document.getElementById('pwStrengthLabel');

  function updatePasswordStrength(val) {
    if (!pwStrengthFill || !pwStrengthLabel) return;
    if (!val) {
      pwStrengthFill.className = 'pw-strength-bar-fill';
      pwStrengthLabel.className = 'pw-strength-label';
      pwStrengthLabel.textContent = '';
      return;
    }
    var score = 0;
    if (val.length >= 8)                           score++;
    if (/[A-Z]/.test(val))                         score++;
    if (/[0-9]/.test(val))                         score++;
    if (/[^A-Za-z0-9]/.test(val))                  score++;
    if (val.length >= 12)                          score++;

    var level = score <= 2 ? 'weak' : score <= 3 ? 'medium' : 'strong';
    var labels = { weak: 'Weak', medium: 'Medium', strong: 'Strong' };

    pwStrengthFill.className  = 'pw-strength-bar-fill ' + level;
    pwStrengthLabel.className = 'pw-strength-label ' + level;
    pwStrengthLabel.textContent = labels[level];
  }

  /* ────────────────────────────────────────────────────────────────
     Password Toggle
  ──────────────────────────────────────────────────────────────── */
  function initPasswordToggles() {
    wizardEl.querySelectorAll('.fl-pw-toggle').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var targetId = btn.dataset.target;
        var inp = document.getElementById(targetId);
        if (!inp) return;
        var isText = inp.type === 'text';
        inp.type = isText ? 'password' : 'text';
        btn.textContent = isText ? 'Show' : 'Hide';
      });
    });
  }

  /* ────────────────────────────────────────────────────────────────
     Toggle Switch (Can Login)
  ──────────────────────────────────────────────────────────────── */
  function initToggleSwitches() {
    wizardEl.querySelectorAll('.wiz-toggle-switch').forEach(function (sw) {
      var hiddenInput = document.getElementById(sw.dataset.target);
      // init state
      if (hiddenInput && hiddenInput.value === '1') sw.classList.add('on');

      sw.addEventListener('click', function () {
        sw.classList.toggle('on');
        if (hiddenInput) hiddenInput.value = sw.classList.contains('on') ? '1' : '0';
      });
    });
    // Also clicking the whole toggle field row
    wizardEl.querySelectorAll('.wiz-toggle-field').forEach(function (row) {
      row.addEventListener('click', function (e) {
        var sw = row.querySelector('.wiz-toggle-switch');
        if (sw && e.target !== sw) sw.click();
      });
    });
  }

  /* ────────────────────────────────────────────────────────────────
     Avatar Upload
  ──────────────────────────────────────────────────────────────── */
  function initAvatarUpload() {
    var fileInput    = document.getElementById('avatar');
    var previewWrap  = document.getElementById('avatarPreviewWrap');
    var previewImg   = document.getElementById('avatarPreviewImg');
    var removeBtn    = document.getElementById('avatarRemoveBtn');
    var avatarError  = document.getElementById('avatarErrorMsg');

    if (!fileInput || !previewWrap) return;

    fileInput.addEventListener('change', function () {
      var file = fileInput.files[0];
      if (!file) return;

      // Client-side validation
      var allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
      var maxSize = 2 * 1024 * 1024; // 2MB

      if (allowedTypes.indexOf(file.type) === -1) {
        if (avatarError) { avatarError.textContent = 'Only JPEG, PNG, JPG, GIF, or WEBP allowed.'; avatarError.style.display = 'block'; }
        fileInput.value = '';
        return;
      }
      if (file.size > maxSize) {
        if (avatarError) { avatarError.textContent = 'Avatar must be smaller than 2MB.'; avatarError.style.display = 'block'; }
        fileInput.value = '';
        return;
      }

      if (avatarError) avatarError.style.display = 'none';
      avatarFile = file;

      var reader = new FileReader();
      reader.onload = function (e) {
        previewImg.src = e.target.result;
        previewWrap.classList.add('has-image');
      };
      reader.readAsDataURL(file);
    });

    if (removeBtn) {
      removeBtn.addEventListener('click', function () {
        avatarFile = null;
        fileInput.value = '';
        previewImg.src = '';
        previewWrap.classList.remove('has-image');
        if (avatarError) avatarError.style.display = 'none';
      });
    }
  }

  /* ────────────────────────────────────────────────────────────────
     Async Availability Check
  ──────────────────────────────────────────────────────────────── */
  function scheduleAvailabilityCheck(fieldName, value) {
    clearTimeout(availTimers[fieldName]);
    var wrap = document.getElementById(fieldName.replace(/\./g,'_') + '_wrap');

    if (!value || value.length < 2) {
      if (wrap) { wrap.classList.remove('checking', 'avail-ok', 'avail-taken'); }
      availCache[fieldName] = null;
      return;
    }

    if (wrap) {
      wrap.classList.remove('avail-ok', 'avail-taken');
      wrap.classList.add('checking');
    }

    availTimers[fieldName] = setTimeout(function () {
      doAvailabilityCheck(fieldName, value, wrap);
    }, 420);
  }

  function doAvailabilityCheck(fieldName, value, wrap) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', checkUrl + '?field=' + encodeURIComponent(fieldName) + '&value=' + encodeURIComponent(value), true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== XMLHttpRequest.DONE) return;
      if (!wrap) return;
      wrap.classList.remove('checking');
      try {
        var resp = JSON.parse(xhr.responseText);
        if (resp.available) {
          wrap.classList.add('avail-ok');
          wrap.classList.remove('avail-taken');
          availCache[fieldName] = 'ok';
          setSuccess(fieldName, fieldName.replace(/_/g, ' ') + ' is available.');
        } else {
          wrap.classList.add('avail-taken');
          wrap.classList.remove('avail-ok');
          availCache[fieldName] = 'taken';
          if (touched[fieldName]) setError(fieldName, 'This ' + fieldName.replace(/_/g, ' ') + ' is already taken.');
        }
      } catch (e) { /* ignore */ }
    };

    xhr.send();
  }

  /* ────────────────────────────────────────────────────────────────
     Validation
  ──────────────────────────────────────────────────────────────── */
  function validateField(name) {
    var val = getValue(name);
    var msg = '';

    switch (name) {
      case 'profile.first_name':
        if (!val) msg = 'First name is required.';
        break;
      case 'profile.last_name':
        if (!val) msg = 'Last name is required.';
        break;
      case 'profile.date_of_birth':
        if (val && new Date(val) >= new Date()) msg = 'Date of birth must be in the past.';
        break;
      case 'email':
        if (!val)                         msg = 'Email is required.';
        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) msg = 'Enter a valid email address.';
        else if (availCache['email'] === 'taken') msg = 'This email is already taken.';
        break;
      case 'mobile_number':
        if (!val) msg = 'Mobile number is required.';
        else if (!/^\+?[0-9\s\-()]{6,20}$/.test(val.replace(/\s/g, ''))) msg = 'Enter a valid phone number.';
        else if (availCache['mobile_number'] === 'taken') msg = 'This mobile number is already taken.';
        break;
      case 'national_id':
        if (val && availCache['national_id'] === 'taken') msg = 'This national ID is already taken.';
        break;
      case 'passport_number':
        if (val && availCache['passport_number'] === 'taken') msg = 'This passport number is already taken.';
        break;
      case 'username':
        if (!val)            msg = 'Username is required.';
        else if (val.length < 3) msg = 'Username must be at least 3 characters.';
        else if (val.length > 100) msg = 'Username must not exceed 100 characters.';
        else if (availCache['username'] === 'taken') msg = 'This username is already taken.';
        break;
      case 'password':
        if (!val)          msg = 'Password is required.';
        else if (val.length < 8) msg = 'Password must be at least 8 characters.';
        break;
      case 'password_confirmation':
        var pw = getValue('password');
        if (!val)      msg = 'Please confirm your password.';
        else if (val !== pw) msg = 'Passwords do not match.';
        break;
      case 'credits':
        if (val !== '' && (isNaN(Number(val)) || Number(val) < 0)) msg = 'Credits must be a non-negative number.';
        break;
      case 'status':
        if (!val) msg = 'Status is required.';
        break;
      case 'type':
        if (!val) msg = 'User type is required.';
        break;
    }

    if (msg) { setError(name, msg); return false; }

    // Keep availability success message in the icon popover
    if (UNIQUE_FIELDS.indexOf(name) !== -1 && availCache[name] === 'ok') {
      setSuccess(name, name.replace(/_/g, ' ') + ' is available.');
    } else {
      clearError(name);
    }
    return true;
  }

  function validateStep(stepNum) {
    var required = REQUIRED_BY_STEP[stepNum] || [];
    var valid = true;
    var firstInvalid = null;

    required.forEach(function (name) {
      touched[name] = true;
      if (!validateField(name)) {
        valid = false;
        if (!firstInvalid) firstInvalid = getField(name);
      }
    });

    // Also check any already-touched fields on this step
    var panel = getPanel(stepNum);
    if (panel) {
      panel.querySelectorAll('.fl-input[data-field-name]').forEach(function (inp) {
        var name = inp.dataset.fieldName;
        if (name && touched[name]) {
          if (!validateField(name)) {
            valid = false;
            if (!firstInvalid) firstInvalid = inp;
          }
        }
      });
    }

    if (!valid && firstInvalid) {
      firstInvalid.focus();
    }
    return valid;
  }

  /* ────────────────────────────────────────────────────────────────
     Wizard Navigation
  ──────────────────────────────────────────────────────────────── */
  function goToStep(targetStep, direction) {
    if (targetStep < 1 || targetStep > TOTAL_STEPS) return;
    direction = direction || (targetStep > currentStep ? 'forward' : 'back');

    var currentPanel = getPanel(currentStep);
    var targetPanel  = getPanel(targetStep);
    if (!currentPanel || !targetPanel) return;

    // Animate out current
    var leavingClass  = direction === 'forward' ? 'leaving-forward' : 'leaving-back';
    var enteringClass = direction === 'forward' ? 'entering-forward' : 'entering-back';

    currentPanel.classList.remove('active');
    currentPanel.classList.add(leavingClass);

    targetPanel.classList.add(enteringClass, 'active');
    void targetPanel.offsetWidth;
    targetPanel.classList.remove(enteringClass);

    setTimeout(function () {
      currentPanel.classList.remove(leavingClass);
    }, 280);

    currentStep = targetStep;
    updateProgressIndicator();
    updateNavButtons();

    if (targetStep === TOTAL_STEPS) {
      populateReview();
    }

    // Scroll to top of wizard
    wizardEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function updateProgressIndicator() {
    getStepItems().forEach(function (item) {
      var s = parseInt(item.dataset.step, 10);
      item.classList.remove('active', 'completed');
      if (s === currentStep) item.classList.add('active');
      if (s < currentStep)   item.classList.add('completed');
    });
  }

  function updateNavButtons() {
    if (backBtn) backBtn.style.display   = currentStep === 1 ? 'none' : '';
    if (nextBtn)   nextBtn.style.display = currentStep < TOTAL_STEPS ? '' : 'none';
    if (submitBtn) submitBtn.style.display = currentStep === TOTAL_STEPS ? '' : 'none';
    var hint = document.getElementById('wizStepHint');
    if (hint) hint.textContent = 'Step ' + currentStep + ' of ' + TOTAL_STEPS;
  }

  /* ────────────────────────────────────────────────────────────────
     Collect Data from All Steps
  ──────────────────────────────────────────────────────────────── */
  function collectFormData() {
    var data = { profile: {} };

    // All inputs with data-field-name
    wizardEl.querySelectorAll('.fl-input[data-field-name]').forEach(function (inp) {
      var name = inp.dataset.fieldName;
      var val  = inp.type === 'checkbox' ? inp.checked : inp.value.trim();

      if (name.startsWith('profile.')) {
        var key = name.slice(8); // remove "profile."
        data.profile[key] = val === '' ? null : val;
      } else {
        data[name] = val === '' ? null : val;
      }
    });

    // Hidden inputs (can_login toggle) — send 1/0 so FormData + JSON both pass boolean rule
    var canLoginInput = document.getElementById('can_login');
    if (canLoginInput) data.can_login = canLoginInput.value === '1' ? 1 : 0;

    // Remove nullified password_confirmation from outer scope (it's only for validation)
    // keep password_confirmation in data so Laravel can validate it
    return data;
  }

  /* ────────────────────────────────────────────────────────────────
     Review Step
  ──────────────────────────────────────────────────────────────── */
  function populateReview() {
    var data = collectFormData();

    function rv(val) {
      return (val === null || val === undefined || val === '') ? '<span class="review-value empty">—</span>'
           : '<span class="review-value">' + escHtml(String(val)) + '</span>';
    }
    function rvBool(val) {
      return val ? '<span class="review-value" style="color:var(--success)">Yes</span>'
                 : '<span class="review-value" style="color:var(--muted)">No</span>';
    }

    // Personal
    var personal = document.getElementById('reviewPersonal');
    if (personal) {
      personal.innerHTML =
        reviewField('Title',         rv(data.profile.title)) +
        reviewField('First Name',    rv(data.profile.first_name)) +
        reviewField('Middle Name',   rv(data.profile.middle_name)) +
        reviewField('Last Name',     rv(data.profile.last_name)) +
        reviewField('Gender',        rv(data.profile.gender)) +
        reviewField('Date of Birth', rv(data.profile.date_of_birth)) +
        reviewAvatarField();
    }

    // Contact
    var contact = document.getElementById('reviewContact');
    if (contact) {
      contact.innerHTML =
        reviewField('Email',           rv(data.email)) +
        reviewField('Mobile',          rv(data.mobile_number)) +
        reviewField('WhatsApp',        rv(data.profile.whatsapp)) +
        reviewField('Telegram',        rv(data.profile.telegram)) +
        reviewField('Nationality',     rv(data.nationality)) +
        reviewField('National ID',     rv(data.national_id)) +
        reviewField('Passport Number', rv(data.passport_number)) +
        reviewField('Address',         rv(data.profile.address));
    }

    // Account
    var account = document.getElementById('reviewAccount');
    if (account) {
      account.innerHTML =
        reviewField('Username',    rv(data.username)) +
        reviewField('User Type',   rv(data.type)) +
        reviewField('Role',        rv(data.role_id)) +
        reviewField('Status',      rv(data.status)) +
        reviewField('Credits',     rv(data.credits)) +
        reviewField('Can Login',   rvBool(data.can_login)) +
        reviewField('Status Note', rv(data.status_details));
    }
  }

  function reviewField(label, valueHtml) {
    return '<div class="review-field"><span class="review-label">' + escHtml(label) + '</span>' + valueHtml + '</div>';
  }

  function reviewAvatarField() {
    if (!avatarFile) return reviewField('Avatar', '<span class="review-value empty">—</span>');
    var url = URL.createObjectURL(avatarFile);
    return reviewField('Avatar', '<img class="review-avatar-thumb" src="' + url + '" alt="Avatar">');
  }

  /* ────────────────────────────────────────────────────────────────
     Form Submission
  ──────────────────────────────────────────────────────────────── */
  function getCsrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? (meta.getAttribute('content') || '') : '';
  }

  function getXsrfTokenFromCookie() {
    var match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);
    return match ? decodeURIComponent(match[1]) : '';
  }

  function applyCsrfHeaders(xhr) {
    var csrfToken = getCsrfToken();
    if (csrfToken) xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
    var xsrf = getXsrfTokenFromCookie();
    if (xsrf) xhr.setRequestHeader('X-XSRF-TOKEN', xsrf);
    return csrfToken;
  }

  function handleSubmit() {
    if (isSubmitting) return;
    isSubmitting = true;
    setSubmitLoading(true);

    var data = collectFormData();
    var csrfToken = getCsrfToken();
    data._token = csrfToken;

    // Build FormData (for avatar) or JSON
    var useFormData = !!avatarFile;
    var body;

    if (useFormData) {
      body = new FormData();
      // Flatten the object into FormData
      function appendToFormData(fd, obj, prefix) {
        Object.keys(obj).forEach(function (key) {
          var fullKey = prefix ? prefix + '[' + key + ']' : key;
          var val = obj[key];
          if (val !== null && typeof val === 'object' && !(val instanceof File)) {
            appendToFormData(fd, val, fullKey);
          } else if (val !== null && val !== undefined) {
            // Booleans must be "1"/"0" — FormData stringifies true/false which Laravel rejects
            if (typeof val === 'boolean') {
              fd.append(fullKey, val ? '1' : '0');
            } else {
              fd.append(fullKey, val);
            }
          }
        });
      }
      appendToFormData(body, data, '');
      body.append('avatar', avatarFile);
      // No Content-Type header — browser sets multipart boundary automatically
    } else {
      body = JSON.stringify(data);
    }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', storeUrl, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('Accept', 'application/json');
    applyCsrfHeaders(xhr);
    if (!useFormData) xhr.setRequestHeader('Content-Type', 'application/json');

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== XMLHttpRequest.DONE) return;
      setSubmitLoading(false);
      isSubmitting = false;

      if (xhr.status >= 200 && xhr.status < 300) {
        // Success
        try { var resp = JSON.parse(xhr.responseText); } catch(e) { var resp = {}; }
        if (window.Toast) Toast.success('User created successfully.');
        var userId = (resp.id || (resp.data && resp.data.id));
        setTimeout(function () {
          if (showUrlTemplate && userId) {
            window.location.href = showUrlTemplate.replace('__ID__', userId);
          } else {
            window.location.href = storeUrl;
          }
        }, 800);

      } else if (xhr.status === 422) {
        // Validation errors
        try { var errResp = JSON.parse(xhr.responseText); } catch(e) { errResp = {}; }
        var errors = errResp.errors || {};
        handleValidationErrors(errors);

      } else if (xhr.status === 419) {
        if (window.Toast) Toast.error('Session expired. Reloading…');
        setTimeout(function () { window.location.reload(); }, 1200);

      } else if (xhr.status === 401) {
        if (window.Toast) Toast.error('Session expired. Please log in again.');
        setTimeout(function () { window.location.reload(); }, 1500);

      } else if (xhr.status === 403) {
        if (window.Toast) Toast.error('You do not have permission to create users.');

      } else if (xhr.status === 409) {
        if (window.Toast) Toast.error('A conflict occurred. Please check your data.');

      } else {
        if (window.Toast) Toast.error('An unexpected error occurred. Please try again.');
      }
    };

    xhr.send(body);
  }

  function handleValidationErrors(errors) {
    var keys = Object.keys(errors);
    if (keys.length === 0) return;

    // Find the earliest step that has an error
    var earliestStep = TOTAL_STEPS;
    keys.forEach(function (key) {
      var step = FIELD_STEP_MAP[key] || TOTAL_STEPS;
      if (step < earliestStep) earliestStep = step;
    });

    // Navigate to that step first
    if (earliestStep !== currentStep) {
      goToStep(earliestStep, earliestStep < currentStep ? 'back' : 'forward');
    }

    // Set all errors and focus the first field with an error on the target step
    var firstField = null;
    keys.forEach(function (key) {
      var msg = (errors[key] || [])[0] || '';
      touched[key] = true;
      setError(key, msg);
      var step = FIELD_STEP_MAP[key] || TOTAL_STEPS;
      if (step === earliestStep && !firstField) {
        firstField = getField(key);
      }
    });

    if (firstField) setTimeout(function () { firstField.focus(); }, 300);
  }

  function setSubmitLoading(loading) {
    if (!submitBtn) return;
    if (loading) {
      submitBtn.disabled = true;
      submitBtn.dataset.origText = submitBtn.querySelector('.btn-text') ?
        submitBtn.querySelector('.btn-text').textContent : submitBtn.textContent;
      if (submitBtn.querySelector('.btn-text')) submitBtn.querySelector('.btn-text').textContent = 'Creating User...';
      var sp = submitBtn.querySelector('.wiz-spinner');
      if (sp) sp.style.display = 'inline-block';
    } else {
      submitBtn.disabled = false;
      if (submitBtn.querySelector('.btn-text')) submitBtn.querySelector('.btn-text').textContent = submitBtn.dataset.origText || 'Create User';
      var sp2 = submitBtn.querySelector('.wiz-spinner');
      if (sp2) sp2.style.display = 'none';
    }
  }

  /* ────────────────────────────────────────────────────────────────
     Utility
  ──────────────────────────────────────────────────────────────── */
  function escHtml(str) {
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  /* ────────────────────────────────────────────────────────────────
     Button Events
  ──────────────────────────────────────────────────────────────── */
  if (nextBtn) {
    nextBtn.addEventListener('click', function () {
      if (!validateStep(currentStep)) return;
      goToStep(currentStep + 1, 'forward');
    });
  }

  if (backBtn) {
    backBtn.addEventListener('click', function () {
      goToStep(currentStep - 1, 'back');
    });
  }

  if (submitBtn) {
    submitBtn.addEventListener('click', function (e) {
      e.preventDefault();
      handleSubmit();
    });
  }

  // Review step "Edit" buttons
  wizardEl.querySelectorAll('[data-edit-step]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var targetStep = parseInt(btn.dataset.editStep, 10);
      goToStep(targetStep, 'back');
    });
  });

  /* ────────────────────────────────────────────────────────────────
     Initialise
  ──────────────────────────────────────────────────────────────── */
  function init() {
    initFloatingLabels();
    initPasswordToggles();
    initToggleSwitches();
    initAvatarUpload();
    updateProgressIndicator();
    updateNavButtons();

    // Ensure first panel is active
    var firstPanel = getPanel(1);
    if (firstPanel) {
      firstPanel.classList.add('active');
    }

    // Focus first input of first step
    setTimeout(function () {
      var inp = firstPanel && firstPanel.querySelector('.fl-input');
      if (inp) inp.focus();
    }, 100);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
