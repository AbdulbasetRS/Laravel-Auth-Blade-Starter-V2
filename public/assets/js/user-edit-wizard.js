/**
 * Edit User — 4-Step Form Wizard
 * Uses raw XMLHttpRequest (project convention — no Axios / fetch).
 * Floating labels, real-time validation, async availability checks with user exclusion,
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

  // Required fields per step for Edit (password is optional on edit)
  var REQUIRED_BY_STEP = {
    1: ['profile.first_name', 'profile.last_name'],
    2: ['email', 'mobile_number'],
    3: ['username', 'status', 'type'],
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
  var availXhrs = {}; // fieldName → XMLHttpRequest (in-flight)
  var initialValues = {};
  var isSubmitting = false;

  var avatarFile = null; // File | null

  /* ────────────────────────────────────────────────────────────────
     DOM references
  ──────────────────────────────────────────────────────────────── */
  var wizardEl = document.getElementById('editUserWizard');
  if (!wizardEl) return;

  var updateUrl       = wizardEl.dataset.updateUrl;
  var checkUrl        = wizardEl.dataset.checkUrl;
  var showUrlTemplate = wizardEl.dataset.showUrl;
  var userId          = wizardEl.dataset.userId;
  var currentAvatar   = wizardEl.dataset.currentAvatar || '';

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
    void fl.offsetWidth;
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
     Password Visibility & Strength
  ──────────────────────────────────────────────────────────────── */
  function initPasswordToggles() {
    wizardEl.querySelectorAll('.fl-pw-toggle').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var targetId = btn.dataset.target;
        var inp = document.getElementById(targetId);
        if (!inp) return;
        var isPassword = inp.type === 'password';
        inp.type = isPassword ? 'text' : 'password';
        btn.textContent = isPassword ? 'Hide' : 'Show';
        inp.focus();
      });
    });
  }

  function updatePasswordStrength(val) {
    var fill  = document.getElementById('pwStrengthFill');
    var label = document.getElementById('pwStrengthLabel');
    if (!fill || !label) return;

    if (!val) {
      fill.className = 'pw-strength-bar-fill';
      label.textContent = '';
      return;
    }

    var score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    var levels = [
      { cls: 'weak',   text: 'Weak' },
      { cls: 'weak',   text: 'Weak' },
      { cls: 'medium', text: 'Fair' },
      { cls: 'medium', text: 'Good' },
      { cls: 'strong', text: 'Strong' },
      { cls: 'strong', text: 'Very Strong' },
    ];

    var lvl = levels[score] || levels[0];
    fill.className = 'pw-strength-bar-fill ' + lvl.cls;
    label.textContent = lvl.text;
  }

  /* ────────────────────────────────────────────────────────────────
     Toggle Switch (Can Login)
  ──────────────────────────────────────────────────────────────── */
  function initToggleSwitches() {
    wizardEl.querySelectorAll('.wiz-toggle-switch').forEach(function (sw) {
      var hiddenInput = document.getElementById(sw.dataset.target);
      if (hiddenInput && (hiddenInput.value === '1' || hiddenInput.value === 'true')) {
        sw.classList.add('on');
        sw.setAttribute('aria-pressed', 'true');
      } else if (hiddenInput) {
        sw.classList.remove('on');
        sw.setAttribute('aria-pressed', 'false');
      }

      sw.addEventListener('click', function () {
        sw.classList.toggle('on');
        var isOn = sw.classList.contains('on');
        sw.setAttribute('aria-pressed', isOn ? 'true' : 'false');
        if (hiddenInput) hiddenInput.value = isOn ? '1' : '0';
      });
    });

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
        previewImg.style.display = 'block';
        previewWrap.classList.add('has-image');
        if (removeBtn) removeBtn.style.display = 'inline-flex';
      };
      reader.readAsDataURL(file);
    });

    if (removeBtn) {
      removeBtn.addEventListener('click', function () {
        avatarFile = null;
        fileInput.value = '';
        if (currentAvatar) {
          previewImg.src = currentAvatar;
          previewImg.style.display = 'block';
          previewWrap.classList.add('has-image');
        } else {
          previewImg.src = '';
          previewImg.style.display = 'none';
          previewWrap.classList.remove('has-image');
          removeBtn.style.display = 'none';
        }
        if (avatarError) avatarError.style.display = 'none';
      });
    }
  }

  /* ────────────────────────────────────────────────────────────────
     Async Availability Check (with Exclude Current User)
  ──────────────────────────────────────────────────────────────── */
  function abortAvailabilityCheck(fieldName) {
    clearTimeout(availTimers[fieldName]);
    availTimers[fieldName] = null;
    if (availXhrs[fieldName]) {
      try { availXhrs[fieldName].abort(); } catch (e) { /* ignore */ }
      availXhrs[fieldName] = null;
    }
  }

  function beginAvailabilityRecheck(fieldName, wrap) {
    abortAvailabilityCheck(fieldName);
    availCache[fieldName] = null;
    clearError(fieldName);
    if (wrap) {
      wrap.classList.remove('avail-ok', 'avail-taken');
      wrap.classList.add('checking');
    }
  }

  function scheduleAvailabilityCheck(fieldName, value) {
    var wrap = document.getElementById(fieldName.replace(/\./g, '_') + '_wrap');

    if (!value || value.length < 2) {
      abortAvailabilityCheck(fieldName);
      availCache[fieldName] = null;
      clearError(fieldName);
      if (wrap) { wrap.classList.remove('checking', 'avail-ok', 'avail-taken'); }
      return;
    }

    // Unchanged from user's current value — valid immediately (no network)
    if (initialValues[fieldName] !== undefined && value === initialValues[fieldName]) {
      abortAvailabilityCheck(fieldName);
      if (wrap) {
        wrap.classList.remove('checking', 'avail-taken');
        wrap.classList.add('avail-ok');
      }
      availCache[fieldName] = 'ok';
      clearError(fieldName);
      return;
    }

    // Any change: drop previous result and show spinner, then recheck
    beginAvailabilityRecheck(fieldName, wrap);

    availTimers[fieldName] = setTimeout(function () {
      // Value may have changed again during debounce
      var current = getValue(fieldName);
      if (current !== value) return;
      doAvailabilityCheck(fieldName, value, wrap);
    }, 420);
  }

  function doAvailabilityCheck(fieldName, value, wrap) {
    abortAvailabilityCheck(fieldName);
    availCache[fieldName] = null;

    if (wrap) {
      wrap.classList.remove('avail-ok', 'avail-taken');
      wrap.classList.add('checking');
    }

    var xhr = new XMLHttpRequest();
    availXhrs[fieldName] = xhr;

    var url = checkUrl + '?field=' + encodeURIComponent(fieldName) + '&value=' + encodeURIComponent(value);
    if (userId) {
      url += '&exclude_user_id=' + encodeURIComponent(userId);
    }
    xhr.open('GET', url, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== XMLHttpRequest.DONE) return;
      if (availXhrs[fieldName] !== xhr) return; // superseded
      availXhrs[fieldName] = null;

      // Ignore stale responses if the user kept typing
      if (getValue(fieldName) !== value) return;
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

  function hasPendingAvailabilityChecks(stepNum) {
    var panel = getPanel(stepNum);
    if (!panel) return false;
    var pending = false;
    panel.querySelectorAll('.fl-input[data-field-name]').forEach(function (inp) {
      var name = inp.dataset.fieldName;
      if (!name || UNIQUE_FIELDS.indexOf(name) === -1) return;
      var val = inp.value.trim();
      if (!val || val.length < 2) return;
      if (initialValues[name] !== undefined && val === initialValues[name]) return;
      if (availCache[name] == null) pending = true;
    });
    return pending;
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
        // Optional on edit — only validate if entered
        if (val && val.length < 8) msg = 'Password must be at least 8 characters.';
        break;
      case 'password_confirmation':
        var pw = getValue('password');
        if (pw && !val)      msg = 'Please confirm your password.';
        else if (pw && val !== pw) msg = 'Passwords do not match.';
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

    // Also check password confirmation if password was provided in step 3
    if (stepNum === 3) {
      var pw = getValue('password');
      if (pw) {
        touched['password'] = true;
        touched['password_confirmation'] = true;
        if (!validateField('password')) {
          valid = false;
          if (!firstInvalid) firstInvalid = getField('password');
        }
        if (!validateField('password_confirmation')) {
          valid = false;
          if (!firstInvalid) firstInvalid = getField('password_confirmation');
        }
      }
    }

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

    wizardEl.querySelectorAll('.fl-input[data-field-name]').forEach(function (inp) {
      var name = inp.dataset.fieldName;
      var val  = inp.type === 'checkbox' ? inp.checked : inp.value.trim();

      if (name.startsWith('profile.')) {
        var key = name.slice(8);
        data.profile[key] = val === '' ? null : val;
      } else {
        // If password is blank on edit, omit it
        if ((name === 'password' || name === 'password_confirmation') && val === '') {
          return;
        }
        data[name] = val === '' ? null : val;
      }
    });

    var canLoginInput = document.getElementById('can_login');
    if (canLoginInput) data.can_login = canLoginInput.value === '1' ? 1 : 0;

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
      var pwStatus = data.password ? '<span class="review-value" style="color:var(--primary)">•••••••• (Updated)</span>'
                                   : '<span class="review-value empty">(Unchanged)</span>';

      account.innerHTML =
        reviewField('Username',    rv(data.username)) +
        reviewField('Password',    pwStatus) +
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
    if (avatarFile) {
      var url = URL.createObjectURL(avatarFile);
      return reviewField('Avatar', '<img class="review-avatar-thumb" src="' + url + '" alt="Avatar">');
    }
    if (currentAvatar) {
      return reviewField('Avatar', '<img class="review-avatar-thumb" src="' + escHtml(currentAvatar) + '" alt="Avatar">');
    }
    return reviewField('Avatar', '<span class="review-value empty">—</span>');
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
    data._method = 'PUT'; // Method spoofing for PUT update

    var useFormData = !!avatarFile;
    var body;

    if (useFormData) {
      body = new FormData();
      function appendToFormData(fd, obj, prefix) {
        Object.keys(obj).forEach(function (key) {
          var fullKey = prefix ? prefix + '[' + key + ']' : key;
          var val = obj[key];
          if (val !== null && typeof val === 'object' && !(val instanceof File)) {
            appendToFormData(fd, val, fullKey);
          } else if (val !== null && val !== undefined) {
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
    } else {
      body = JSON.stringify(data);
    }

    var xhr = new XMLHttpRequest();
    // POST with _method=PUT handles both multipart FormData and JSON gracefully in Laravel
    xhr.open('POST', updateUrl, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('Accept', 'application/json');
    applyCsrfHeaders(xhr);
    if (!useFormData) xhr.setRequestHeader('Content-Type', 'application/json');

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== XMLHttpRequest.DONE) return;
      setSubmitLoading(false);
      isSubmitting = false;

      if (xhr.status >= 200 && xhr.status < 300) {
        try { var resp = JSON.parse(xhr.responseText); } catch(e) { var resp = {}; }
        if (window.Toast) Toast.success(resp.message || 'User updated successfully.');
        setTimeout(function () {
          if (showUrlTemplate) {
            window.location.href = showUrlTemplate;
          } else {
            window.location.reload();
          }
        }, 800);

      } else if (xhr.status === 422) {
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
        if (window.Toast) Toast.error('You do not have permission to edit this user.');

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

    var earliestStep = TOTAL_STEPS;
    keys.forEach(function (key) {
      var step = FIELD_STEP_MAP[key] || TOTAL_STEPS;
      if (step < earliestStep) earliestStep = step;
    });

    if (earliestStep !== currentStep) {
      goToStep(earliestStep, earliestStep < currentStep ? 'back' : 'forward');
    }

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
      if (submitBtn.querySelector('.btn-text')) submitBtn.querySelector('.btn-text').textContent = 'Saving Changes...';
      var sp = submitBtn.querySelector('.wiz-spinner');
      if (sp) sp.style.display = 'inline-block';
    } else {
      submitBtn.disabled = false;
      if (submitBtn.querySelector('.btn-text')) submitBtn.querySelector('.btn-text').textContent = submitBtn.dataset.origText || 'Save Changes';
      var sp2 = submitBtn.querySelector('.wiz-spinner');
      if (sp2) sp2.style.display = 'none';
    }
  }

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
      if (hasPendingAvailabilityChecks(currentStep)) {
        if (window.Toast) Toast.info('Please wait until availability checks finish.');
        return;
      }
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
    // Record initial values so unmodified fields bypass uniqueness error
    wizardEl.querySelectorAll('.fl-input[data-field-name]').forEach(function (inp) {
      var name = inp.dataset.fieldName;
      if (name) {
        initialValues[name] = inp.value.trim();
        // If it's a unique field and already has a value, mark it avail-ok by default
        if (UNIQUE_FIELDS.indexOf(name) !== -1 && initialValues[name] !== '') {
          availCache[name] = 'ok';
          var wrap = document.getElementById(name.replace(/\./g, '_') + '_wrap');
          if (wrap) wrap.classList.add('avail-ok');
        }
      }
    });

    initFloatingLabels();
    initPasswordToggles();
    initToggleSwitches();
    initAvatarUpload();
    updateProgressIndicator();
    updateNavButtons();

    var firstPanel = getPanel(1);
    if (firstPanel) {
      firstPanel.classList.add('active');
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
