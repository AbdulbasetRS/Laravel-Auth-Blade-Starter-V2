/**
 * Edit User — mirrors the create-user wizard: XHR availability checks,
 * API validation and multipart API saving (including profile/avatar fields).
 */
(function () {
  'use strict';

  var form = document.getElementById('editUserWizard');
  if (!form) return;

  var TOTAL_STEPS = 4;
  var currentStep = 1;
  var isSubmitting = false;
  var availabilityTimers = {};
  var availability = {};
  var uniqueFields = ['username', 'email', 'mobile_number', 'national_id', 'passport_number'];
  var fieldSteps = {
    'profile.title': 1, 'profile.gender': 1, 'profile.first_name': 1, 'profile.middle_name': 1,
    'profile.last_name': 1, 'profile.date_of_birth': 1, 'avatar': 1,
    'email': 2, 'mobile_number': 2, 'profile.whatsapp': 2, 'profile.telegram': 2,
    'nationality': 2, 'national_id': 2, 'passport_number': 2, 'profile.address': 2,
    'username': 3, 'role_id': 3, 'password': 3, 'password_confirmation': 3,
    'type': 3, 'status': 3, 'credits': 3, 'can_login': 3, 'status_details': 3
  };

  var nextButton = document.getElementById('editNextBtn');
  var backButton = document.getElementById('editBackBtn');
  var submitButton = document.getElementById('editSubmitBtn');
  var stepHint = document.getElementById('editStepHint');
  var ICON_ERROR = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12.5"/><circle cx="12" cy="16" r="0.5" fill="currentColor"/></svg>';
  var ICON_SUCCESS = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M8.5 12.5l2.5 2.5 4.5-5"/></svg>';

  function fieldInput(name) {
    return form.querySelector('[data-field-name="' + name + '"]') || form.querySelector('[name="' + name + '"]');
  }
  function fieldBox(name) {
    var input = fieldInput(name);
    return input ? input.closest('.fl-field') : null;
  }
  function escapeHtml(value) { var node = document.createElement('div'); node.textContent = value; return node.innerHTML; }
  function value(name) { var input = fieldInput(name); return input ? input.value.trim() : ''; }
  function selectedText(name) { var input = fieldInput(name); return input && input.selectedIndex >= 0 ? input.options[input.selectedIndex].text : ''; }
  function errorTarget(name) { return form.querySelector('[data-error="' + name + '"]'); }

  function ensureInputWrap(box) {
    var input = box.querySelector('.fl-input');
    if (!input) return null;
    var wrap = input.closest('.fl-input-wrap');
    if (wrap) return wrap;
    wrap = document.createElement('div');
    wrap.className = 'fl-input-wrap';
    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);
    var passwordToggle = box.querySelector('.fl-pw-toggle');
    if (passwordToggle && passwordToggle.parentNode !== wrap) wrap.appendChild(passwordToggle);
    return wrap;
  }
  function ensureValidationUi(box) {
    if (!box) return null;
    box.classList.add('vfield');
    var wrap = ensureInputWrap(box);
    if (!wrap) return null;
    var icon = wrap.querySelector('.vfield-icon');
    if (!icon) {
      icon = document.createElement('button');
      icon.type = 'button';
      icon.className = 'vfield-icon';
      icon.setAttribute('aria-label', 'Show validation message');
      wrap.appendChild(icon);
    }
    var popover = box.querySelector('.vfield-popover');
    if (!popover) {
      popover = document.createElement('div');
      popover.className = 'vfield-popover';
      popover.setAttribute('role', 'tooltip');
      popover.innerHTML = '<p></p>';
      box.appendChild(popover);
    }
    if (window.GlobalValidation) window.GlobalValidation.initField(box);
    if (icon.dataset.editWizardPersistent !== 'true') {
      icon.dataset.editWizardPersistent = 'true';
      icon.addEventListener('click', function () {
        // GlobalValidation treats a second icon click as a toggle. In the
        // wizard, validation feedback must remain visible on every click.
        if (box.classList.contains('has-error') || box.classList.contains('has-success')) {
          box.classList.add('popover-open');
          box.dataset.pinned = 'true';
        }
      });
    }
    if (popover.dataset.editWizardInit !== 'true') {
      popover.dataset.editWizardInit = 'true';
      popover.addEventListener('click', function (event) {
        // Keep a clicked validation message pinned, rather than letting the
        // document-level click handler close it immediately.
        event.stopPropagation();
        box.classList.add('popover-open');
        box.dataset.pinned = 'true';
      });
    }
    return { icon: icon, popover: popover };
  }
  function setMessage(name, message, type) {
    if (name === 'avatar') {
      var avatarMessage = document.getElementById('avatarErrorMsg');
      if (avatarMessage) { avatarMessage.textContent = message || ''; avatarMessage.style.display = message ? 'block' : 'none'; }
      return;
    }
    var box = fieldBox(name);
    if (!box) return;
    box.classList.remove('has-error', 'has-success', 'popover-open');
    box.dataset.pinned = '';
    if (!message) return;
    var ui = ensureValidationUi(box);
    if (!ui) return;
    box.classList.add(type === 'success' ? 'has-success' : 'has-error');
    ui.icon.innerHTML = type === 'success' ? ICON_SUCCESS : ICON_ERROR;
    var paragraph = ui.popover.querySelector('p');
    if (paragraph) paragraph.textContent = message;
  }
  function setError(name, message) { setMessage(name, message, 'error'); }
  function setSuccess(name, message) { setMessage(name, message, 'success'); }
  function clearError(name) { setMessage(name, '', 'error'); }
  function clearAllErrors() {
    form.querySelectorAll('.fl-field').forEach(function (box) { box.classList.remove('has-error', 'has-success', 'popover-open'); box.dataset.pinned = ''; });
    form.querySelectorAll('[data-error]').forEach(function (target) { target.textContent = ''; });
    clearError('avatar');
  }
  function setAvailability(name, state, message) {
    availability[name] = state;
    var box = fieldBox(name);
    var wrap = box && ensureInputWrap(box);
    if (!wrap) return;
    wrap.classList.add('has-avail-indicator');
    if (!wrap.querySelector('.fl-avail-indicator')) {
      var indicator = document.createElement('div');
      indicator.className = 'fl-avail-indicator';
      indicator.setAttribute('aria-live', 'polite');
      indicator.innerHTML = '<span class="avail-spinner"></span><svg class="avail-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><svg class="avail-cross" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>';
      wrap.appendChild(indicator);
    }
    wrap.classList.remove('checking', 'avail-ok', 'avail-taken');
    if (state === 'checking') wrap.classList.add('checking');
    if (state === 'available') wrap.classList.add('avail-ok');
    if (state === 'taken') wrap.classList.add('avail-taken');
  }

  function updateFloatingLabel(input) {
    var box = input.closest('.fl-field');
    if (box) box.classList.toggle('has-value', input.value !== '');
  }
  function updateAllFloatingLabels() { form.querySelectorAll('.fl-input').forEach(updateFloatingLabel); }

  function checkAvailability(name) {
    var currentValue = value(name);
    if (!currentValue || currentValue.length < 2) {
      setAvailability(name, '', '');
      return;
    }
    setAvailability(name, 'checking');
    var xhr = new XMLHttpRequest();
    var url = form.dataset.checkUrl + '?field=' + encodeURIComponent(name) + '&value=' + encodeURIComponent(currentValue) + '&ignore_user=' + encodeURIComponent(form.dataset.userId);
    xhr.open('GET', url, true);
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.onreadystatechange = function () {
      if (xhr.readyState !== XMLHttpRequest.DONE) return;
      if (value(name) !== currentValue) return;
      try {
        var response = JSON.parse(xhr.responseText);
        if (xhr.status >= 200 && xhr.status < 300 && response.available) {
          setAvailability(name, 'available');
          setSuccess(name, name.replace(/_/g, ' ') + ' is available.');
        } else if (xhr.status >= 200 && xhr.status < 300) {
          setAvailability(name, 'taken');
          setError(name, 'This value is already in use.');
        } else {
          setAvailability(name, '');
        }
      } catch (error) {
        setAvailability(name, '');
      }
    };
    xhr.send();
  }
  function scheduleAvailability(name) {
    window.clearTimeout(availabilityTimers[name]);
    availabilityTimers[name] = window.setTimeout(function () { checkAvailability(name); }, 400);
  }

  function validateField(name) {
    var input = fieldInput(name);
    if (!input) return true;
    var inputValue = value(name);
    var message = '';
    if (input.required && !inputValue) message = 'This field is required.';
    if (!message && name === 'email' && inputValue && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(inputValue)) message = 'Enter a valid email address.';
    if (!message && name === 'mobile_number' && inputValue && !/^\+?[0-9\s\-()]{6,30}$/.test(inputValue)) message = 'Enter a valid phone number.';
    if (!message && name === 'username' && inputValue.length > 0 && inputValue.length < 3) message = 'Username must be at least 3 characters.';
    if (!message && name === 'password' && inputValue && inputValue.length < 8) message = 'Password must be at least 8 characters.';
    if (!message && name === 'password_confirmation' && inputValue !== value('password')) message = 'Passwords do not match.';
    if (!message && name === 'credits' && inputValue && Number(inputValue) < 0) message = 'Credits must be zero or greater.';
    if (!message && availability[name] === 'taken') message = 'This value is already in use.';
    setError(name, message);
    return !message;
  }
  function validateStep(step) {
    var valid = true;
    var firstInvalid = null;
    var panel = form.querySelector('.wizard-panel[data-step="' + step + '"]');
    if (!panel) return true;
    panel.querySelectorAll('[data-field-name]').forEach(function (input) {
      if (!validateField(input.dataset.fieldName)) {
        valid = false;
        if (!firstInvalid) firstInvalid = input;
      }
    });
    if (!valid && firstInvalid) firstInvalid.focus();
    return valid;
  }

  function reviewField(label, displayValue) {
    var empty = !displayValue;
    return '<div class="review-field"><span class="review-label">' + escapeHtml(label) + '</span><span class="review-value' + (empty ? ' empty' : '') + '">' + escapeHtml(displayValue || '—') + '</span></div>';
  }
  function populateReview() {
    document.getElementById('reviewPersonal').innerHTML =
      reviewField('Title', value('profile.title')) + reviewField('Gender', selectedText('profile.gender')) +
      reviewField('First Name', value('profile.first_name')) + reviewField('Middle Name', value('profile.middle_name')) +
      reviewField('Last Name', value('profile.last_name')) + reviewField('Date of Birth', value('profile.date_of_birth'));
    document.getElementById('reviewContact').innerHTML =
      reviewField('Email', value('email')) + reviewField('Mobile', value('mobile_number')) + reviewField('WhatsApp', value('profile.whatsapp')) +
      reviewField('Telegram', value('profile.telegram')) + reviewField('Nationality', value('nationality')) + reviewField('National ID', value('national_id')) +
      reviewField('Passport', value('passport_number')) + reviewField('Address', value('profile.address'));
    document.getElementById('reviewAccount').innerHTML =
      reviewField('Username', value('username')) + reviewField('Role', value('role_id')) + reviewField('User Type', selectedText('type')) +
      reviewField('Status', selectedText('status')) + reviewField('Credits', value('credits')) +
      reviewField('Can Login', value('can_login') === '1' ? 'Yes' : 'No') + reviewField('Password', value('password') ? 'Will be changed' : 'Unchanged') +
      reviewField('Status Note', value('status_details'));
  }

  function goToStep(target) {
    if (target < 1 || target > TOTAL_STEPS) return;
    currentStep = target;
    form.querySelectorAll('.wizard-panel').forEach(function (panel) { panel.classList.toggle('active', Number(panel.dataset.step) === target); });
    form.querySelectorAll('.wizard-step-item').forEach(function (item) {
      var number = Number(item.dataset.step);
      item.classList.toggle('active', number === target);
      item.classList.toggle('completed', number < target);
    });
    backButton.style.display = target === 1 ? 'none' : '';
    nextButton.style.display = target === TOTAL_STEPS ? 'none' : '';
    submitButton.style.display = target === TOTAL_STEPS ? '' : 'none';
    stepHint.textContent = 'Step ' + target + ' of ' + TOTAL_STEPS;
    if (target === TOTAL_STEPS) populateReview();
    updateAllFloatingLabels();
  }

  function csrfToken() {
    var token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
  }
  function setSubmitting(loading) {
    isSubmitting = loading;
    submitButton.disabled = loading;
    var spinner = submitButton.querySelector('.wiz-spinner');
    var text = submitButton.querySelector('.btn-text');
    if (spinner) spinner.style.display = loading ? 'inline-block' : 'none';
    if (text) text.textContent = loading ? 'Saving…' : 'Save';
  }
  function submitWithApi() {
    if (isSubmitting) return;
    setSubmitting(true);
    clearAllErrors();
    var body = new FormData(form);
    body.set('_method', 'PUT');
    var xhr = new XMLHttpRequest();
    xhr.open('POST', form.action, true);
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken());
    xhr.onreadystatechange = function () {
      if (xhr.readyState !== XMLHttpRequest.DONE) return;
      setSubmitting(false);
      var response = {};
      try { response = JSON.parse(xhr.responseText); } catch (error) {}
      if (xhr.status >= 200 && xhr.status < 300) {
        if (window.Toast) window.Toast.success('User updated successfully.');
        window.setTimeout(function () { window.location.assign(form.dataset.showUrl); }, 600);
        return;
      }
      if (xhr.status === 422 && response.errors) {
        var earliestStep = TOTAL_STEPS;
        var firstName = Object.keys(response.errors)[0];
        Object.keys(response.errors).forEach(function (name) {
          var targetStep = fieldSteps[name] || TOTAL_STEPS;
          earliestStep = Math.min(earliestStep, targetStep);
          setError(name, (response.errors[name] || [])[0] || 'Invalid value.');
        });
        goToStep(earliestStep);
        var firstInput = fieldInput(firstName);
        if (firstInput) window.setTimeout(function () { firstInput.focus(); }, 100);
        return;
      }
      if (window.Toast) window.Toast.error(xhr.status === 419 ? 'Session expired. Please reload the page.' : 'Could not save changes. Please try again.');
    };
    xhr.send(body);
  }

  function initAvatar() {
    var input = document.getElementById('avatar');
    var choose = document.getElementById('avatarChooseBtn');
    var remove = document.getElementById('avatarRemoveBtn');
    var preview = document.getElementById('avatarPreviewImg');
    var previewWrap = document.getElementById('avatarPreviewWrap');
    var removeInput = document.getElementById('remove_avatar');
    if (!input || !preview || !previewWrap) return;
    if (choose) choose.addEventListener('click', function () { input.click(); });
    input.addEventListener('change', function () {
      var file = input.files && input.files[0];
      if (!file) return;
      if (['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'].indexOf(file.type) === -1 || file.size > 2 * 1024 * 1024) {
        input.value = '';
        setError('avatar', 'Choose an image in an allowed format smaller than 2MB.');
        return;
      }
      clearError('avatar');
      removeInput.value = '0';
      var reader = new FileReader();
      reader.onload = function (event) { preview.src = event.target.result; previewWrap.classList.add('has-image'); };
      reader.readAsDataURL(file);
    });
    if (remove) remove.addEventListener('click', function () {
      input.value = '';
      removeInput.value = '1';
      preview.src = '';
      previewWrap.classList.remove('has-image');
    });
  }
  function initPasswordStrength() {
    var password = fieldInput('password');
    var fill = document.getElementById('pwStrengthFill');
    var label = document.getElementById('pwStrengthLabel');
    if (!password || !fill || !label) return;
    password.addEventListener('input', function () {
      var score = 0, passwordValue = password.value;
      if (passwordValue.length >= 8) score++; if (/[A-Z]/.test(passwordValue)) score++; if (/[0-9]/.test(passwordValue)) score++; if (/[^A-Za-z0-9]/.test(passwordValue)) score++;
      var level = !passwordValue ? '' : score <= 1 ? 'weak' : score <= 3 ? 'medium' : 'strong';
      fill.className = 'pw-strength-bar-fill' + (level ? ' ' + level : '');
      label.className = 'pw-strength-label' + (level ? ' ' + level : '');
      label.textContent = level ? level.charAt(0).toUpperCase() + level.slice(1) : '';
    });
  }

  form.querySelectorAll('.fl-input').forEach(function (input) {
    input.addEventListener('focus', function () { var box = input.closest('.fl-field'); if (box) box.classList.add('focused'); });
    input.addEventListener('blur', function () { var box = input.closest('.fl-field'); if (box) box.classList.remove('focused'); updateFloatingLabel(input); validateField(input.dataset.fieldName); });
    input.addEventListener('input', function () { updateFloatingLabel(input); clearError(input.dataset.fieldName); if (uniqueFields.indexOf(input.dataset.fieldName) !== -1) scheduleAvailability(input.dataset.fieldName); });
  });
  nextButton.addEventListener('click', function () { if (validateStep(currentStep)) goToStep(currentStep + 1); });
  backButton.addEventListener('click', function () { goToStep(currentStep - 1); });
  form.querySelectorAll('[data-edit-step]').forEach(function (button) { button.addEventListener('click', function () { goToStep(Number(button.dataset.editStep)); }); });
  document.getElementById('canLoginToggle').addEventListener('click', function () { var on = this.classList.toggle('on'); this.setAttribute('aria-pressed', on ? 'true' : 'false'); document.getElementById('can_login').value = on ? '1' : '0'; });
  form.querySelectorAll('.fl-pw-toggle').forEach(function (button) { button.addEventListener('click', function () { var input = document.getElementById(button.dataset.target); input.type = input.type === 'password' ? 'text' : 'password'; button.textContent = input.type === 'password' ? 'Show' : 'Hide'; }); });
  form.addEventListener('submit', function (event) { event.preventDefault(); submitWithApi(); });

  initAvatar();
  initPasswordStrength();
  updateAllFloatingLabels();
})();
