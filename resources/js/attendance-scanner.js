import jsQR from 'jsqr';

// Map printed QR tokens to this installation, even after changing the hostname.
// Never redirect employees to an arbitrary URL embedded in a QR code.
export function attendanceUrl(value, origin) {
    try {
        const url = new URL(value, origin);
        const match = url.pathname.match(/^\/attendance\/([a-zA-Z0-9]{32})\/?$/);
        return ['http:', 'https:'].includes(url.protocol) && match
            ? new URL(`/attendance/${match[1]}`, origin).href : null;
    } catch { return null; }
}

export function createScanner(root, browser = window, decode = jsQR) {
    const video = root.querySelector('[data-scanner-video]');
    const status = root.querySelector('[data-scanner-status]');
    const panel = root.querySelector('[data-camera-panel]');
    const start = root.querySelector('[data-camera-start]');
    const stop = root.querySelector('[data-camera-stop]');
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d', { willReadFrequently: true });
    let stream = null, frame = null, attempt = 0, lastScan = 0;
    const blocked = !browser.isSecureContext;
    const message = (text, error = false) => {
        status.textContent = text;
        status.dataset.error = String(error);
    };
    const release = () => {
        attempt++;
        if (frame !== null) browser.cancelAnimationFrame(frame);
        frame = null;
        stream?.getTracks().forEach((track) => track.stop());
        stream = null;
        video.srcObject = null;
        panel.hidden = true;
        stop.hidden = true;
        start.disabled = blocked;
        start.hidden = false;
        start.textContent = 'Open camera';
    };
    const follow = (code) => {
        if (!code?.data) return false;
        const url = attendanceUrl(code.data, browser.location.origin);
        if (!url) { message('This is not a shop attendance QR. Please scan the correct code.', true); return false; }
        release(); message('QR found. Checking your location...');
        browser.location.assign(url);
        return true;
    };
    const tick = (time) => {
        if (!stream) return;
        if (video.readyState >= 2 && video.videoWidth && time - lastScan >= 180) {
            lastScan = time;
            const ratio = Math.min(1, 900 / video.videoWidth);
            canvas.width = Math.round(video.videoWidth * ratio);
            canvas.height = Math.round(video.videoHeight * ratio);
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            const data = context.getImageData(0, 0, canvas.width, canvas.height);
            if (follow(decode(data.data, data.width, data.height, { inversionAttempts: 'attemptBoth' }))) return;
        }
        frame = browser.requestAnimationFrame(tick);
    };
    const startCamera = async () => {
        release();
        if (!browser.isSecureContext) { message('Camera and GPS are blocked on this HTTP address. Open the secure HTTPS attendance website in Safari or Chrome. A private IP over HTTP cannot enable these permissions.', true); return; }
        if (!browser.navigator.mediaDevices?.getUserMedia) { message('This browser cannot open the camera. Open this link in Safari or Chrome.', true); return; }
        const current = ++attempt;
        start.disabled = true;
        start.hidden = true;
        stop.hidden = false;
        message('Allow camera access when asked…');
        try {
            const camera = await browser.navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false });
            if (current !== attempt) { camera.getTracks().forEach((track) => track.stop()); return; }
            stream = camera; video.srcObject = stream; panel.hidden = false; stop.hidden = false;
            await video.play();
            if (current !== attempt) return;
            start.disabled = false; start.hidden = true;
            message('Place the QR inside the frame.');
            frame = browser.requestAnimationFrame(tick);
        } catch (error) {
            if (current !== attempt) return;
            release();
            const errors = {
                NotAllowedError: 'Camera permission denied. Allow camera access in browser settings, then try again. If this is Telegram, open the page in Safari or Chrome.',
                NotFoundError: 'No camera was found. Open this page on a phone with a camera.',
                NotReadableError: 'The camera is busy. Close other camera apps and try again.',
                SecurityError: 'Camera access is blocked. Open the secure HTTPS link in Safari or Chrome.',
            };
            message(errors[error.name] || 'The camera could not start. Check camera permissions and try again.', true);
        }
    };
    start.addEventListener('click', startCamera);
    stop.addEventListener('click', () => { release(); message('Camera stopped. You can start it again.'); });
    root.querySelector('[data-copy-link]').addEventListener('click', async () => {
        try { await browser.navigator.clipboard.writeText(browser.location.href); message('Link copied. Paste it into Safari or Chrome.'); }
        catch { message('Copy the page address from your browser menu and open it in Safari or Chrome.'); }
    });
    root.querySelector('[data-browser-notice]').hidden = blocked || !/Telegram|FBAN|FBAV|Instagram|Line\//i.test(browser.navigator.userAgent);
    if (blocked) {
        // Explain the connection issue before inviting a tap that cannot succeed.
        const help = root.querySelector('[data-device-help]');
        const actions = root.querySelector('[data-scanner-actions]');
        if (help) { help.hidden = false; status.hidden = true; }
        else message('Camera and GPS require HTTPS. Ask your manager for the secure attendance link.', true);
        if (actions) actions.hidden = true;
        start.disabled = true;
    } else {
        start.disabled = false;
    }
    if (root.dataset) root.dataset.scannerReady = 'true';
    browser.addEventListener('pagehide', release);
    document.addEventListener('visibilitychange', () => { if (document.hidden) release(); });
    if (!blocked && root.dataset?.autoStart === 'true') startCamera();
    return { startCamera, release };
}

if (typeof document !== 'undefined') document.querySelectorAll('[data-attendance-scanner]').forEach((root) => createScanner(root));
