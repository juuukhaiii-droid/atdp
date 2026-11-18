document.querySelectorAll('[data-location-attendance]').forEach((form) => {
    const button = form.querySelector('[data-location-submit]');
    const status = form.querySelector('[data-location-status]');
    const help = form.querySelector('[data-device-help]');
    const label = button.textContent.trim();
    const configured = form.dataset.locationConfigured === 'true';
    const supported = window.isSecureContext && !!navigator.geolocation;
    const autoStart = form.dataset.autoStart === 'true';
    let busy = false;
    let attempt = 0;
    const message = (text, state) => {
        status.textContent = text;
        status.dataset.state = state;
    };
    const preflight = () => {
        if (!configured) return false;
        if (!supported) {
            button.disabled = true;
            button.hidden = true;
            status.hidden = true;
            if (help) help.hidden = false;
            form.querySelector('[data-location-privacy]').hidden = true;
            const description = form.querySelector('[data-attendance-description]');
            if (description) description.textContent = 'ត្រូវការតំណ HTTPS ដើម្បីកត់ត្រាវត្តមាន។';
            return false;
        }
        button.disabled = false;
        button.hidden = false;
        return true;
    };
    const fail = (text) => {
        busy = false;
        button.disabled = false;
        button.hidden = false;
        button.textContent = 'សាក ' + label + ' ម្តងទៀត';
        form.setAttribute('aria-busy', 'false');
        message(text, 'error');
    };
    preflight();
    form.dataset.locationReady = 'true';
    const capture = (event) => {
        event?.preventDefault();
        if (busy || !preflight()) return;
        busy = true;
        const current = ++attempt;
        button.disabled = true;
        button.textContent = 'កំពុងយកទីតាំង…';
        form.setAttribute('aria-busy', 'true');
        message('សូមចុចអនុញ្ញាតឱ្យប្រើទីតាំង ហើយរង់ចាំបន្តិច…', 'busy');
        navigator.geolocation.getCurrentPosition((position) => {
            if (current !== attempt) return;
            const { latitude, longitude, accuracy } = position.coords;
            const select = form.querySelector('[name=attendance_point_id]');
            const radius = select?.selectedOptions?.[0]?.dataset.radius;
            const accuracyLimit = Math.min(100, Number(radius || form.dataset.locationAccuracyLimit || 100));
            if (![latitude, longitude, accuracy].every(Number.isFinite) || accuracy < 0 || accuracy > accuracyLimit) {
                fail('ទីតាំងមិនទាន់ច្បាស់។ សូមទៅជិតបង្អួច ឬខាងក្រៅ រួចសាកម្តងទៀត។');
                return;
            }
            if (!Number.isFinite(position.timestamp) || !Number.isFinite(new Date(position.timestamp).getTime())) {
                fail('ទូរស័ព្ទមិនអាចយកទីតាំងថ្មីបាន។ សូមសាកម្តងទៀត។');
                return;
            }
            const values = { latitude, longitude, location_accuracy: accuracy, location_captured_at: new Date(position.timestamp).toISOString() };
            Object.entries(values).forEach(([name, value]) => { form.elements.namedItem(name).value = value; });
            button.textContent = 'កំពុងរក្សាទុកវត្តមាន…';
            message('កំពុងពិនិត្យទីតាំង និងរក្សាទុកវត្តមាន…', 'busy');
            HTMLFormElement.prototype.submit.call(form);
        }, (error) => {
            if (current !== attempt) return;
            const messages = {
                1: 'ទីតាំងត្រូវបានបិទ។ សូមអនុញ្ញាតទីតាំងក្នុងការកំណត់កម្មវិធី រួចសាកម្តងទៀត។',
                2: 'រកមិនឃើញទីតាំង។ សូមបើកសេវាទីតាំង ហើយសាកនៅជិតបង្អួច។',
                3: 'ការយកទីតាំងយូរពេក។ សូមពិនិត្យការតភ្ជាប់ រួចសាកម្តងទៀត។',
            };
            fail(messages[error.code] || 'មិនអាចយកទីតាំងបាន។ សូមសាកម្តងទៀត។');
        }, { enableHighAccuracy: true, maximumAge: 0, timeout: 20000 });
    };
    form.addEventListener('submit', capture);
    if (form.dataset.scanId && window.history && window.location) {
        // Refresh/back must reuse this scan rather than toggle to another action.
        const url = new URL(window.location.href);
        url.searchParams.set('scan', form.dataset.scanId);
        window.history.replaceState(null, '', url.href);
    }
    if (autoStart && configured && supported) {
        capture();
        button.hidden = true;
    }
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            attempt++;
            busy = false;
            button.disabled = !configured;
            button.textContent = label;
            form.setAttribute('aria-busy', 'false');
            if (preflight()) message('ចុច ' + label + ' រួចអនុញ្ញាតទីតាំង។', 'ready');
        }
    });
    window.addEventListener('pagehide', () => { attempt++; busy = false; });
});
