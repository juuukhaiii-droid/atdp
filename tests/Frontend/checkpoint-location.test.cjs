const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

const source = fs.readFileSync(path.join(__dirname, '../../public/js/checkpoint-location.js'), 'utf8');
function setup(secure = true) {
    const elements = {};
    const selectors = [
        'data-use-checkpoint-location', 'data-checkpoint-gps-status', 'data-checkpoint-gps-review',
        'data-checkpoint-gps-estimate', 'data-checkpoint-gps-map', 'data-use-approximate-location',
        'name=latitude', 'name=longitude',
    ];
    selectors.forEach((key) => {
        elements[key] = { textContent: '', value: '', hidden: true, disabled: false, events: {},
            addEventListener(event, callback) { this.events[event] = callback; } };
    });
    elements['data-use-checkpoint-location'].textContent = 'Use my current location';
    let success, error, deadline;
    const cleared = [];
    const geo = {
        watchPosition(ok, fail) { success = ok; error = fail; return 7; },
        clearWatch(id) { cleared.push(id); },
    };
    vm.runInNewContext(source, {
        document: { querySelectorAll: () => [{ querySelector: (selector) => elements[selector.slice(1, -1)] }] },
        window: { isSecureContext: secure, addEventListener() {} }, navigator: { geolocation: geo },
        setTimeout(callback) { deadline = callback; return 1; }, clearTimeout() {},
    });
    return {
        elements, cleared,
        start: () => elements['data-use-checkpoint-location'].events.click(),
        position: (accuracy) => success({ coords: { latitude: 11.594184, longitude: 104.810963, accuracy } }),
        fail: (code) => error({ code }), expire: () => deadline(),
    };
}

test('coarse reading is offered for review without replacing existing coordinates', () => {
    const h = setup(); h.elements['name=latitude'].value = '10'; h.start(); h.position(2500);
    assert.equal(h.elements['name=latitude'].value, '10');
    assert.equal(h.elements['data-checkpoint-gps-review'].hidden, false);
    assert.match(h.elements['data-checkpoint-gps-estimate'].textContent, /2500 m/);
    assert.match(h.elements['data-checkpoint-gps-map'].href, /11\.594184,104\.810963/);
    h.elements['data-use-approximate-location'].events.click();
    assert.equal(h.elements['name=latitude'].value, '11.5941840');
    assert.match(h.elements['data-checkpoint-gps-status'].textContent, /Approximate/);
    assert.deepEqual(h.cleared, [7]);
});
test('better reading fills coordinates and releases the watcher', () => {
    const h = setup(); h.start(); h.position(2500); h.position(15);
    assert.equal(h.elements['name=longitude'].value, '104.8109630');
    assert.equal(h.elements['data-use-checkpoint-location'].disabled, false);
    assert.equal(h.elements['data-checkpoint-gps-review'].hidden, true);
    assert.deepEqual(h.cleared, [7]);
});
test('deadline keeps best estimate available and enables retry', () => {
    const h = setup(); h.start(); h.position(600); h.position(1800); h.expire();
    assert.match(h.elements['data-checkpoint-gps-status'].textContent, /600 m/);
    assert.equal(h.elements['name=latitude'].value, '');
    assert.equal(h.elements['data-use-checkpoint-location'].disabled, false);
});
test('permission denial gives actionable feedback and enables retry', () => {
    const h = setup(); h.start(); h.fail(1);
    assert.match(h.elements['data-checkpoint-gps-status'].textContent, /permission denied/);
    assert.equal(h.elements['data-use-checkpoint-location'].disabled, false);
    assert.deepEqual(h.cleared, [7]);
});
test('manual edits are preserved when a late reading arrives', () => {
    const h = setup(); h.start(); h.elements['name=latitude'].value = '12';
    h.elements['name=latitude'].events.input(); h.position(5);
    assert.equal(h.elements['name=latitude'].value, '12');
    assert.equal(h.elements['data-use-checkpoint-location'].disabled, false);
});
test('insecure context explains HTTPS without disabling the button', () => {
    const h = setup(false); h.start();
    assert.match(h.elements['data-checkpoint-gps-status'].textContent, /HTTPS/);
    assert.equal(h.elements['data-use-checkpoint-location'].disabled, false);
});
