(function () {
  'use strict';

  var script = document.currentScript;
  var measurementId = script.dataset.measurementId;
  var hosts = script.dataset.analyticsHosts.split(',');
  var isPublicSite = hosts.indexOf(window.location.hostname) !== -1;
  var storageKey = 'sl-analytics-consent-v1';
  var consentLifetime = 180 * 24 * 60 * 60 * 1000;
  var started = false;
  var banner = document.querySelector('[data-cookie-banner]');
  var settings = document.querySelector('[data-cookie-settings]');

  if (!banner || !settings || !/^G-[A-Z0-9]+$/.test(measurementId)) return;

  function readChoice() {
    try {
      var saved = JSON.parse(window.localStorage.getItem(storageKey));
      if (saved && saved.expires > Date.now() &&
          (saved.choice === 'granted' || saved.choice === 'denied')) {
        return saved.choice;
      }
    } catch (error) {
      // Storage may be unavailable. Ask again next visit rather than assume consent.
    }
    return null;
  }

  function startAnalytics() {
    if (!isPublicSite || started) return;
    started = true;
    window['ga-disable-' + measurementId] = false;
    window.dataLayer = window.dataLayer || [];
    window.gtag = window.gtag || function () { window.dataLayer.push(arguments); };
    window.gtag('consent', 'default', {
      analytics_storage: 'denied',
      ad_storage: 'denied',
      ad_user_data: 'denied',
      ad_personalization: 'denied'
    });
    window.gtag('consent', 'update', { analytics_storage: 'granted' });
    window.gtag('js', new Date());
    window.gtag('config', measurementId, {
      allow_google_signals: false,
      allow_ad_personalization_signals: false
    });

    // Basic consent mode: never contact Google Analytics before permission.
    var tag = document.createElement('script');
    tag.async = true;
    tag.src = 'https://www.googletagmanager.com/gtag/js?id=' + measurementId;
    document.head.appendChild(tag);
  }

  function clearAnalyticsCookies() {
    var parts = window.location.hostname.split('.');
    var domains = [''];
    for (var i = 0; i < parts.length - 1; i++) {
      domains.push('; domain=' + parts.slice(i).join('.'));
      domains.push('; domain=.' + parts.slice(i).join('.'));
    }
    document.cookie.split(';').forEach(function (cookie) {
      var name = cookie.trim().split('=')[0];
      if (name !== '_ga' && name.indexOf('_ga_') !== 0) return;
      domains.forEach(function (domain) {
        document.cookie = name + '=; Max-Age=0; path=/' + domain;
      });
    });
  }

  function applyChoice(choice) {
    if (choice === 'granted') {
      startAnalytics();
    } else {
      window['ga-disable-' + measurementId] = true;
      if (started) window.gtag('consent', 'update', { analytics_storage: 'denied' });
      clearAnalyticsCookies();
      // Unload the running tag and its event listeners after withdrawal.
      if (started) window.location.reload();
    }
    banner.hidden = choice !== null;
  }

  settings.hidden = false;
  settings.addEventListener('click', function () {
    banner.hidden = false;
    banner.querySelector('[data-cookie-choice]').focus();
  });

  banner.querySelectorAll('[data-cookie-choice]').forEach(function (button) {
    button.addEventListener('click', function () {
      var choice = button.dataset.cookieChoice;
      try {
        window.localStorage.setItem(storageKey, JSON.stringify({
          choice: choice,
          expires: Date.now() + consentLifetime
        }));
      } catch (error) {
        // The choice still applies to this page if storage is blocked.
      }
      applyChoice(choice);
      settings.focus({ preventScroll: true });
    });
  });

  // Keep open tabs in sync, including when a visitor withdraws permission.
  window.addEventListener('storage', function (event) {
    if (event.key === storageKey || event.key === null) applyChoice(readChoice());
  });
  document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'visible' && started && readChoice() !== 'granted') {
      applyChoice(readChoice());
    }
  });

  applyChoice(readChoice());
})();
