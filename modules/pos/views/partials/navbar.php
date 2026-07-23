<?php
// Shared POS layout shell (sidebar + topbar).
// Expected optional vars from caller:
//   - $activeNav: one of dashboard|pos|holds|products|customers|reports|settings

$host = $_SERVER['HTTP_HOST'] ?? '';
$helperCandidates = [
    dirname(__DIR__, 4) . '/core/helpers/url.php',
    dirname(__DIR__, 5) . '/core/helpers/url.php',
    dirname(__DIR__, 3) . '/core/helpers/url.php',
    __DIR__ . '/../../../core/helpers/url.php'
];
foreach ($helperCandidates as $helperPath) {
    if (is_file($helperPath)) {
        require_once $helperPath;
        break;
    }
}
if (!function_exists('mc_base_path')) {
    throw new RuntimeException('Unable to load core/helpers/url.php');
}

// Load Store class for store switcher
$storeClassPath = dirname(__DIR__, 4) . '/core/classes/Store.php';
if (is_file($storeClassPath) && !class_exists('Store')) {
    require_once $storeClassPath;
}
$basePath = mc_base_path();
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';

$tenantSlug = null;
$tenantName = 'Mekong';

if (class_exists('Tenant')) {
    $currentTenant = Tenant::getCurrent();
    if (is_array($currentTenant)) {
        $tenantSlug = $currentTenant['subdomain'] ?? null;
        $tenantName = $currentTenant['name'] ?? 'Mekong';
    }
}

// Detect if we are on the development direct POS route: /Mekong_CyberUnit/pos/...
$devPosPrefix = $basePath . '/pos/';
$isDevPos = (strpos($requestPath, $devPosPrefix) === 0);

$posBase = $basePath;
if ($isDevPos) {
    $posBase .= '/pos';
} elseif ($tenantSlug) {
    $posBase .= '/' . $tenantSlug . '/pos';
} else {
    // Fallback.
    $posBase .= '/pos';
}

$logoutUrl = $tenantSlug && !$isDevPos
    ? ($basePath . '/' . $tenantSlug . '/logout')
    : ($basePath . '/public/logout.php');

$activeNav = $activeNav ?? '';

$posUrl = function (string $path) use ($posBase): string {
    $path = ltrim($path, '/');
    return $posBase . '/' . $path;
};

$activeClass = function (string $key) use ($activeNav): string {
    return ($activeNav === $key) ? 'active' : '';
};

$navLabel = function (string $key): string {
    $labels = [
        'dashboard'    => __('dashboard'),
        'pos'          => __('pos'),
        'sessions'     => __('sessions'),
        'holds'        => __('on_hold'),
        'orders'       => __('orders'),
        'products'     => __('inventory'),
        'customers'    => __('customers'),
        'reports'      => __('analytics'),
        'settings'     => __('settings'),
        'cashiers'     => __('cashiers'),
        'digital_menu' => __('qr_menu'),
        'stores'       => __('manage_stores'),
        'gps'          => __('gps_tracking'),
    ];

    return $labels[$key] ?? __($key);
};
?>

