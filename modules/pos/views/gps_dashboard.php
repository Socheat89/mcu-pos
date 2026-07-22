<?php
// modules/pos/views/gps_dashboard.php
// GPS Tracking Dashboard - Real-time map for store owners
// Variables expected: $activeSessions, $recentSessions, $telegramConfig, $tenantName

$urlPrefix = mc_base_path();
$tenantSlug = Tenant::getCurrent()['subdomain'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('gps_tracking'); ?> - <?php echo htmlspecialchars($tenantName); ?></title>
    <link href="<?php echo $urlPrefix; ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Battambang:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <style>
        body, h1, h2, h3, h4, h5, h6, p, span, a, button, input, select, textarea {
            font-family: 'Battambang', 'Inter', sans-serif !important;
        }
        #gps-map {
            height: 550px;
            border-radius: 20px;
            border: 2px solid var(--pos-border);
            z-index: 1;
        }
        .gps-stat-card {
            background: var(--pos-card);
            border: 1.5px solid var(--pos-border);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
        }
        .gps-stat-card:hover {
            border-color: var(--pos-primary);
            box-shadow: 0 8px 25px rgba(6, 182, 212, 0.1);
        }
        .gps-stat-value {
            font-size: 32px;
            font-weight: 900;
            color: var(--pos-primary);
        }
        .gps-stat-label {
            font-size: 13px;
            font-weight: 700;
            color: var(--pos-text-muted);
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .tracker-card {
            background: var(--pos-card);
            border: 1.5px solid var(--pos-border);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 12px;
            transition: all 0.3s;
            cursor: pointer;
        }
        .tracker-card:hover {
            border-color: var(--pos-primary);
            box-shadow: 0 8px 25px rgba(6, 182, 212, 0.1);
        }
        .tracker-card.active {
            border-color: #10b981;
            background: rgba(16, 185, 129, 0.03);
        }
        .tracker-card.stopped {
            opacity: 0.6;
        }
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }
        .status-dot.active { background: #10b981; box-shadow: 0 0 10px rgba(16,185,129,0.5); }
        .status-dot.stopped { background: #6b7280; }

        /* Map Legend */
        .map-legend {
            background: rgba(15, 23, 42, 0.95);
            border: 1px solid var(--pos-border);
            border-radius: 12px;
            padding: 12px 16px;
            color: #e2e8f0;
            font-size: 12px;
            font-weight: 600;
            backdrop-filter: blur(10px);
        }
        .map-legend i { margin-right: 6px; }

        /* Telegram Setup Section */
        .telegram-setup-card {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border: 1.5px solid #2d3748;
            border-radius: 20px;
            padding: 32px;
            color: #e2e8f0;
        }
        .telegram-setup-card .btn-telegram {
            background: #0088cc;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }
        .telegram-setup-card .btn-telegram:hover {
            background: #006699;
        }

        /* Leaflet dark mode tweaks */
        .leaflet-container {
            background: #1a1a2e;
        }

        /* Settings tabs */
        .gps-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            padding: 6px;
            background: #eaecef;
            border-radius: 18px;
            width: fit-content;
            border: 1px solid var(--pos-border);
        }
        .gps-tab-link {
            padding: 12px 24px;
            border-radius: 14px;
            font-weight: 800;
            cursor: pointer;
            color: var(--pos-text-muted);
            transition: all 0.25s;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .gps-tab-link:hover { color: var(--pos-text); }
        .gps-tab-link.active {
            background: rgba(var(--pos-primary-rgb), 0.15);
            color: var(--pos-primary);
            border: 1px solid rgba(var(--pos-primary-rgb), 0.25);
            box-shadow: var(--pos-shadow-sm);
        }
        .tab-content { display: none; animation: fadeIn 0.4s ease-out; }
        .tab-content.active { display: block; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'gps'; include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
            <div class="pos-title">
                <h1><i class="fas fa-satellite" style="color: var(--pos-primary);"></i> <?php echo __('gps_tracking'); ?></h1>
                <p><?php echo __('gps_tracking_desc'); ?></p>
            </div>
            <div style="display: flex; gap: 12px;">
                <span id="liveIndicator" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); border-radius: 14px; font-weight: 700; font-size: 13px; color: #10b981;">
                    <span class="status-dot active"></span> <?php echo __('live'); ?>
                </span>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="gps-tabs">
            <a class="gps-tab-link active" onclick="switchTab('live-map')">
                <i class="fas fa-map-marked-alt"></i> <?php echo __('live_map'); ?>
            </a>
            <a class="gps-tab-link" onclick="switchTab('history')">
                <i class="fas fa-history"></i> <?php echo __('tracking_history'); ?>
            </a>
        </div>

        <!-- Tab: Live Map -->
        <div id="tab-live-map" class="tab-content active">
            <!-- Stats Row -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px;">
                <div class="gps-stat-card">
                    <div class="gps-stat-value" id="stat-active"><?php echo count(array_filter($activeSessions ?? [], fn($s) => ($s['status'] ?? '') === 'active')); ?></div>
                    <div class="gps-stat-label"><i class="fas fa-user-check"></i> <?php echo __('active_trackers'); ?></div>
                </div>
                <div class="gps-stat-card">
                    <div class="gps-stat-value" id="stat-points">-</div>
                    <div class="gps-stat-label"><i class="fas fa-map-pin"></i> <?php echo __('gps_points_today'); ?></div>
                </div>
                <div class="gps-stat-card">
                    <div class="gps-stat-value" id="stat-sessions">-</div>
                    <div class="gps-stat-label"><i class="fas fa-play-circle"></i> <?php echo __('sessions_today'); ?></div>
                </div>
            </div>

            <!-- Map -->
            <div id="gps-map"></div>

            <!-- Active Trackers List -->
            <div style="margin-top: 24px;">
                <h3 style="font-size: 16px; font-weight: 800; color: var(--pos-text); margin-bottom: 16px;">
                    <i class="fas fa-list" style="color: var(--pos-primary);"></i> <?php echo __('active_trackers'); ?>
                </h3>
                <div id="activeTrackersList" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 12px;">
                    <?php if (empty($activeSessions)): ?>
                        <div style="grid-column: 1/-1; text-align: center; padding: 60px; color: var(--pos-text-muted);">
                            <i class="fas fa-satellite-dish" style="font-size: 48px; margin-bottom: 16px; display: block; opacity: 0.3;"></i>
                            <h3 style="font-weight: 800;"><?php echo __('no_active_trackers'); ?></h3>
                            <p><?php echo __('no_active_trackers_desc'); ?></p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($activeSessions as $s): ?>
                        <div class="tracker-card <?php echo ($s['status'] ?? '') === 'active' ? 'active' : 'stopped'; ?>"
                             onclick="focusTracker(<?php echo $s['id']; ?>, <?php echo ($s['last_location']['latitude'] ?? 0); ?>, <?php echo ($s['last_location']['longitude'] ?? 0); ?>)">
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                                <div style="width: 42px; height: 42px; border-radius: 12px; background: var(--pos-primary-light); display: grid; place-items: center; font-weight: 900; font-size: 16px; color: var(--pos-primary);">
                                    <?php echo strtoupper(substr(htmlspecialchars($s['username'] ?? '?'), 0, 1)); ?>
                                </div>
                                <div style="flex:1;">
                                    <div style="font-weight: 800; font-size: 14px; color: var(--pos-text);"><?php echo htmlspecialchars($s['username'] ?? 'N/A'); ?></div>
                                    <div style="font-size: 12px; color: var(--pos-text-muted);">
                                        <?php if (!empty($s['store_name'])): ?>
                                            <i class="fas fa-store-alt"></i> <?php echo htmlspecialchars($s['store_name']); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div>
                                    <span class="status-dot <?php echo ($s['status'] ?? '') === 'active' ? 'active' : 'stopped'; ?>"></span>
                                    <span style="font-size: 11px; font-weight: 700; color: var(--pos-text-muted);"><?php echo ($s['status'] ?? '') === 'active' ? __('active') : __('stopped'); ?></span>
                                </div>
                            </div>
                            <?php if (!empty($s['last_location'])): ?>
                            <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--pos-text-muted);">
                                <span><i class="fas fa-map-pin"></i> <?php echo number_format($s['last_location']['latitude'], 6); ?>, <?php echo number_format($s['last_location']['longitude'], 6); ?></span>
                                <span><i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($s['last_location']['recorded_at'] ?? '')); ?></span>
                            </div>
                            <?php if (!empty($s['last_location']['battery_level'])): ?>
                            <div style="margin-top: 6px; height: 4px; background: #374151; border-radius: 2px;">
                                <div style="width: <?php echo $s['last_location']['battery_level']; ?>%; height: 100%; background: <?php echo $s['last_location']['battery_level'] > 20 ? '#10b981' : '#ef4444'; ?>; border-radius: 2px;"></div>
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tab: History -->
        <div id="tab-history" class="tab-content">
            <div style="display: grid; gap: 12px;">
                <?php if (empty($recentSessions)): ?>
                    <div style="text-align: center; padding: 80px; color: var(--pos-text-muted);">
                        <i class="fas fa-history" style="font-size: 48px; margin-bottom: 16px; display: block; opacity: 0.3;"></i>
                        <h3 style="font-weight: 800;"><?php echo __('no_tracking_history'); ?></h3>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentSessions as $rs): ?>
                    <div class="tracker-card <?php echo ($rs['status'] ?? '') === 'active' ? 'active' : 'stopped'; ?>"
                         style="display: flex; justify-content: space-between; align-items: center; cursor: default;">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="width: 42px; height: 42px; border-radius: 12px; background: var(--pos-primary-light); display: grid; place-items: center; font-weight: 900; color: var(--pos-primary);">
                                <?php echo strtoupper(substr(htmlspecialchars($rs['username'] ?? '?'), 0, 1)); ?>
                            </div>
                            <div>
                                <div style="font-weight: 800; color: var(--pos-text);"><?php echo htmlspecialchars($rs['username'] ?? 'N/A'); ?></div>
                                <div style="font-size: 12px; color: var(--pos-text-muted);">
                                    <?php if (!empty($rs['store_name'])): ?>
                                        <i class="fas fa-store-alt"></i> <?php echo htmlspecialchars($rs['store_name']); ?> &bull;
                                    <?php endif; ?>
                                    <?php echo date('M d, Y H:i', strtotime($rs['started_at'] ?? '')); ?>
                                    <?php if (!empty($rs['ended_at'])): ?>
                                        → <?php echo date('H:i', strtotime($rs['ended_at'])); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 11px; color: var(--pos-text-muted);">
                                <i class="fas fa-map-pin"></i> <?php echo $rs['location_count'] ?? 0; ?> points
                            </div>
                            <span class="status-dot <?php echo ($rs['status'] ?? '') === 'active' ? 'active' : 'stopped'; ?>"></span>
                            <span style="font-size: 11px; font-weight: 700;"><?php echo ($rs['status'] ?? '') === 'active' ? __('active') : __('stopped'); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <?php include __DIR__ . '/partials/footer.php'; ?>

    <!-- Leaflet Map -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
    // ===== Map Setup =====
    let map;
    let markers = {};
    let markerLayer;
    let livePollInterval;

    function initMap() {
        map = L.map('gps-map', {
            center: [11.5564, 104.9282], // Phnom Penh center
            zoom: 13,
            zoomControl: true
        });

        // Dark tile layer
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 19
        }).addTo(map);

        // Legend
        const legend = L.control({position: 'bottomright'});
        legend.onAdd = function() {
            const div = L.DomUtil.create('div', 'map-legend');
            div.innerHTML = '<i class="fas fa-circle" style="color:#10b981;"></i> Active Tracker &nbsp; ' +
                           '<i class="fas fa-circle" style="color:#f59e0b;"></i> Last Seen > 5min';
            return div;
        };
        legend.addTo(map);

        // Initial load
        loadLiveLocations();
        loadStats();

        // Poll every 15 seconds
        livePollInterval = setInterval(loadLiveLocations, 15000);

        // Also reload stats every 60 seconds
        setInterval(loadStats, 60000);
    }

    function loadLiveLocations() {
        fetch('<?php echo $urlPrefix; ?>/public/api/gps_dashboard.php?action=live', {
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.sessions) return;

            // Clear old markers
            if (markerLayer) map.removeLayer(markerLayer);
            markerLayer = L.layerGroup().addTo(map);

            const bounds = [];
            const now = new Date();

            data.sessions.forEach(session => {
                const loc = session.last_location;
                if (!loc) return;

                const lat = parseFloat(loc.latitude);
                const lng = parseFloat(loc.longitude);
                bounds.push([lat, lng]);

                const recordedTime = new Date(loc.recorded_at.replace(' ', 'T'));
                const minutesAgo = Math.round((now - recordedTime) / 60000);
                const isRecent = minutesAgo < 5;
                const color = isRecent ? '#10b981' : '#f59e0b';

                // Custom icon
                const icon = L.divIcon({
                    className: 'custom-marker',
                    html: `<div style="
                        width: 32px; height: 32px;
                        background: ${color};
                        border: 3px solid white;
                        border-radius: 50% 50% 50% 0;
                        transform: rotate(-45deg);
                        box-shadow: 0 4px 15px ${color}80;
                    "></div>
                    <div style="
                        position: relative;
                        top: -8px;
                        left: 20px;
                        background: rgba(15,23,42,0.95);
                        color: white;
                        padding: 2px 8px;
                        border-radius: 6px;
                        font-size: 10px;
                        font-weight: 700;
                        white-space: nowrap;
                    ">${escapeHtml(session.username)} ${isRecent ? '🟢' : '🟡'}</div>`,
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });

                const marker = L.marker([lat, lng], { icon: icon }).addTo(markerLayer);

                // Popup
                let popupHtml = `<strong>${escapeHtml(session.username)}</strong><br>`;
                if (session.store_name) popupHtml += `🏪 ${escapeHtml(session.store_name)}<br>`;
                popupHtml += `📍 ${lat.toFixed(6)}, ${lng.toFixed(6)}<br>`;
                popupHtml += `🕐 ${loc.recorded_at}<br>`;
                if (loc.battery_level) popupHtml += `🔋 ${loc.battery_level}%<br>`;
                if (loc.speed) popupHtml += `🚀 ${loc.speed} m/s<br>`;
                popupHtml += `<a href="https://maps.google.com/?q=${lat},${lng}" target="_blank">📍 View on Google Maps</a>`;
                marker.bindPopup(popupHtml);

                markers[session.id] = marker;
            });

            // Fit bounds
            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
            }

            // Update active count
            document.getElementById('stat-active').textContent = data.sessions.length;

            // Update tracker cards
            updateTrackerCards(data.sessions);
        })
        .catch(err => console.error('GPS load error:', err));
    }

    function updateTrackerCards(sessions) {
        const container = document.getElementById('activeTrackersList');
        if (!container) return;

        if (sessions.length === 0) {
            container.innerHTML = `
                <div style="grid-column:1/-1;text-align:center;padding:60px;color:var(--pos-text-muted);">
                    <i class="fas fa-satellite-dish" style="font-size:48px;margin-bottom:16px;display:block;opacity:0.3;"></i>
                    <h3 style="font-weight:800;"><?php echo __('no_active_trackers'); ?></h3>
                    <p><?php echo __('no_active_trackers_desc'); ?></p>
                </div>`;
            return;
        }

        const now = new Date();
        container.innerHTML = sessions.map(s => {
            const loc = s.last_location;
            const recordedTime = loc ? new Date(loc.recorded_at.replace(' ', 'T')) : null;
            const minutesAgo = recordedTime ? Math.round((now - recordedTime) / 60000) : null;
            const isRecent = minutesAgo !== null && minutesAgo < 5;

            return `
            <div class="tracker-card active" onclick="focusTracker(${s.id}, ${loc ? loc.latitude : 0}, ${loc ? loc.longitude : 0})">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                    <div style="width:42px;height:42px;border-radius:12px;background:var(--pos-primary-light);display:grid;place-items:center;font-weight:900;font-size:16px;color:var(--pos-primary);">
                        ${escapeHtml(s.username || '?').charAt(0).toUpperCase()}
                    </div>
                    <div style="flex:1;">
                        <div style="font-weight:800;font-size:14px;color:var(--pos-text);">${escapeHtml(s.username || 'N/A')}</div>
                        <div style="font-size:12px;color:var(--pos-text-muted);">
                            ${s.store_name ? '<i class="fas fa-store-alt"></i> ' + escapeHtml(s.store_name) : ''}
                        </div>
                    </div>
                    <div>
                        <span class="status-dot ${isRecent ? 'active' : ''}" style="background:${isRecent ? '#10b981' : '#f59e0b'};"></span>
                        <span style="font-size:11px;font-weight:700;color:var(--pos-text-muted);">${isRecent ? 'Live' : minutesAgo + 'm ago'}</span>
                    </div>
                </div>
                ${loc ? `
                <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--pos-text-muted);">
                    <span><i class="fas fa-map-pin"></i> ${parseFloat(loc.latitude).toFixed(6)}, ${parseFloat(loc.longitude).toFixed(6)}</span>
                    <span><i class="fas fa-clock"></i> ${loc.recorded_at ? loc.recorded_at.substring(11,16) : ''}</span>
                </div>
                ${loc.battery_level ? `
                <div style="margin-top:6px;height:4px;background:#374151;border-radius:2px;">
                    <div style="width:${loc.battery_level}%;height:100%;background:${loc.battery_level > 20 ? '#10b981' : '#ef4444'};border-radius:2px;"></div>
                </div>` : ''}
                ` : ''}
            </div>`;
        }).join('');
    }

    function focusTracker(id, lat, lng) {
        if (map && lat && lng) {
            map.setView([lat, lng], 16, { animate: true });
            const marker = markers[id];
            if (marker) marker.openPopup();
        }
    }

    function loadStats() {
        fetch('<?php echo $urlPrefix; ?>/public/api/gps_dashboard.php?action=stats', {
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.stats) {
                document.getElementById('stat-points').textContent = data.stats.today_points || 0;
                document.getElementById('stat-sessions').textContent = data.stats.today_sessions || 0;
            }
        })
        .catch(() => {});
    }

    // ===== Telegram Config =====

    // Claim setup code (the easy way — no Chat IDs!)
    function claimSetupCode() {
        const codeInput = document.getElementById('setupCodeInput');
        const msgEl = document.getElementById('tgClaimMsg');
        const code = codeInput.value.trim().toUpperCase();

        if (code.length !== 6) {
            msgEl.innerHTML = '<span style="color:#f59e0b;">⚠️ សូមបញ្ចូលលេខកូដ ៦ ខ្ទង់ / Enter 6-digit code</span>';
            codeInput.focus();
            return;
        }

        msgEl.innerHTML = '<span style="color:#f59e0b;">⏳ កំពុងភ្ជាប់... / Connecting...</span>';

        fetch('<?php echo $urlPrefix; ?>/public/api/gps_claim_code.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ setup_code: code }),
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                msgEl.innerHTML = '<span style="color:#10b981;font-size:15px;">✅ ' + res.message + '</span>';
                // Reload after 2 seconds to show connected state
                setTimeout(() => location.reload(), 2000);
            } else {
                msgEl.innerHTML = '<span style="color:#ef4444;">❌ ' + (res.error || 'Error') + '</span>';
            }
        })
        .catch(err => {
            msgEl.innerHTML = '<span style="color:#ef4444;">❌ មានបញ្ហា / Error: ' + err.message + '</span>';
        });
    }

    // Disconnect Telegram
    function disconnectTelegram() {
        if (!confirm('ផ្តាច់ Telegram? / Disconnect Telegram?\n\nYou can reconnect anytime by entering a new setup code.')) return;

        const msgEl = document.getElementById('tgConfigMsg');
        msgEl.innerHTML = '<span style="color:#f59e0b;">⏳ កំពុងផ្តាច់... / Disconnecting...</span>';

        fetch('<?php echo $urlPrefix; ?>/public/api/gps_telegram_config.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ chat_id: '', chat_title: '', setup_code: '', is_active: 0 }),
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                setTimeout(() => location.reload(), 1000);
            } else {
                msgEl.innerHTML = '<span style="color:#ef4444;">❌ ' + (res.error || 'Error') + '</span>';
            }
        });
    }

    function saveTelegramConfig() {
        const form = document.getElementById('telegramConfigForm');
        const data = {
            notify_session_open: form.notify_session_open?.checked ? 1 : 0,
            notify_session_close: form.notify_session_close?.checked ? 1 : 0,
            notify_sales_report: form.notify_sales_report?.checked ? 1 : 0,
            notify_gps_start: form.notify_gps_start?.checked ? 1 : 0,
            notify_gps_stop: form.notify_gps_stop?.checked ? 1 : 0,
            is_active: 1
        };

        fetch('<?php echo $urlPrefix; ?>/public/api/gps_telegram_config.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(res => {
            const msgEl = document.getElementById('tgConfigMsg');
            if (res.success) {
                msgEl.innerHTML = '<span style="color:#10b981;">✅ បានរក្សាទុក / Saved!</span>';
            } else {
                msgEl.innerHTML = '<span style="color:#ef4444;">❌ ' + (res.error || 'Error') + '</span>';
            }
            setTimeout(() => msgEl.innerHTML = '', 5000);
        });
    }

    function testTelegram() {
        const msgEl = document.getElementById('tgConfigMsg');
        msgEl.innerHTML = '<span style="color:#f59e0b;">⏳ កំពុងផ្ញើសារសាកល្បង...</span>';

        fetch('<?php echo $urlPrefix; ?>/public/api/gps_telegram_config.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: 'test' }),
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(tgRes => {
            if (tgRes && tgRes.success) {
                msgEl.innerHTML = '<span style="color:#10b981;">✅ បានផ្ញើសារសាកល្បង! សូមពិនិត្យមើលក្រុម Telegram របស់អ្នក។</span>';
            } else {
                msgEl.innerHTML = '<span style="color:#ef4444;">❌ បរាជ័យ / Failed: ' + ((tgRes && tgRes.error) || 'Unknown') + '</span>';
            }
            setTimeout(() => msgEl.innerHTML = '', 8000);
        })
        .catch(err => {
            msgEl.innerHTML = '<span style="color:#ef4444;">❌ បរាជ័យ / Failed: ' + err.message + '</span>';
            setTimeout(() => msgEl.innerHTML = '', 8000);
        });
    }

    // ===== Tab Switching =====
    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.gps-tab-link').forEach(el => el.classList.remove('active'));

        const tabEl = document.getElementById('tab-' + tabName);
        if (tabEl) tabEl.classList.add('active');

        // Find the corresponding tab link
        document.querySelectorAll('.gps-tab-link').forEach(link => {
            if (link.textContent.toLowerCase().includes(tabName.replace('-', ' '))) {
                link.classList.add('active');
            }
        });

        // Re-invalidate map size if switching to map tab
        if (tabName === 'live-map' && map) {
            setTimeout(() => map.invalidateSize(), 100);
        }
    }

    // ===== Helpers =====
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // ===== Init =====
    document.addEventListener('DOMContentLoaded', function() {
        initMap();
    });

    // Cleanup on page leave
    window.addEventListener('beforeunload', function() {
        if (livePollInterval) clearInterval(livePollInterval);
    });
    </script>
</body>
</html>
