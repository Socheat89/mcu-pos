<?php
$tenant = class_exists('Tenant') ? (Tenant::getCurrent() ?? []) : [];
$tenantName = is_array($tenant) && !empty($tenant['name']) ? $tenant['name'] : 'Tenant';
$tenantSlug  = is_array($tenant) && !empty($tenant['subdomain']) ? $tenant['subdomain'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('qr_menu'); ?> - <?php echo htmlspecialchars($tenantName); ?></title>
    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&family=Battambang:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <style>
        body, h1, h2, h3, h4, h5, h6, p, span, a, button, input, select, textarea {
            font-family: 'Battambang', 'Outfit', 'Inter', sans-serif !important;
        }
        .menu-hero {
            background: var(--pos-gradient-dark);
            border-radius: 24px;
            padding: 36px 40px;
            color: white;
            margin-bottom: 32px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.3);
            border: 1px solid var(--pos-border);
        }
        .menu-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 70% 50%, rgba(99,102,241,0.15) 0%, transparent 60%);
            pointer-events: none;
        }
        .menu-hero-icon {
            width: 56px; height: 56px;
            background: rgba(99,102,241,0.2);
            border-radius: 16px;
            display: grid; place-items: center;
            font-size: 24px; color: #a5b4fc;
            margin-bottom: 16px;
            border: 1px solid rgba(99,102,241,0.3);
        }

        /* Tabs */
        .tab-bar {
            display: flex; gap: 8px;
            background: var(--pos-card);
            border: 1px solid var(--pos-border);
            border-radius: 16px; padding: 6px;
            margin-bottom: 28px;
        }
        .tab-btn {
            flex: 1; padding: 10px 20px;
            border-radius: 10px; border: none;
            font-weight: 700; font-size: 13px;
            cursor: pointer; transition: all 0.2s;
            color: var(--pos-text-muted);
            background: transparent;
        }
        .tab-btn.active {
            background: var(--pos-primary);
            color: white;
            box-shadow: 0 4px 12px rgba(99,102,241,0.35);
        }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }

        /* Cards */
        .qr-card {
            background: var(--pos-card);
            border-radius: 20px; padding: 28px;
            border: 1px solid var(--pos-border);
            text-align: center;
        }
        .qr-img-wrap {
            background: white;
            border-radius: 16px; padding: 16px;
            display: inline-block;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        }
        .link-card {
            background: var(--pos-card);
            border-radius: 20px; padding: 28px;
            border: 1px solid var(--pos-border);
        }
        .copy-input {
            background: rgba(255,255,255,0.05);
            border: 1.5px solid var(--pos-border);
            border-radius: 12px; padding: 12px 16px;
            width: 100%;
            font-family: 'Outfit', monospace;
            font-size: 13px;
            font-weight: 600;
            color: var(--pos-text);
            margin-bottom: 14px; outline: none;
            transition: all 0.2s;
        }
        .copy-input:focus { border-color: var(--pos-primary); }

        /* Table QR grid */
        .table-controls {
            background: var(--pos-card);
            border: 1px solid var(--pos-border);
            border-radius: 20px; padding: 24px;
            margin-bottom: 24px;
            display: flex; align-items: center;
            gap: 20px; flex-wrap: wrap;
        }
        .table-count-label {
            font-size: 14px; font-weight: 700;
            color: var(--pos-text-muted);
            white-space: nowrap;
        }
        .table-count-input {
            background: rgba(255,255,255,0.05);
            border: 1.5px solid var(--pos-border);
            border-radius: 12px; padding: 10px 16px;
            font-weight: 700; font-size: 15px;
            color: var(--pos-text); outline: none;
            width: 100px; transition: all 0.2s;
        }
        .table-count-input:focus { border-color: var(--pos-primary); }

        .table-qr-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .table-qr-card {
            background: var(--pos-card);
            border: 1px solid var(--pos-border);
            border-radius: 20px; padding: 20px;
            text-align: center;
            transition: all 0.25s ease;
        }
        .table-qr-card:hover {
            border-color: rgba(99,102,241,0.3);
            box-shadow: 0 8px 24px rgba(99,102,241,0.1);
            transform: translateY(-3px);
        }
        .table-qr-card .table-label {
            font-size: 13px; font-weight: 800;
            color: var(--pos-primary);
            margin-bottom: 12px;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .table-qr-card .qr-img-wrap-sm {
            background: white; border-radius: 12px;
            padding: 10px; display: inline-block;
            border: 1px solid #e2e8f0; margin-bottom: 14px;
        }
        .table-qr-card .card-actions {
            display: flex; gap: 8px; justify-content: center;
        }
        .btn-sm-icon {
            width: 36px; height: 36px;
            border-radius: 10px; border: 1px solid var(--pos-border);
            background: var(--pos-card); color: var(--pos-text-muted);
            display: grid; place-items: center;
            font-size: 13px; cursor: pointer;
            transition: all 0.2s; text-decoration: none;
        }
        .btn-sm-icon:hover {
            background: var(--pos-primary);
            color: white; border-color: var(--pos-primary);
        }

        /* Info banner */
        .info-banner {
            display: flex; align-items: flex-start; gap: 18px;
            background: rgba(99,102,241,0.06);
            border: 1px solid rgba(99,102,241,0.15);
            border-radius: 16px; padding: 20px 24px;
            margin-top: 28px;
        }
        .info-banner-icon {
            width: 44px; height: 44px; flex-shrink: 0;
            background: rgba(99,102,241,0.15);
            border-radius: 12px; display: grid;
            place-items: center; color: var(--pos-primary);
            font-size: 18px;
        }

        /* Print styles */
        @media print {
            body * { visibility: hidden; }
            #printArea, #printArea * { visibility: visible; }
            #printArea { position: fixed; inset: 0; background: white; padding: 20px; }
            .table-qr-grid { grid-template-columns: repeat(3, 1fr); }
            .table-qr-card { border: 1px solid #ccc; break-inside: avoid; }
            .tab-bar, .table-controls, .menu-hero, .pos-sidebar, .pos-topbar,
            .info-banner, .btn-sm-icon { display: none !important; }
        }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'digital_menu'; include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in">
        <!-- Hero -->
        <div class="menu-hero">
            <div class="menu-hero-icon"><i class="fas fa-qrcode"></i></div>
            <h1 style="font-size: 28px; font-weight: 900; margin: 0 0 8px;"><?php echo __('qr_menu'); ?></h1>
            <p style="color: rgba(255,255,255,0.6); margin: 0; font-size: 14px; max-width: 520px;"><?php echo __('qr_menu_subtitle'); ?></p>
        </div>

        <!-- Tab Bar -->
        <div class="tab-bar">
            <button class="tab-btn active" onclick="switchTab('general', this)" id="tab-general">
                <i class="fas fa-globe" style="margin-right:6px;"></i><?php echo __('qr_general_menu'); ?>
            </button>
            <button class="tab-btn" onclick="switchTab('tables', this)" id="tab-tables">
                <i class="fas fa-table-cells-large" style="margin-right:6px;"></i><?php echo __('qr_per_table'); ?>
            </button>
        </div>

        <!-- === Tab 1: General Menu === -->
        <div class="tab-pane active" id="pane-general">
            <div class="pos-grid cols-2">
                <!-- QR Code -->
                <div class="qr-card">
                    <h3 class="pos-card-title" style="margin-bottom: 20px;"><?php echo __('scan_qr_code'); ?></h3>
                    <div class="qr-img-wrap">
                        <img id="generalQr"
                             src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=<?php echo urlencode($menuUrl); ?>"
                             alt="QR Code" style="width: 220px; height: 220px; display: block;">
                    </div>
                    <p style="font-size: 12px; color: var(--pos-text-muted); margin-bottom: 20px;"><?php echo __('qr_scan_hint'); ?></p>
                    <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                        <button class="btn btn-primary" onclick="printGeneral()">
                            <i class="fas fa-print"></i> <?php echo __('print_qr'); ?>
                        </button>
                        <a href="https://api.qrserver.com/v1/create-qr-code/?size=600x600&data=<?php echo urlencode($menuUrl); ?>" download="menu-qr.png" target="_blank" class="btn btn-outline">
                            <i class="fas fa-download"></i> <?php echo __('download'); ?>
                        </a>
                    </div>
                </div>

                <!-- Shareable Link -->
                <div class="link-card">
                    <h3 class="pos-card-title" style="margin-bottom: 8px;"><?php echo __('shareable_link'); ?></h3>
                    <p style="color: var(--pos-text-muted); font-size: 13px; margin-bottom: 18px;"><?php echo __('copy_link_msg'); ?></p>

                    <input type="text" class="copy-input" value="<?php echo htmlspecialchars($menuUrl); ?>" readonly id="menuLink">
                    <button class="btn btn-primary w-100" onclick="copyLink('menuLink', this)">
                        <i class="fas fa-copy"></i> <?php echo __('copy_link'); ?>
                    </button>

                    <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--pos-border);">
                        <h4 style="font-weight: 800; font-size: 15px; margin-bottom: 14px;"><?php echo __('direct_preview'); ?></h4>
                        <a href="<?php echo htmlspecialchars($menuUrl); ?>" target="_blank" class="btn btn-outline w-100">
                            <i class="fas fa-external-link-alt"></i> <?php echo __('open_menu_new_tab'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- === Tab 2: Per-Table QR === -->
        <div class="tab-pane" id="pane-tables">
            <!-- Controls -->
            <div class="table-controls">
                <span class="table-count-label"><i class="fas fa-chair" style="margin-right:8px;"></i><?php echo __('qr_number_of_tables'); ?></span>
                <input type="number" class="table-count-input" id="tableCount" value="10" min="1" max="50" onchange="renderTableQRs()">
                <div style="margin-left: auto; display: flex; gap: 10px;">
                    <button class="btn btn-outline" onclick="printAllTables()">
                        <i class="fas fa-print"></i> <?php echo __('qr_print_all'); ?>
                    </button>
                </div>
            </div>

            <!-- QR Grid -->
            <div class="table-qr-grid" id="tableQrGrid"></div>
        </div>

        <!-- Info Banner -->
        <div class="info-banner">
            <div class="info-banner-icon"><i class="fas fa-circle-info"></i></div>
            <div>
                <h3 style="margin: 0 0 4px; font-size: 15px; font-weight: 800;"><?php echo __('realtime_updates'); ?></h3>
                <p style="margin: 0; color: var(--pos-text-muted); font-size: 13px; line-height: 1.6;"><?php echo __('realtime_updates_msg'); ?></p>
            </div>
        </div>
    </div>

    <!-- Hidden print area for table QRs -->
    <div id="printArea" style="display:none;"></div>

    <script>
        var menuBaseUrl = <?php echo json_encode($menuUrl); ?>;

        // Tab switcher
        function switchTab(name, btn) {
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById('pane-' + name).classList.add('active');
            btn.classList.add('active');
        }

        // Copy link helper
        function copyLink(inputId, btn) {
            var inp = document.getElementById(inputId);
            inp.select();
            inp.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(inp.value).catch(() => document.execCommand('copy'));
            var orig = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> <?php echo __('copied'); ?>';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-primary');
            setTimeout(() => {
                btn.innerHTML = orig;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-primary');
            }, 2000);
        }

        // Build table QR URL
        function tableUrl(n) {
            var sep = menuBaseUrl.indexOf('?') === -1 ? '?' : '&';
            return menuBaseUrl + sep + 'table=' + encodeURIComponent(n);
        }

        // Render table QR grid
        function renderTableQRs() {
            var count = Math.max(1, Math.min(50, parseInt(document.getElementById('tableCount').value) || 10));
            var grid = document.getElementById('tableQrGrid');
            grid.innerHTML = '';
            for (var i = 1; i <= count; i++) {
                (function(n) {
                    var url = tableUrl(n);
                    var qrSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(url);
                    var card = document.createElement('div');
                    card.className = 'table-qr-card';
                    card.innerHTML = `
                        <div class="table-label"><i class="fas fa-chair" style="margin-right:5px;"></i><?php echo __('qr_table'); ?> ${n}</div>
                        <div class="qr-img-wrap-sm">
                            <img src="${qrSrc}" alt="Table ${n}" style="width:130px;height:130px;display:block;">
                        </div>
                        <div class="card-actions">
                            <a href="${qrSrc.replace('150x150','600x600')}" download="table-${n}-qr.png" target="_blank" class="btn-sm-icon" title="<?php echo __('download'); ?>">
                                <i class="fas fa-download"></i>
                            </a>
                            <button class="btn-sm-icon" onclick="printSingleTable(${n})" title="<?php echo __('print_qr'); ?>">
                                <i class="fas fa-print"></i>
                            </button>
                            <a href="${url}" target="_blank" class="btn-sm-icon" title="<?php echo __('open_menu_new_tab'); ?>">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    `;
                    grid.appendChild(card);
                })(i);
            }
        }

        // Print single table QR
        function printSingleTable(n) {
            var url = tableUrl(n);
            var qrSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + encodeURIComponent(url);
            var w = window.open('', '_blank', 'width=400,height=500');
            w.document.write(`<!DOCTYPE html><html><head><title><?php echo __('qr_table'); ?> ${n}</title>
                <style>body{font-family:sans-serif;text-align:center;padding:40px;background:#fff;}
                h2{font-size:22px;margin-bottom:4px;}p{color:#666;font-size:13px;}
                img{border-radius:12px;border:1px solid #e2e8f0;padding:8px;}</style></head>
                <body onload="window.print();window.close();">
                <h2><?php echo htmlspecialchars($tenantName); ?></h2>
                <p><?php echo __('qr_table'); ?> ${n}</p>
                <img src="${qrSrc}" width="300" height="300"><br>
                <p style="margin-top:12px;font-size:11px;color:#94a3b8;"><?php echo __('qr_scan_hint'); ?></p>
                </body></html>`);
            w.document.close();
        }

        // Print general QR
        function printGeneral() {
            var qrSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=' + encodeURIComponent(menuBaseUrl);
            var w = window.open('', '_blank', 'width=500,height=600');
            w.document.write(`<!DOCTYPE html><html><head><title><?php echo __('qr_general_menu'); ?></title>
                <style>body{font-family:sans-serif;text-align:center;padding:40px;background:#fff;}
                h2{font-size:22px;margin-bottom:4px;}p{color:#666;font-size:13px;}
                img{border-radius:16px;border:1px solid #e2e8f0;padding:10px;}</style></head>
                <body onload="window.print();window.close();">
                <h2><?php echo htmlspecialchars($tenantName); ?></h2>
                <p><?php echo __('explore_menu_msg'); ?></p>
                <img src="${qrSrc}" width="320" height="320"><br>
                <p style="margin-top:12px;font-size:11px;color:#94a3b8;"><?php echo __('qr_scan_hint'); ?></p>
                </body></html>`);
            w.document.close();
        }

        // Print all tables
        function printAllTables() {
            var count = Math.max(1, Math.min(50, parseInt(document.getElementById('tableCount').value) || 10));
            var cards = '';
            for (var i = 1; i <= count; i++) {
                var url = tableUrl(i);
                var qrSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(url);
                cards += `<div style="display:inline-block;text-align:center;margin:12px;vertical-align:top;break-inside:avoid;width:200px;">
                    <p style="font-weight:800;margin:0 0 6px;"><?php echo htmlspecialchars($tenantName); ?></p>
                    <p style="font-size:12px;color:#555;margin:0 0 8px;"><?php echo __('qr_table'); ?> ${i}</p>
                    <img src="${qrSrc}" style="width:180px;height:180px;border:1px solid #ddd;border-radius:10px;padding:6px;">
                    <p style="font-size:10px;color:#aaa;margin-top:6px;"><?php echo __('qr_scan_hint'); ?></p>
                </div>`;
            }
            var w = window.open('', '_blank', 'width=900,height=700');
            w.document.write(`<!DOCTYPE html><html><head><title><?php echo __('qr_print_all'); ?></title>
                <style>body{font-family:sans-serif;padding:20px;background:#fff;}</style></head>
                <body onload="window.print();window.close();">${cards}</body></html>`);
            w.document.close();
        }

        // Init
        renderTableQRs();
    </script>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