<div class="pos-shell" id="posShell">
    <div class="pos-overlay" id="posOverlay" aria-hidden="true"></div>

    <aside class="pos-sidebar">
        <div class="pos-sidebar__brand">
            <a class="pos-brand" href="<?php echo htmlspecialchars($posUrl('dashboard')); ?>">
                <div class="pos-brand__logo">
                    <i class="fas fa-terminal"></i>
                </div>
                <div class="pos-brand__text">
                    <span class="pos-brand__title"><?php echo htmlspecialchars($tenantName); ?></span>
                    <span class="pos-brand__sub"><?php echo __('cyber_unit_pos'); ?></span>
                </div>
            </a>
        </div>

        <nav class="pos-side-nav">
            <?php
            $hasFeature = function($mod, $feat) use ($isDevPos) {
                if ($isDevPos) return true;
                if (!class_exists('Tenant')) return false;
                return Tenant::hasFeature($mod, $feat);
            };
            ?>

            <?php if ($hasFeature('pos', 'core')): ?>
            <a class="pos-side-link <?php echo $activeClass('dashboard'); ?>" href="<?php echo htmlspecialchars($posUrl('dashboard')); ?>">
                <i class="fas fa-chart-pie"></i><span><?php echo $navLabel('dashboard'); ?></span>
            </a>
            <a class="pos-side-link <?php echo $activeClass('pos'); ?>" href="<?php echo htmlspecialchars($posUrl('pos')); ?>">
                <i class="fas fa-desktop"></i><span><?php echo $navLabel('pos'); ?></span>
            </a>
            <a class="pos-side-link <?php echo $activeClass('sessions'); ?>" href="<?php echo htmlspecialchars($posUrl('sessions')); ?>">
                <i class="fas fa-history"></i><span><?php echo $navLabel('sessions'); ?></span>
            </a>
            <?php endif; ?>

            <?php if ($hasFeature('pos', 'holds')): ?>
            <a class="pos-side-link <?php echo $activeClass('holds'); ?>" href="<?php echo htmlspecialchars($posUrl('holds')); ?>">
                <i class="fas fa-clock-rotate-left"></i><span><?php echo $navLabel('holds'); ?></span>
            </a>
            <?php endif; ?>

            <?php if ($hasFeature('pos', 'orders')): ?>
            <a class="pos-side-link <?php echo $activeClass('orders'); ?>" href="<?php echo htmlspecialchars($posUrl('orders')); ?>">
                <i class="fas fa-list-ul"></i><span><?php echo $navLabel('orders'); ?></span>
            </a>
            <?php endif; ?>

            <?php 
            $isTenantAdmin = class_exists('Auth') && Auth::isTenantAdmin();
            $canManage = $hasFeature('pos', 'inventory') || $hasFeature('pos', 'customers') || $hasFeature('pos', 'reports') || $hasFeature('pos', 'settings');
            if ($canManage): 
            ?>
                <div class="pos-nav-header"><?php echo __('management'); ?></div>
                
                <?php if ($hasFeature('pos', 'inventory') && $isTenantAdmin): ?>
                <a class="pos-side-link <?php echo $activeClass('products'); ?>" href="<?php echo htmlspecialchars($posUrl('products')); ?>">
                    <i class="fas fa-boxes-stacked"></i><span><?php echo $navLabel('products'); ?></span>
                </a>
                <?php endif; ?>

                <a class="pos-side-link <?php echo $activeClass('customers'); ?>" href="<?php echo htmlspecialchars($posUrl('customers')); ?>">
                    <i class="fas fa-user-group"></i><span><?php echo $navLabel('customers'); ?></span>
                </a>

                <?php if ($hasFeature('pos', 'reports') && $isTenantAdmin): ?>
                <a class="pos-side-link <?php echo $activeClass('reports'); ?>" href="<?php echo htmlspecialchars($posUrl('reports')); ?>">
                    <i class="fas fa-chart-line"></i><span><?php echo $navLabel('reports'); ?></span>
                </a>
                <?php endif; ?>

                <?php if ($hasFeature('pos', 'settings') && $isTenantAdmin): ?>
                <a class="pos-side-link <?php echo $activeClass('settings'); ?>" href="<?php echo htmlspecialchars($posUrl('settings')); ?>">
                    <i class="fas fa-gear"></i><span><?php echo $navLabel('settings'); ?></span>
                </a>
                <?php endif; ?>

                <?php if ($isTenantAdmin): ?>
                <a class="pos-side-link <?php echo $activeClass('cashiers'); ?>" href="<?php echo htmlspecialchars($posUrl('cashiers')); ?>">
                    <i class="fas fa-user-tie"></i><span><?php echo $navLabel('cashiers'); ?></span>
                </a>
                <?php endif; ?>

                <?php if ($isTenantAdmin): ?>
                <a class="pos-side-link <?php echo $activeClass('digital_menu'); ?>" href="<?php echo htmlspecialchars($posUrl('menu/admin')); ?>">
                    <i class="fas fa-qrcode"></i><span><?php echo $navLabel('digital_menu'); ?></span>
                </a>
                <?php endif; ?>

                <?php if ($isTenantAdmin): ?>
                <a class="pos-side-link <?php echo $activeClass('stores'); ?>" href="<?php echo htmlspecialchars($posUrl('stores')); ?>">
                    <i class="fas fa-store-alt"></i><span><?php echo $navLabel('stores'); ?></span>
                </a>
                <?php endif; ?>

                <?php if ($isTenantAdmin): ?>
                <a class="pos-side-link <?php echo $activeClass('gps'); ?>" href="<?php echo htmlspecialchars($posUrl('gps')); ?>">
                    <i class="fas fa-satellite"></i><span><?php echo $navLabel('gps'); ?></span>
                </a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>

        <div class="pos-sidebar__footer">
            <a class="pos-side-link" href="<?php echo htmlspecialchars($logoutUrl); ?>">
                <i class="fas fa-right-from-bracket"></i><span><?php echo __('logout'); ?></span>
            </a>
        </div>
    </aside>

    <main class="pos-main">
        <header class="pos-topbar">
            <div class="pos-header-left">
                <button class="pos-sidebar-toggle" type="button" onclick="window.__posToggleSidebar && window.__posToggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="pos-status-indicator">
                    <div class="pos-status-dot"></div>
                    <span class="pos-status-text">
                        <?php echo htmlspecialchars($pageTitle ?? ($activeNav ? $navLabel($activeNav) : $navLabel('dashboard'))); ?>
                    </span>
                </div>
            </div>

            <div class="pos-header-right">
                <div class="pos-clock-widget">
                     <div id="posClock" class="pos-clock-time"><?php echo date('H:i'); ?></div>
                     <div class="pos-clock-date"><?php echo date('F d, Y'); ?></div>
                </div>

                <div class="pos-header-divider"></div>

                <!-- Language Switcher -->
                <style>
                    .pos-lang-switcher {
                        position: relative;
                        display: flex;
                        align-items: center;
                    }
                    .pos-lang-btn {
                        background: rgba(14, 19, 34, 0.6);
                        border: 1px solid var(--pos-border);
                        padding: 10px 14px;
                        border-radius: 16px;
                        font-weight: 800;
                        font-family: 'Space Grotesk', 'Battambang', sans-serif;
                        font-size: 13px;
                        color: var(--pos-text);
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        box-shadow: var(--pos-shadow-sm);
                        transition: all 0.2s;
                    }
                    .pos-lang-btn:hover {
                        border-color: var(--pos-primary);
                        background: rgba(14, 19, 34, 0.8);
                        color: var(--pos-primary);
                        transform: translateY(-1px);
                    }
                    .pos-lang-dropdown { 
                        position: absolute;
                        top: 100%;
                        right: 0;
                        padding-top: 15px; /* Bridge gap */
                        background: transparent;
                        display: none;
                        z-index: 1000;
                    }
                    .pos-lang-dropdown-inner {
                        background: var(--pos-elevated);
                        border-radius: 18px;
                        box-shadow: var(--pos-shadow-xl);
                        border: 1px solid var(--pos-border);
                        min-width: 160px;
                        padding: 6px;
                        overflow: hidden;
                    }
                    .pos-lang-switcher:hover .pos-lang-dropdown,
                    .pos-lang-switcher.active .pos-lang-dropdown { 
                        display: block;
                    }
                    .pos-lang-item {
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        padding: 10px 14px;
                        text-decoration: none;
                        color: var(--pos-text-muted);
                        font-size: 13px;
                        font-weight: 700;
                        border-radius: 12px;
                        transition: all 0.2s;
                    }
                    .pos-lang-item:hover {
                        background: rgba(255, 255, 255, 0.05);
                        color: var(--pos-primary);
                    }
                    .pos-lang-item.active {
                        background: var(--pos-primary-light);
                        color: var(--pos-primary);
                    }
                </style>
                <div class="pos-lang-switcher" id="posLangSwitcher">
                    <button class="pos-lang-btn" type="button" onclick="togglePosLang(event)">
                        <i class="fas fa-globe"></i>
                        <?php 
                        $curr = Language::getCurrentLang();
                        echo $curr == 'en' ? 'English' : ($curr == 'km' ? 'ភាសាខ្មែរ' : '中文');
                        ?>
                    </button>
                    <div class="pos-lang-dropdown">
                        <div class="pos-lang-dropdown-inner">
                                <a href="<?php echo mc_url('public/set_lang.php?lang=en'); ?>" class="pos-lang-item <?php echo $curr == 'en' ? 'active' : ''; ?>">
                                <img src="https://flagcdn.com/w20/gb.png" width="20" alt="English"> English
                            </a>
                                <a href="<?php echo mc_url('public/set_lang.php?lang=km'); ?>" class="pos-lang-item <?php echo $curr == 'km' ? 'active' : ''; ?>">
                                <img src="https://flagcdn.com/w20/kh.png" width="20" alt="Khmer"> ភាសាខ្មែរ
                            </a>
                                <a href="<?php echo mc_url('public/set_lang.php?lang=zh'); ?>" class="pos-lang-item <?php echo $curr == 'zh' ? 'active' : ''; ?>">
                                <img src="https://flagcdn.com/w20/cn.png" width="20" alt="Chinese"> 中文
                            </a>
                        </div>
                    </div>
                </div>

                <div class="pos-header-divider"></div>

                <div class="pos-user-profile">
                    <div class="pos-user-details">
                        <?php 
                        $user = Auth::user(); 
                        $userName = $user ? (isset($user['first_name']) && $user['first_name'] ? $user['first_name'] . ' ' . ($user['last_name'] ?? '') : ($user['username'] ?? 'Administrator')) : 'Administrator';
                        $roleName = $user['role_name'] ?? 'Super Admin';
                        ?>
                        <span class="pos-user-name"><?php echo htmlspecialchars($userName); ?></span>
                        <span class="pos-user-role"><?php echo htmlspecialchars($roleName); ?></span>
                    </div>
                    <div class="pos-avatar">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
            </div>

            <script>
                setInterval(() => {
                    const now = new Date();
                    const clock = document.getElementById('posClock');
                    if(clock) clock.textContent = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', hour12: false});
                }, 1000);
            </script>
        </header>

        <div class="pos-page">

