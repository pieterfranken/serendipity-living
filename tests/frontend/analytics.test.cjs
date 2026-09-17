// Run with: node --test tests/frontend/analytics.test.cjs
const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../../themes/serendipity-living/assets/js/analytics.js'), 'utf8');
const key = 'sl-analytics-consent-v1';
const id = 'G-1CGVH22GJN';

function setup({ hostname = 'serendipityliving.nl', choice, expires, blockedStorage = false, rawStorage } = {}) {
  const state = { scripts: [], reloads: 0, cookieWrites: [], storage: rawStorage ?? null };
  if (choice) state.storage = JSON.stringify({ choice, expires: expires ?? Date.now() + 100000 });
  const handlers = {};
  function element(dataset = {}) {
    const listeners = {};
    return { dataset, hidden: true, addEventListener: (type, fn) => { listeners[type] = fn; },
      click: () => listeners.click(), focus: () => {}, listeners };
  }
  const accept = element({ cookieChoice: 'granted' });
  const decline = element({ cookieChoice: 'denied' });
  const settings = element();
  const banner = element();
  banner.querySelectorAll = () => [decline, accept];
  banner.querySelector = () => decline;
  const document = {
    currentScript: { dataset: { measurementId: id, analyticsHosts: 'serendipityliving.nl,www.serendipityliving.nl' } },
    querySelector: selector => selector === '[data-cookie-banner]' ? banner : settings,
    createElement: () => ({}),
    head: { appendChild: script => state.scripts.push(script) },
    addEventListener: (type, fn) => { handlers[type] = fn; },
    visibilityState: 'visible'
  };
  Object.defineProperty(document, 'cookie', {
    get: () => '_ga=123; _ga_1CGVH22GJN=456; october_session=keep',
    set: value => state.cookieWrites.push(value)
  });
  const window = {
    location: { hostname, reload: () => state.reloads++ },
    localStorage: {
      getItem: () => { if (blockedStorage) throw Error('blocked'); return state.storage; },
      setItem: (name, value) => { assert.equal(name, key); if (blockedStorage) throw Error('blocked'); state.storage = value; }
    },
    addEventListener: (type, fn) => { handlers[type] = fn; }
  };
  vm.runInNewContext(source, { window, document, Date });
  return { state, window, banner, settings, accept, decline, handlers };
}

test('first visit and rejection never load Google or initialize its queue', () => {
  const ctx = setup();
  assert.equal(ctx.banner.hidden, false);
  assert.equal(ctx.state.scripts.length, 0);
  assert.equal(ctx.window.dataLayer, undefined);
  ctx.decline.click();
  assert.equal(ctx.banner.hidden, true);
  assert.equal(JSON.parse(ctx.state.storage).choice, 'denied');
  assert.equal(ctx.state.scripts.length, 0);
  assert.equal(ctx.window.dataLayer, undefined);
});

test('acceptance loads the correct tag once and grants analytics only', () => {
  const ctx = setup();
  ctx.accept.click();
  ctx.accept.click();
  assert.equal(ctx.state.scripts.length, 1);
  assert.equal(ctx.state.scripts[0].src, 'https://www.googletagmanager.com/gtag/js?id=' + id);
  const calls = ctx.window.dataLayer.map(call => Array.from(call));
  assert.deepEqual(Array.from(calls[0].slice(0, 2)), ['consent', 'default']);
  assert.equal(calls[0][2].ad_storage, 'denied');
  assert.equal(calls[0][2].ad_user_data, 'denied');
  assert.equal(calls[0][2].ad_personalization, 'denied');
  assert.equal(calls[1][2].analytics_storage, 'granted');
  assert.equal(calls.filter(call => call[0] === 'config').length, 1);
  assert.equal(calls[3][1], id);
  assert.equal(calls[3][2].allow_google_signals, false);
  assert.equal(ctx.banner.hidden, true);
});

test('saved acceptance works on both production hosts; saved rejection stays blocked', () => {
  for (const hostname of ['serendipityliving.nl', 'www.serendipityliving.nl']) {
    const ctx = setup({ hostname, choice: 'granted' });
    assert.equal(ctx.state.scripts.length, 1);
    assert.equal(ctx.banner.hidden, true);
  }
  const denied = setup({ choice: 'denied' });
  assert.equal(denied.state.scripts.length, 0);
  assert.equal(denied.banner.hidden, true);
});

test('local, preview and lookalike domains never send events, even after acceptance', () => {
  for (const hostname of ['127.0.0.1', 'localhost', '::1', 'staging.serendipityliving.nl', 'serendipityliving.nl.example.com']) {
    const ctx = setup({ hostname });
    ctx.accept.click();
    assert.equal(ctx.state.scripts.length, 0, hostname);
    assert.equal(ctx.window.dataLayer, undefined, hostname);
  }
});

test('expired or malformed saved consent cannot activate analytics', () => {
  for (const options of [{ choice: 'granted', expires: 1 }, { rawStorage: '{bad json' }, { choice: 'anything' }]) {
    const ctx = setup(options);
    assert.equal(ctx.state.scripts.length, 0);
    assert.equal(ctx.banner.hidden, false);
  }
});

test('settings reopen; withdrawal disables collection, clears only GA cookies and unloads the tag', () => {
  const ctx = setup({ choice: 'granted' });
  ctx.settings.click();
  assert.equal(ctx.banner.hidden, false);
  ctx.decline.click();
  assert.equal(ctx.window['ga-disable-' + id], true);
  assert.equal(ctx.state.reloads, 1);
  assert.equal(JSON.parse(ctx.state.storage).choice, 'denied');
  assert.ok(ctx.state.cookieWrites.some(cookie => cookie.startsWith('_ga=;')));
  assert.ok(ctx.state.cookieWrites.some(cookie => cookie.startsWith('_ga_1CGVH22GJN=;')));
  assert.ok(ctx.state.cookieWrites.every(cookie => !cookie.includes('october_session')));
});

test('storage failure is safe and does not prevent a choice on the current page', () => {
  const ctx = setup({ blockedStorage: true });
  assert.equal(ctx.banner.hidden, false);
  assert.equal(ctx.state.scripts.length, 0);
  ctx.accept.click();
  assert.equal(ctx.state.scripts.length, 1);
});

test('withdrawal in another tab disables collection in an already open tab', () => {
  const ctx = setup({ choice: 'granted' });
  ctx.state.storage = JSON.stringify({ choice: 'denied', expires: Date.now() + 100000 });
  ctx.handlers.storage({ key });
  assert.equal(ctx.window['ga-disable-' + id], true);
  assert.equal(ctx.state.reloads, 1);
});
