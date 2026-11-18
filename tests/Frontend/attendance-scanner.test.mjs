import { test } from 'node:test';
import assert from 'node:assert/strict';
import { attendanceUrl, createScanner } from '../../resources/js/attendance-scanner.js';

const token = 'a'.repeat(32);
test('maps printed QR tokens to current origin without following foreign websites', () => {
    assert.equal(attendanceUrl(`http://localhost:8000/attendance/${token}`, 'https://attendance.test'), `https://attendance.test/attendance/${token}`);
    for (const url of ['javascript:alert(1)', 'https://evil.test/phishing', '/attendance/scan/qr', '/attendance/not-a-token']) {
        assert.equal(attendanceUrl(url, 'https://attendance.test'), null);
    }
});

function fixture({ secure = true, error, decoder, autoStart = false } = {}) {
    const nodes = {};
    for (const name of ['scanner-video', 'scanner-status', 'camera-panel', 'camera-start', 'camera-stop', 'copy-link', 'browser-notice']) {
        nodes[name] = { hidden: true, disabled: false, dataset: {}, events: {}, textContent: '',
            addEventListener(event, callback) { this.events[event] = callback; } };
    }
    Object.assign(nodes['scanner-video'], { play: async () => {}, readyState: 2, videoWidth: 640, videoHeight: 480 });
    let stopped = 0, requested = 0, frame, navigated;
    const stream = { getTracks: () => [{ stop() { stopped++; } }] };
    const browser = {
        isSecureContext: secure, navigator: { userAgent: 'Telegram', mediaDevices: { async getUserMedia() {
            requested++; if (error) throw { name: error }; return stream;
        } } },
        location: { origin: 'https://attendance.test', assign(url) { navigated = url; } },
        requestAnimationFrame(callback) { frame = callback; return 1; }, cancelAnimationFrame() {}, addEventListener() {},
    };
    globalThis.document = {
        createElement: () => ({ getContext: () => ({ drawImage() {}, getImageData: () => ({ data: [], width: 1, height: 1 }) }) }),
        addEventListener() {},
    };
    const scanner = createScanner({ dataset: { autoStart: String(autoStart) }, querySelector: (selector) => nodes[selector.slice(6, -1)] }, browser, decoder || (() => null));
    return { nodes, scanner, state: () => ({ stopped, requested, navigated }), frame: () => frame(200) };
}
test('HTTP shows actionable feedback without requesting camera or leaving black preview', async () => {
    const f = fixture({ secure: false }); await f.scanner.startCamera();
    assert.match(f.nodes['scanner-status'].textContent, /HTTPS/);
    assert.equal(f.nodes['camera-panel'].hidden, true);
    assert.equal(f.state().requested, 0);
});

test('scan page automatically opens camera and hides the extra start button', async () => {
    const f = fixture({ autoStart: true });
    await new Promise(resolve => setTimeout(resolve, 0));
    assert.equal(f.state().requested, 1);
    assert.equal(f.nodes['camera-panel'].hidden, false);
    assert.equal(f.nodes['camera-start'].hidden, true);
    f.scanner.release();
});

test('automatic camera denial exposes a manual retry and HTTP never auto starts', async () => {
    const f = fixture({ autoStart: true, error: 'NotAllowedError' });
    await new Promise(resolve => setTimeout(resolve, 0));
    assert.equal(f.nodes['camera-start'].hidden, false);
    assert.equal(f.nodes['camera-start'].disabled, false);
    assert.equal(f.state().requested, 1);
    const blocked = fixture({ autoStart: true, secure: false });
    assert.equal(blocked.state().requested, 0);
});
test('permission denial permits retry and hides camera panel', async () => {
    const f = fixture({ error: 'NotAllowedError' }); await f.scanner.startCamera();
    assert.match(f.nodes['scanner-status'].textContent, /permission denied/);
    assert.equal(f.nodes['camera-start'].disabled, false);
    assert.equal(f.nodes['camera-panel'].hidden, true);
});
test('successful decode releases camera and opens confirmation', async () => {
    const f = fixture({ decoder: () => ({ data: `http://old-host/attendance/${token}` }) });
    await f.scanner.startCamera(); assert.equal(f.nodes['camera-panel'].hidden, false);
    f.frame(); assert.equal(f.state().navigated, `https://attendance.test/attendance/${token}`);
    assert.equal(f.state().stopped, 1);
});
test('unrelated QR never navigates to an external page', async () => {
    const f = fixture({ decoder: () => ({ data: 'https://evil.test/phishing' }) });
    await f.scanner.startCamera(); f.frame();
    assert.equal(f.state().navigated, undefined);
    assert.match(f.nodes['scanner-status'].textContent, /not a shop attendance QR/);
    f.scanner.release(); assert.equal(f.state().stopped, 1);
});

