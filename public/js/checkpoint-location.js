// Checkpoint setup can offer approximate readings for review; employee attendance
// still uses the independent, strict accuracy check on the server.
document.querySelectorAll('[data-checkpoint-gps]').forEach((fields) => {
    const button = fields.querySelector('[data-use-checkpoint-location]');
    const status = fields.querySelector('[data-checkpoint-gps-status]');
    const review = fields.querySelector('[data-checkpoint-gps-review]');
    const estimate = fields.querySelector('[data-checkpoint-gps-estimate]');
    const map = fields.querySelector('[data-checkpoint-gps-map]');
    const approximateButton = fields.querySelector('[data-use-approximate-location]');
    const latitude = fields.querySelector('[name=latitude]');
    const longitude = fields.querySelector('[name=longitude]');
    const originalLabel = button.textContent;
    let watchId = null;
    let deadline = null;
    let best = null;
    let running = false;

    const stop = () => {
        running = false;
        if (watchId !== null) navigator.geolocation.clearWatch(watchId);
        if (deadline !== null) clearTimeout(deadline);
        watchId = null;
        deadline = null;
        button.disabled = false;
        button.textContent = originalLabel;
    };
    const fill = (position, approximate = false) => {
        stop();
        latitude.value = position.coords.latitude.toFixed(7);
        longitude.value = position.coords.longitude.toFixed(7);
        review.hidden = true;
        status.textContent = `${approximate ? 'Approximate location' : 'Location'} filled in (accuracy ±${Math.round(position.coords.accuracy)} m). Review the coordinates, then save to apply.`;
    };
    const offer = () => {
        review.hidden = false;
        const accuracy = Math.round(best.coords.accuracy);
        estimate.textContent = `Best available reading: ±${accuracy} m. Desktop computers often provide an approximate location rather than GPS.`;
        map.href = `https://www.google.com/maps?q=${best.coords.latitude},${best.coords.longitude}`;
    };

    button.addEventListener('click', () => {
        stop();
        best = null;
        review.hidden = true;
        if (!window.isSecureContext || !navigator.geolocation) {
            status.textContent = 'Location requires HTTPS or localhost and a supported browser. You can also enter the shop coordinates manually.';
            return;
        }
        running = true;
        button.disabled = true;
        button.textContent = 'Finding location…';
        status.textContent = 'Allow location access. Waiting up to 20 seconds for an accurate reading…';
        deadline = setTimeout(() => {
            stop();
            if (best) {
                offer();
                status.textContent = `No precise reading was available. Best accuracy: ±${Math.round(best.coords.accuracy)} m. Review the approximate location below, retry on a phone at the shop, or enter coordinates manually.`;
            } else {
                status.textContent = 'No location was found within 20 seconds. Enable device location services and retry, or enter the shop coordinates manually.';
            }
        }, 20000);
        try {
            watchId = navigator.geolocation.watchPosition((position) => {
                if (!running) return;
                const { latitude: lat, longitude: lng, accuracy } = position.coords;
                if (![lat, lng, accuracy].every(Number.isFinite) || Math.abs(lat) > 90 || Math.abs(lng) > 180 || accuracy < 0) return;
                if (!best || accuracy < best.coords.accuracy) best = position;
                if (best.coords.accuracy <= 100) {
                    fill(best);
                } else {
                    offer();
                    status.textContent = `Location found, but accuracy is ±${Math.round(best.coords.accuracy)} m. Still looking for a better reading…`;
                }
            }, (error) => {
                if (!running) return;
                if (error.code === 1) {
                    stop();
                    review.hidden = true;
                    status.textContent = 'Location permission denied. Allow location for this site in browser settings, then retry.';
                } else {
                    // Keep the best reading while waiting for another update or the deadline.
                    status.textContent = best
                        ? 'Waiting for a better location. You can review the approximate reading below.'
                        : 'Location is not available yet. Enable device location services; still trying…';
                }
            }, { enableHighAccuracy: true, maximumAge: 0, timeout: 20000 });
        } catch {
            stop();
            status.textContent = 'Your browser could not start location access. Retry or enter the shop coordinates manually.';
        }
    });
    approximateButton.addEventListener('click', () => {
        if (best) fill(best, true);
    });
    [latitude, longitude].forEach((input) => input.addEventListener('input', () => {
        stop();
        best = null;
        review.hidden = true;
        status.textContent = 'Coordinates entered manually. Save to apply the shop location.';
    }));
    window.addEventListener('pagehide', stop);
});
