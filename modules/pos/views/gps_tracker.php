<?php
// modules/pos/views/gps_tracker.php
// GPS Tracker widget - embedded in POS page for sellers
// This runs in the background, polling geolocation and sending to server
?>
<!-- GPS Tracker Script -->
<script>
(function() {
    'use strict';

    const GPS_CONFIG = {
        apiEndpoint: '<?php echo mc_base_path(); ?>/public/api/gps_track.php',
        pollInterval: 30000, // 30 seconds between pings
        highAccuracy: true,
        timeout: 15000,
        maximumAge: 10000,
        minAccuracy: 100, // meters - ignore if accuracy worse than this
        debug: false
    };

    let trackingInterval = null;
    let isTracking = false;
    let watchId = null;

    function log(msg) {
        if (GPS_CONFIG.debug) {
            console.log('[GPS Tracker]', msg);
        }
    }

    function sendLocation(position) {
        const { latitude, longitude, accuracy, altitude, speed, heading } = position.coords;
        const battery = getBatteryLevel();

        const data = {
            latitude: latitude,
            longitude: longitude,
            accuracy: accuracy || null,
            altitude: altitude || null,
            speed: speed || null,
            heading: heading || null,
            battery_level: battery
        };

        log('Sending location: ' + latitude.toFixed(6) + ', ' + longitude.toFixed(6));

        fetch(GPS_CONFIG.apiEndpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                log('Location sent OK. Tracking ID: ' + res.tracking_id);
            } else {
                log('Server error: ' + (res.error || 'Unknown'));
                if (res.code === 'NO_POS_SESSION') {
                    log('No active POS session. Stopping tracker.');
                    stopTracking();
                }
            }
        })
        .catch(err => {
            log('Network error: ' + err.message);
        });
    }

    function handlePositionError(error) {
        switch(error.code) {
            case error.PERMISSION_DENIED:
                log('GPS permission denied by user');
                stopTracking();
                break;
            case error.POSITION_UNAVAILABLE:
                log('GPS position unavailable');
                break;
            case error.TIMEOUT:
                log('GPS request timed out');
                break;
            default:
                log('Unknown GPS error: ' + error.message);
        }
    }

    function pollLocation() {
        if (!navigator.geolocation) {
            log('Geolocation not supported by this browser');
            stopTracking();
            return;
        }

        navigator.geolocation.getCurrentPosition(
            sendLocation,
            handlePositionError,
            {
                enableHighAccuracy: GPS_CONFIG.highAccuracy,
                timeout: GPS_CONFIG.timeout,
                maximumAge: GPS_CONFIG.maximumAge
            }
        );
    }

    function getBatteryLevel() {
        if ('getBattery' in navigator) {
            // Battery API is async, return null for sync calls
            // We'll enhance this in the future with async handling
            return null;
        }
        return null;
    }

    // Also try to get battery info asynchronously
    if ('getBattery' in navigator) {
        navigator.getBattery().then(function(battery) {
            log('Battery: ' + Math.round(battery.level * 100) + '%');
            // Store battery level for next ping
            window.__gpsBatteryLevel = Math.round(battery.level * 100);
        });
    }

    function startTracking() {
        if (isTracking) return;
        isTracking = true;
        log('GPS Tracking STARTED');

        // Send first location immediately
        pollLocation();

        // Then poll every interval
        trackingInterval = setInterval(pollLocation, GPS_CONFIG.pollInterval);

        // Show indicator
        showTrackingIndicator();
    }

    function stopTracking() {
        if (!isTracking) return;
        isTracking = false;
        log('GPS Tracking STOPPED');

        if (trackingInterval) {
            clearInterval(trackingInterval);
            trackingInterval = null;
        }

        // Notify server that tracking stopped
        fetch(GPS_CONFIG.apiEndpoint.replace('gps_track.php', '') + 'gps_stop.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({}),
            credentials: 'same-origin'
        }).catch(() => {});

        hideTrackingIndicator();
    }

    function showTrackingIndicator() {
        // Floating GPS indicator hidden as per user UI request
        hideTrackingIndicator();
        return;
    }

    function hideTrackingIndicator() {
        const indicator = document.getElementById('gps-tracking-indicator');
        if (indicator) {
            indicator.remove();
        }
    }

    // Auto-start tracking when the page loads (POS page)
    function autoStart() {
        // Check if we're on a POS page with an active session
        const posUrlPattern = /\/pos\/pos$/;
        const isPosPage = posUrlPattern.test(window.location.pathname);

        if (isPosPage || document.querySelector('.pos-app')) {
            // Check with server if there's an active session
            startTracking();
            log('Auto-started GPS tracking on POS page');
        }
    }

    // Expose API globally
    window.GPSTracker = {
        start: startTracking,
        stop: stopTracking,
        isTracking: function() { return isTracking; },
        config: GPS_CONFIG
    };

    // Auto-start with a small delay to ensure page is ready
    if (document.readyState === 'complete') {
        setTimeout(autoStart, 2000);
    } else {
        window.addEventListener('load', function() {
            setTimeout(autoStart, 2000);
        });
    }

})();
</script>
