const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../../public/js/attendance-location.js'), 'utf8');

function fixture({ secure = true, configured = true, accuracyLimit = 100, autoStart = false, scanId = '' } = {}) {
    const nodes = {
        '[data-location-submit]': { textContent: 'Check In', disabled: !configured, hidden: false },
        '[data-location-status]': { textContent: '', dataset: {}, hidden: false },
        '[data-device-help]': { hidden: true },
        '[data-location-privacy]': { hidden: false },
    };
    const values = Object.fromEntries(['latitude', 'longitude', 'location_accuracy', 'location_captured_at'].map((name) => [name, { value: '' }]));
    const form = {
        dataset: { locationConfigured: String(configured), locationAccuracyLimit: String(accuracyLimit), autoStart: String(autoStart), scanId },
        events: {}, attributes: {}, elements: { namedItem: (name) => values[name] },
        querySelector: (selector) => nodes[selector],
        addEventListener(event, callback) { this.events[event] = callback; },
        setAttribute(name, value) { this.attributes[name] = value; },
    };
    let success, fail, requests = 0, submissions = 0;
    const window = { isSecureContext: secure, events: {}, addEventListener(event, callback) { this.events[event] = callback; } };
    window.location = { href: 'https://attendance.test/attendance/' + 'a'.repeat(32) };
    window.history = { replaceState(data, title, url) { window.location.href = url; } };
    vm.runInNewContext(source, {
        window, URL, document: { querySelectorAll: () => [form] },
        navigator: { geolocation: { getCurrentPosition(ok, error) { requests++; success = ok; fail = error; } } },
        HTMLFormElement: { prototype: { submit() { submissions++; } } },
    });
    return {
        nodes, values, form, window,
        submit: () => form.events.submit({ preventDefault() {} }),
        position: (accuracy = 10, timestamp = Date.now()) => success({ coords: { latitude: 11.594184, longitude: 104.810963, accuracy }, timestamp }),
        fail: (code) => fail({ code }), state: () => ({ requests, submissions }),
    };
}

test('blocked HTTP is explained before a tap and never requests location', () => {
    const f = fixture({ secure: false });
    assert.equal(f.nodes['[data-location-submit]'].hidden, true);
    assert.equal(f.nodes['[data-device-help]'].hidden, false);
    f.submit(); assert.deepEqual(f.state(), { requests: 0, submissions: 0 });
});
test('one tap captures fresh coordinates and double taps do not submit twice', () => {
    const f = fixture(); f.submit(); f.submit();
    assert.equal(f.state().requests, 1);
    f.position(); assert.equal(f.state().submissions, 1);
    assert.equal(f.values.latitude.value, 11.594184);
    assert.ok(f.values.location_captured_at.value);
    assert.equal(f.nodes['[data-location-submit]'].textContent, 'កំពុងរក្សាទុកវត្តមាន…');
});
test('permission denial allows an explicit retry', () => {
    const f = fixture(); f.submit(); f.fail(1);
    assert.match(f.nodes['[data-location-status]'].textContent, /ទីតាំងត្រូវបានបិទ/);
    assert.equal(f.nodes['[data-location-submit]'].disabled, false);
    f.submit(); f.position(); assert.equal(f.state().submissions, 1);
});
test('low accuracy cannot bypass the smaller checkpoint radius', () => {
    const f = fixture({ accuracyLimit: 25 }); f.submit(); f.position(50);
    assert.equal(f.state().submissions, 0);
    assert.equal(f.nodes['[data-location-status]'].dataset.state, 'error');
});
test('an invalid GPS date exposes retry instead of leaving saving stuck', () => {
    const f = fixture({ autoStart: true });
    f.position(10, 1e20);
    assert.equal(f.state().submissions, 0);
    assert.equal(f.nodes['[data-location-submit]'].hidden, false);
    assert.equal(f.nodes['[data-location-submit]'].disabled, false);
    assert.equal(f.nodes['[data-location-status]'].dataset.state, 'error');
});
test('unconfigured checkpoints stay disabled after restoring the page', () => {
    const f = fixture({ configured: false });
    f.window.events.pageshow({ persisted: true }); f.submit();
    assert.equal(f.nodes['[data-location-submit]'].disabled, true);
    assert.equal(f.state().requests, 0);
});
test('a restored page ignores a late callback from the old capture', () => {
    const f = fixture(); f.submit(); f.window.events.pageshow({ persisted: true }); f.position();
    assert.equal(f.state().submissions, 0);
    assert.equal(f.nodes['[data-location-submit]'].disabled, false);
});

test('QR flow automatically captures GPS and submits without a confirm tap', () => {
    const f = fixture({ autoStart: true, scanId: '94a9cf25-ec30-4185-b7a6-bb1f429db546' });
    assert.equal(f.state().requests, 1);
    assert.equal(f.nodes['[data-location-submit]'].hidden, true);
    assert.match(f.window.location.href, /scan=94a9cf25-ec30-4185-b7a6-bb1f429db546/);
    f.position();
    assert.equal(f.state().submissions, 1);
});

test('automatic capture failure exposes retry rather than repeatedly requesting GPS', () => {
    const f = fixture({ autoStart: true }); f.fail(1);
    assert.equal(f.state().requests, 1);
    assert.equal(f.nodes['[data-location-submit]'].hidden, false);
    assert.equal(f.nodes['[data-location-submit]'].disabled, false);
});

test('automatic flow never captures on HTTP or automatically repeats after back navigation', () => {
    const blocked = fixture({ autoStart: true, secure: false });
    assert.equal(blocked.state().requests, 0);
    const f = fixture({ autoStart: true });
    f.window.events.pageshow({ persisted: true }); f.position();
    assert.equal(f.state().requests, 1);
    assert.equal(f.state().submissions, 0);
});