<script>
(function() {
    function setOpen(open) {
        var shell = document.getElementById('posShell');
        var overlay = document.getElementById('posOverlay');
        if (!shell || !overlay) return;
        shell.classList.toggle('pos-shell--open', !!open);
        overlay.setAttribute('aria-hidden', open ? 'false' : 'true');
    }

    function toggle() {
        var shell = document.getElementById('posShell');
        if (!shell) return;

        if (window.innerWidth >= 980) {
            // Desktop: toggle collapse
            shell.classList.toggle('pos-shell--collapsed');
        } else {
            // Mobile: toggle drawer
            setOpen(!shell.classList.contains('pos-shell--open'));
        }
    }

    window.__posToggleSidebar = toggle;

    var overlay = document.getElementById('posOverlay');
    if (overlay) {
        overlay.addEventListener('click', function() { setOpen(false); });
    }

    // Close sidebar on resize up (desktop)
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 980) setOpen(false);
    });
})();
</script>

<script>
(function() {
    if (window.POSUI) return;

    function ensureToastHost() {
        var host = document.querySelector('.pos-toast-host');
        if (host) return host;
        host = document.createElement('div');
        host.className = 'pos-toast-host';
        document.body.appendChild(host);
        return host;
    }

    function toast(opts) {
        opts = opts || {};
        var type = opts.type || 'info';
        var title = opts.title || (type === 'danger' ? '<?php echo __('error'); ?>' : type === 'warning' ? '<?php echo __('warning'); ?>' : type === 'success' ? '<?php echo __('success'); ?>' : '<?php echo __('info'); ?>');
        var message = opts.message || '';
        var timeout = typeof opts.timeout === 'number' ? opts.timeout : 2800;

        var host = ensureToastHost();
        var el = document.createElement('div');
        el.className = 'pos-toast pos-toast--' + type;
        el.innerHTML =
            '<span class="pos-toast__dot"></span>' +
            '<div style="min-width:0;">' +
                '<p class="pos-toast__title">' + String(title) + '</p>' +
                '<p class="pos-toast__msg">' + String(message) + '</p>' +
            '</div>';
        host.appendChild(el);

        setTimeout(function() {
            if (el && el.parentNode) el.parentNode.removeChild(el);
        }, timeout);
    }

    function buildModal(opts, isConfirm) {
        opts = opts || {};
        var type = opts.type || 'info';
        var title = opts.title || (isConfirm ? '<?php echo __('please_confirm'); ?>' : '<?php echo __('message'); ?>');
        var subtitle = opts.subtitle || '';
        var message = opts.message || '';
        var okText = opts.okText || (isConfirm ? '<?php echo __('yes'); ?>' : '<?php echo __('ok'); ?>');
        var cancelText = opts.cancelText || '<?php echo __('cancel'); ?>';

        var existing = document.querySelector('.pos-modal-overlay');
        if (existing) existing.remove();

        var overlay = document.createElement('div');
        overlay.className = 'pos-modal-overlay';
        overlay.style.display = 'grid';

        var modal = document.createElement('div');
        modal.className = 'pos-modal pos-modal--' + type;
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');

        var icon = '<i class="fas fa-circle-info"></i>';
        if (type === 'danger') icon = '<i class="fas fa-triangle-exclamation"></i>';
        if (type === 'warning') icon = '<i class="fas fa-triangle-exclamation"></i>';
        if (type === 'success') icon = '<i class="fas fa-circle-check"></i>';

        modal.innerHTML =
            '<div class="pos-modal__header">' +
                '<div class="pos-modal__title">' +
                    '<span class="pos-modal__icon">' + icon + '</span>' +
                    '<div style="min-width:0;">' +
                        '<h3>' + String(title) + '</h3>' +
                        (subtitle ? '<p>' + String(subtitle) + '</p>' : '') +
                    '</div>' +
                '</div>' +
                '<button class="pos-modal__close" type="button" aria-label="Close"><i class="fas fa-xmark"></i></button>' +
            '</div>' +
            '<div class="pos-modal__body">' + String(message) + '</div>' +
            '<div class="pos-modal__actions">' +
                (isConfirm ? '<button class="pos-modal-btn" type="button" data-pos-cancel="1">' + String(cancelText) + '</button>' : '') +
                '<button class="pos-modal-btn primary" type="button" data-pos-ok="1">' + String(okText) + '</button>' +
            '</div>';

        overlay.appendChild(modal);
        document.body.appendChild(overlay);

        function close() {
            if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay);
        }

        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) close();
        });
        modal.querySelector('.pos-modal__close').addEventListener('click', close);

        return { overlay: overlay, modal: modal, close: close };
    }

    function alertModal(opts) {
        opts = opts || {};
        var m = buildModal(opts, false);
        var ok = m.modal.querySelector('[data-pos-ok="1"]');
        ok.addEventListener('click', function() {
            m.close();
            if (typeof opts.onOk === 'function') opts.onOk();
        });
    }

    function confirmModal(opts) {
        opts = opts || {};
        var m = buildModal(opts, true);

        var ok = m.modal.querySelector('[data-pos-ok="1"]');
        var cancel = m.modal.querySelector('[data-pos-cancel="1"]');

        ok.addEventListener('click', function() {
            m.close();
            if (typeof opts.onOk === 'function') opts.onOk();
        });
        cancel.addEventListener('click', function() {
            m.close();
            if (typeof opts.onCancel === 'function') opts.onCancel();
        });
    }

    window.POSUI = {
        toast: toast,
        alert: alertModal,
        confirm: confirmModal
    };

    // Auto-handle confirm links (replace native confirm())
    document.addEventListener('click', function(e) {
        var el = e.target && e.target.closest ? e.target.closest('[data-pos-confirm]') : null;
        if (!el) return;

        var msg = el.getAttribute('data-pos-confirm') || 'Are you sure?';
        var href = el.getAttribute('href');
        if (!href) return;

        e.preventDefault();
        confirmModal({
            type: 'warning',
            title: '<?php echo __('confirm_action'); ?>',
            message: msg,
            okText: '<?php echo __('yes'); ?>',
            cancelText: '<?php echo __('cancel'); ?>',
            onOk: function() { window.location.href = href; }
        });
    });
})();
</script>
<script>
    function togglePosLang(e) {
        e.stopPropagation();
        var s = document.getElementById('posLangSwitcher');
        if (s) s.classList.toggle('active');
    }
    document.addEventListener('click', function(e) {
        var s = document.getElementById('posLangSwitcher');
        if (s && !s.contains(e.target)) s.classList.remove('active');
    });
