/* Theme bootstrapper — runs BEFORE paint (in <head>) to avoid FOUC.
   Priority: user choice (localStorage) > server default theme attr. */
(function () {
  'use strict';
  try {
    var saved = localStorage.getItem('dealhub-theme');
    if (saved === 'light' || saved === 'dark') {
      document.documentElement.setAttribute('data-theme', saved);
    }
  } catch (e) { /* storage disabled — keep server default */ }
})();
