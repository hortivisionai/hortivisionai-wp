/*
 * HortiVision — notify when results ready. (robust init)
 */
(function () {
  var DELAY_MS = 30000; // 30s. Set to 3000 to test quickly.

  function log() {
    var a = Array.prototype.slice.call(arguments);
    console.log.apply(console, ['[hv-notify]'].concat(a));
  }

  function resultsVisible() {
    var el = document.getElementById('hv-result');
    return el && !el.hidden;
  }

  function init() {
    var runBtn = document.getElementById('hv-run');
    if (!runBtn) { log('no #hv-run on this page'); return; }
    log('ready, watching #hv-run');

    runBtn.addEventListener('click', function () {
      log('upload clicked, starting timer');

      if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission().then(function (p) {
          log('permission after prompt:', p);
        });
      } else {
        log('permission currently:', ('Notification' in window) ? Notification.permission : 'no API');
      }

      var notified = false;
      var start = Date.now();

      function fire(reason) {
        if (notified) return;
        if (!('Notification' in window)) { log('no Notification API'); return; }
        if (Notification.permission !== 'granted') { log('not granted:', Notification.permission); return; }
        notified = true;
        log('FIRING (' + reason + ')');
        new Notification('HortiVision AI', {
          body: 'Your plant count is ready — open the dashboard to view it.'
        });
      }

      var poll = setInterval(function () {
        var elapsed = Date.now() - start;
        if (resultsVisible()) {
          if (elapsed >= DELAY_MS) {
            fire('results after delay');
          } else {
            log('results appeared in ' + Math.round(elapsed/1000) + 's, no notification');
          }
          clearInterval(poll);
        }
      }, 1000);

      setTimeout(function () { clearInterval(poll); }, 6 * 60 * 1000);
    });
  }

  // Run now if DOM is already parsed (footer scripts often load after DOMContentLoaded),
  // otherwise wait for it.
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();