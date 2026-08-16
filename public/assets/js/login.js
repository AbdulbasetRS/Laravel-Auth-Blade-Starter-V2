document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.pw-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = document.getElementById(btn.getAttribute('data-toggle-target'));
      if (!input) return;
      var isPw = input.type === 'password';
      input.type = isPw ? 'text' : 'password';
      btn.textContent = isPw ? btn.getAttribute('data-hide-label') : btn.getAttribute('data-show-label');
    });
  });

  var form = document.getElementById('loginForm');
  var btn = document.getElementById('loginBtn');
  if (form && btn) {
    form.addEventListener('submit', function () {
      btn.classList.add('loading');
      btn.disabled = true;
    });
  }
});
