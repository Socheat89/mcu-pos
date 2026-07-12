<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('sessions'); ?> - <?php echo htmlspecialchars($tenantName ?? 'POS'); ?></title>
    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&family=Battambang:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <style>
        .search-container { position: relative; margin-bottom: 24px; }
        .search-container i { position: absolute; left: 20px; top: 16px; color: var(--pos-primary); font-size: 18px; }
        .search-container input { width: 100%; padding: 14px 20px 14px 54px; border-radius: var(--pos-radius); border: 1.5px solid var(--pos-border); background: #ffffff; color: var(--pos-text); font-size: 15px; font-weight: 600; outline: none; transition: all 0.3s; }
        .search-container input:focus { border-color: var(--pos-primary); background: #ffffff; box-shadow: 0 0 0 4px rgba(var(--pos-primary-rgb), 0.15); }
        .avatar-box { width: 36px; height: 36px; border-radius: var(--pos-radius); background: var(--pos-primary-light); display: grid; place-items: center; font-size: 14px; font-weight: 900; color: var(--pos-primary); border: 1px solid rgba(var(--pos-primary-rgb), 0.2); }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'sessions'; include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
            <div class="pos-title">
                <h1><?php echo __('session_list'); ?></h1>
                <p><?php echo __('sessions'); ?> - Odoo POS style cash control and sales tracking</p>
            </div>
            
            <div style="display: flex; gap: 12px;">
                <?php if ($activeSession): ?>
                    <a href="<?php echo htmlspecialchars($posUrl('pos')); ?>" class="btn" style="background: var(--pos-secondary); color: white; border: none;">
                        <i class="fas fa-desktop"></i> <?php echo __('resume_sale'); ?>
                    </a>
                    <a href="<?php echo htmlspecialchars($posUrl('sessions/close')); ?>" class="btn" style="background: rgba(244, 63, 94, 0.2); border: 1px solid rgba(244, 63, 94, 0.3); color: #fda4af;">
                        <i class="fas fa-power-off"></i> <?php echo __('close_session'); ?>
                    </a>
                <?php else: ?>
                    <a href="<?php echo htmlspecialchars($posUrl('sessions/open')); ?>" class="btn btn-primary">
                        <i class="fas fa-key"></i> <?php echo __('open_session'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isset($_SESSION['success_msg'])): ?>
            <div style="padding: 16px; border-radius: var(--pos-radius); background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #86efac; font-weight: 700; margin-bottom: 24px;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success_msg']); unset($_SESSION['success_msg']); ?>
            </div>
        <?php endif; ?>

        <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search sessions..." onkeyup="searchSessions()">
        </div>

        <div class="pos-table-container">
            <table class="pos-table" id="sessionsTable">
                <thead>
                    <tr>
                        <th style="width: 100px;">ID</th>
                        <th><?php echo __('opened_by'); ?></th>
                        <th><?php echo __('opened_at'); ?></th>
                        <th><?php echo __('closed_at'); ?></th>
                        <th><?php echo __('opening_balance'); ?></th>
                        <th><?php echo __('total_sales'); ?></th>
                        <th><?php echo __('closing_balance'); ?></th>
                        <th><?php echo __('status'); ?></th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sessions)): ?>
                        <tr>
                            <td colspan="9" style="padding: 100px; text-align: center;">
                                <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.03); border: 1px solid var(--pos-border); border-radius: 50%; display: grid; place-items: center; margin: 0 auto 20px;">
                                    <i class="fas fa-history" style="font-size: 32px; color: var(--pos-text-dim);"></i>
                                </div>
                                <h3 style="color: var(--pos-text); font-weight: 800; margin: 0;"><?php echo __('no_sessions_found'); ?></h3>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sessions as $s): 
                            $status = $s['status'] ?? 'closed';
                            $badge = ($status === 'open') ? 'badge-success' : 'badge-warning';
                            $statusLabel = ($status === 'open') ? __('status_open') : __('status_closed');
                        ?>
                            <tr class="session-row">
                                <td style="font-weight: 800; color: var(--pos-primary);">#<?php echo $s['id']; ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div class="avatar-box">
                                            <?php echo strtoupper(substr($s['username'] ?? 'C', 0, 1)); ?>
                                        </div>
                                        <div style="font-weight: 700; color: var(--pos-text);"><?php echo htmlspecialchars($s['username'] ?? 'Cashier'); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 13px; font-weight: 600; color: var(--pos-text);"><?php echo date('M d, Y', strtotime($s['opened_at'])); ?></div>
                                    <div style="font-size: 11px; color: var(--pos-text-muted); font-weight: 600; margin-top: 2px;"><?php echo date('h:i A', strtotime($s['opened_at'])); ?></div>
                                </td>
                                <td>
                                    <?php if ($s['closed_at']): ?>
                                        <div style="font-size: 13px; font-weight: 600; color: var(--pos-text);"><?php echo date('M d, Y', strtotime($s['closed_at'])); ?></div>
                                        <div style="font-size: 11px; color: var(--pos-text-muted); font-weight: 600; margin-top: 2px;"><?php echo date('h:i A', strtotime($s['closed_at'])); ?></div>
                                    <?php else: ?>
                                        <span style="color: var(--pos-text-muted); font-style: italic;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--pos-text);">$<?php echo number_format($s['opening_balance'], 2); ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: 800; color: var(--pos-primary);">$<?php echo number_format($s['total_sales'], 2); ?></div>
                                </td>
                                <td>
                                    <?php if ($s['closing_balance'] !== null): ?>
                                        <div style="font-weight: 700; color: var(--pos-text);">$<?php echo number_format($s['closing_balance'], 2); ?></div>
                                    <?php else: ?>
                                        <span style="color: var(--pos-text-muted); font-style: italic;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $badge; ?>">
                                        <?php echo $statusLabel; ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                                        <a href="<?php echo htmlspecialchars($posUrl('sessions/' . $s['id'])); ?>" class="pos-icon-btn" title="<?php echo __('view_details'); ?>">
                                            <i class="fas fa-eye" style="font-size: 14px;"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function searchSessions() {
            const filter = document.getElementById('searchInput').value.toUpperCase();
            const rows = document.querySelectorAll('.session-row');
            rows.forEach(row => {
                const text = row.innerText.toUpperCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        }
    </script>
    
    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
