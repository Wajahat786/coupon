/* DealHub front-end behaviours: theme toggle + copy-to-clipboard.
   No innerHTML is ever populated from user data (XSS-safe). */
(function () {
  'use strict';

  /* ---------- Theme toggle ---------- */
  function currentTheme() {
    return document.documentElement.getAttribute('data-theme') || 'dark';
  }

  document.addEventListener('click', function (ev) {
    var btn = ev.target.closest('#themeToggle');
    if (!btn) return;
    var next = currentTheme() === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    try { localStorage.setItem('dealhub-theme', next); } catch (e) {}
  });

  /* ---------- Copy to clipboard ---------- */
  document.addEventListener('click', function (ev) {
    var btn = ev.target.closest('[data-copy]');
    if (!btn) return;
    var text = btn.getAttribute('data-copy') || '';

    function done() {
      var original = btn.textContent;
      btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg> Copied!';
      btn.classList.add('copied');
      setTimeout(function () {
        btn.textContent = original;
        btn.classList.remove('copied');
      }, 1500);
    }

    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(text).then(done, fallback);
    } else {
      fallback();
    }

    function fallback() {
      var ta = document.createElement('textarea');
      ta.value = text;
      ta.style.position = 'fixed';
      ta.style.opacity = '0';
      document.body.appendChild(ta);
      ta.select();
      try { document.execCommand('copy'); done(); } catch (e) {}
      document.body.removeChild(ta);
    }
  });
})();