</script>

<!-- 📱 Mobile Touch Enhancements -->
<script>
(function() {
    // Remove 300ms tap delay on all interactive elements
    var style = document.createElement('style');
    style.textContent = 'a, button, input, select, textarea, .btn, .pos-side-link, .pill-btn, .pos-tab-link { touch-action: manipulation; }';
    document.head.appendChild(style);

    // Swipe right to open sidebar on mobile
    var touchStartX = 0;
    document.addEventListener('touchstart', function(e) {
        touchStartX = e.touches[0].clientX;
    }, { passive: true });

    document.addEventListener('touchend', function(e) {
        var diff = e.changedTouches[0].clientX - touchStartX;
        // Swipe right > 80px from left edge → open sidebar
        if (diff > 80 && touchStartX < 30 && window.innerWidth < 980) {
            var shell = document.getElementById('posShell');
            if (shell && !shell.classList.contains('pos-shell--open')) {
                shell.classList.add('pos-shell--open');
                var overlay = document.getElementById('posOverlay');
                if (overlay) overlay.setAttribute('aria-hidden', 'false');
            }
        }
        // Swipe left > 80px → close sidebar
        if (diff < -80 && window.innerWidth < 980) {
            var shell = document.getElementById('posShell');
            if (shell && shell.classList.contains('pos-shell--open')) {
                shell.classList.remove('pos-shell--open');
                var overlay = document.getElementById('posOverlay');
                if (overlay) overlay.setAttribute('aria-hidden', 'true');
            }
        }
    });

    // Add scroll hint on tables
    document.addEventListener('DOMContentLoaded', function() {
        // Inject PWA meta tags for installable webapp
        var head = document.head;

        // Apple mobile webapp
        if (!document.querySelector('meta[name="apple-mobile-web-app-capable"]')) {
            var appleCapable = document.createElement('meta');
            appleCapable.name = 'apple-mobile-web-app-capable';
            appleCapable.content = 'yes';
            head.appendChild(appleCapable);
        }
        if (!document.querySelector('meta[name="apple-mobile-web-app-status-bar-style"]')) {
            var appleStatus = document.createElement('meta');
            appleStatus.name = 'apple-mobile-web-app-status-bar-style';
            appleStatus.content = 'black-translucent';
            head.appendChild(appleStatus);
        }
        if (!document.querySelector('link[rel="manifest"]')) {
            var manifestLink = document.createElement('link');
            manifestLink.rel = 'manifest';
            manifestLink.href = '/public/manifest.json';
            head.appendChild(manifestLink);
        }
        if (!document.querySelector('meta[name="theme-color"]')) {
            var themeColor = document.createElement('meta');
            themeColor.name = 'theme-color';
            themeColor.content = '#06b6d4';
            head.appendChild(themeColor);
        }

        var tables = document.querySelectorAll('.pos-table-container, .pos-table');
        tables.forEach(function(t) {
            if (t.scrollWidth > t.clientWidth) {
                t.style.setProperty('--table-scroll-hint', '"← swipe →"');
                t.setAttribute('data-scrollable', 'true');
            }
        });

        // Prevent pinch-zoom on mobile
        var vp = document.querySelector('meta[name="viewport"]');
        if (vp) {
            vp.setAttribute('content', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, viewport-fit=cover');
        }

        // Register Service Worker for PWA
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/public/service-worker.js');
        }
    });
})();
</script>
