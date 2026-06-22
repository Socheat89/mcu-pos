<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Management - <?php echo htmlspecialchars($tenantName); ?></title>
    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&family=Battambang:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <style>
        body, h1, h2, h3, h4, h5, h6, p, span, a, button, input, select, textarea {
            font-family: 'Battambang', 'Outfit', 'Inter', sans-serif !important;
        }

        /* ── Two-column layout ── */
        .csh-layout { display: grid; grid-template-columns: 400px 1fr; gap: 28px; align-items: start; }
        @media (max-width: 1100px) { .csh-layout { grid-template-columns: 1fr; } }

        /* ── Form card ── */
        .csh-form { position: sticky; top: 24px; }

        /* ── Avatar ring ── */
        .csh-avatar { width: 40px; height: 40px; border-radius: 14px; background: var(--pos-primary-light); display: grid; place-items: center; font-size: 16px; font-weight: 900; color: var(--pos-primary); border: 1.5px solid rgba(var(--pos-primary-rgb), 0.2); flex-shrink: 0; }
        .csh-avatar.inactive { background: rgba(255,255,255,0.04); color: var(--pos-text-muted); border-color: var(--pos-border); }

        /* ── Status badge ── */
        .badge-active   { background: rgba(16,185,129,0.12); color: #10b981; border: 1px solid rgba(16,185,129,0.25); padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 800; }
        .badge-inactive { background: rgba(244,63,94,0.1); color: var(--pos-danger); border: 1px solid rgba(244,63,94,0.2); padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 800; }
        .badge-perm     { background: rgba(99,102,241,0.1); color: var(--pos-primary); border: 1px solid rgba(99,102,241,0.2); padding: 2px 10px; border-radius: 20px; font-size: 10px; font-weight: 800; }

        /* ── Cashier list row ── */
        .csh-row { display: flex; align-items: center; gap: 16px; padding: 18px 20px; border-radius: 16px; border: 1.5px solid var(--pos-border); background: rgba(255,255,255,0.02); transition: border-color 0.2s, background 0.2s; margin-bottom: 12px; }
        .csh-row:hover { border-color: rgba(var(--pos-primary-rgb), 0.3); background: rgba(var(--pos-primary-rgb), 0.03); }
        .csh-row.is-inactive { opacity: 0.55; }
        .csh-info { flex: 1; min-width: 0; }
        .csh-name { font-weight: 800; font-size: 14px; color: var(--pos-text); }
        .csh-email { font-size: 12px; color: var(--pos-text-muted); margin-top: 2px; }
        .csh-meta  { font-size: 11px; color: var(--pos-text-muted); margin-top: 6px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
        .csh-actions { display: flex; gap: 8px; flex-shrink: 0; flex-wrap: wrap; justify-content: flex-end; }

        /* ── Pill toggle btn ── */
        .pill-btn { border: 1.5px solid var(--pos-border); background: transparent; color: var(--pos-text-muted); border-radius: 10px; padding: 6px 14px; font-size: 11px; font-weight: 700; cursor: pointer; transition: all 0.2s; white-space: nowrap; }
        .pill-btn:hover { border-color: var(--pos-primary); color: var(--pos-primary); }
        .pill-btn.danger:hover { border-color: var(--pos-danger); color: var(--pos-danger); }

        /* ── Permission checkboxes ── */
        .perm-group { margin-bottom: 14px; }
        .perm-group-label { font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; color: var(--pos-text-muted); margin-bottom: 8px; }
        .perm-checks { display: flex; flex-wrap: wrap; gap: 8px; }
        .perm-check-item { display: flex; align-items: center; gap: 7px; background: rgba(255,255,255,0.04); border: 1.5px solid var(--pos-border); border-radius: 10px; padding: 7px 12px; cursor: pointer; transition: all 0.18s; font-size: 12px; font-weight: 700; color: var(--pos-text-muted); user-select: none; }
        .perm-check-item:hover { border-color: rgba(99,102,241,0.4); color: var(--pos-primary); }
        .perm-check-item input[type="checkbox"] { width: 15px; height: 15px; accent-color: var(--pos-primary); cursor: pointer; flex-shrink: 0; }
        .perm-check-item.checked { border-color: rgba(99,102,241,0.5); background: rgba(99,102,241,0.08); color: var(--pos-primary); }

        /* ── Permission panel ── */
        .perm-panel { background: rgba(99,102,241,0.05); border: 1.5px solid rgba(99,102,241,0.15); border-radius: 16px; padding: 18px 20px; margin-bottom: 18px; }
        .perm-panel-title { font-size: 12px; font-weight: 900; color: var(--pos-primary); margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
        .perm-badges { display: flex; flex-wrap: wrap; gap: 6px; }
        .perm-badge-ok  { background: rgba(16,185,129,0.12); color: #10b981; border: 1px solid rgba(16,185,129,0.25); padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 800; }
        .perm-badge-miss { background: rgba(244,63,94,0.1); color: var(--pos-danger); border: 1px solid rgba(244,63,94,0.2); padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 800; }

        /* ── Warning banner ── */
        .perm-warning { display: flex; align-items: flex-start; gap: 14px; background: rgba(245,158,11,0.07); border: 1.5px solid rgba(245,158,11,0.25); border-radius: 16px; padding: 16px 18px; margin-bottom: 24px; }
        .perm-warning-icon { width: 36px; height: 36px; border-radius: 10px; background: rgba(245,158,11,0.15); display: grid; place-items: center; color: #f59e0b; font-size: 16px; flex-shrink: 0; }

        /* ── Modal ── */
        .csh-modal-backdrop { position: fixed; inset: 0; z-index: 300; background: rgba(0,0,0,0.55); backdrop-filter: blur(6px); display: none; align-items: center; justify-content: center; padding: 20px; }
        .csh-modal-backdrop.open { display: flex; }
        .csh-modal { background: var(--pos-card); border: 1.5px solid var(--pos-border); border-radius: 24px; padding: 32px; width: 100%; max-width: 420px; animation: scaleIn 0.2s ease; }
        @keyframes scaleIn { from { opacity:0; transform:scale(0.95); } to { opacity:1; transform:scale(1); } }
        .csh-modal h3 { font-size: 15px; font-weight: 900; margin-bottom: 20px; }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'cashiers'; include __DIR__ . '/partials/navbar.php'; ?>

    <?php
    $posUrl = function(string $path) use ($posBase): string {
        return $posBase . '/' . ltrim($path, '/');
    };

    // Group permissions by module
    $permsByModule = [];
    foreach ($allPermissions as $p) {
        $permsByModule[$p['module']][] = $p;
    }

    // Check if cashier role is missing critical POS permissions
    $hasPosRead  = in_array('pos:read',  array_map(fn($p) => $p['module'].':'.$p['action'], $cashierRolePerms));
    $hasPosWrite = in_array('pos:write', array_map(fn($p) => $p['module'].':'.$p['action'], $cashierRolePerms));
    $missingPosAccess = !$hasPosRead;
    ?>

    <div class="fade-in">

        <!-- ── Page Header ── -->
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:28px; flex-wrap:wrap; gap:16px;">
            <div class="pos-title">
                <h1><i class="fas fa-user-tie" style="color:var(--pos-primary); margin-right:10px;"></i>Cashier Management</h1>
                <p>Create and manage cashier accounts for your POS terminal.
                    <strong style="color:var(--pos-primary);"><?php echo $totalUsers; ?>/<?php echo $maxUsers; ?></strong> users used.
                </p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="pos-alert" style="background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.25); color:#10b981; padding:14px 20px; border-radius:14px; margin-bottom:24px; display:flex; align-items:center; gap:10px; font-weight:700;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="pos-alert" style="background:rgba(244,63,94,0.1); border:1px solid rgba(244,63,94,0.25); color:var(--pos-danger); padding:14px 20px; border-radius:14px; margin-bottom:24px; display:flex; align-items:center; gap:10px; font-weight:700;">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- ⚠️ Warning if cashier role has no POS access -->
        <?php if ($missingPosAccess && !empty($cashiers)): ?>
        <div class="perm-warning">
            <div class="perm-warning-icon"><i class="fas fa-triangle-exclamation"></i></div>
            <div style="flex:1;">
                <div style="font-weight:900; font-size:14px; color:#f59e0b; margin-bottom:4px;">Cashier Role Missing POS Permission!</div>
                <p style="font-size:12px; color:var(--pos-text-muted); margin:0 0 12px; line-height:1.6;">
                    Cashiers cannot access the POS Dashboard because the <strong>Staff</strong> role has no <code>pos → read</code> permission.
                    Click below to fix this for all <?php echo count($cashiers); ?> cashier(s).
                </p>
                <form method="POST">
                    <input type="hidden" name="_action" value="fix_permissions">
                    <button type="submit" class="btn" style="background:linear-gradient(135deg,#f59e0b,#d97706); color:white; border:none; padding:10px 22px; border-radius:12px; font-size:13px; font-weight:800; cursor:pointer;">
                        <i class="fas fa-wand-magic-sparkles" style="margin-right:8px;"></i>Fix Cashier Permissions Now
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <div class="csh-layout">

            <!-- ═══ Left: Create Form ═══ -->
            <div class="csh-form">
                <div class="pos-card pad">
                    <div style="display:flex; align-items:center; gap:14px; margin-bottom:24px;">
                        <div style="width:46px; height:46px; border-radius:16px; background:rgba(var(--pos-primary-rgb),0.12); display:grid; place-items:center; font-size:20px; color:var(--pos-primary);">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div>
                            <h3 class="pos-card-title" style="margin:0;">Create Cashier</h3>
                            <p style="font-size:12px; color:var(--pos-text-muted); margin:4px 0 0;">New POS terminal user</p>
                        </div>
                    </div>

                    <?php if (!$canCreate): ?>
                        <div style="padding:20px; background:rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.2); border-radius:14px; color:#f59e0b; font-weight:700; font-size:13px; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                            <i class="fas fa-triangle-exclamation"></i> User limit reached. Upgrade plan to add more.
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="createCashierForm">
                        <input type="hidden" name="_action" value="create">

                        <div class="pos-form-group">
                            <label class="pos-form-label"><i class="fas fa-user" style="margin-right:6px; color:var(--pos-primary);"></i>Username <span style="color:red;">*</span></label>
                            <input type="text" name="username" class="pos-form-control" placeholder="e.g. cashier_01" required <?php echo !$canCreate ? 'disabled' : ''; ?>>
                        </div>

                        <div class="pos-form-group">
                            <label class="pos-form-label"><i class="fas fa-envelope" style="margin-right:6px; color:var(--pos-primary);"></i>Email Address <span style="color:red;">*</span></label>
                            <input type="email" name="email" class="pos-form-control" placeholder="cashier@example.com" required <?php echo !$canCreate ? 'disabled' : ''; ?>>
                        </div>

                        <div class="pos-form-group">
                            <label class="pos-form-label"><i class="fas fa-lock" style="margin-right:6px; color:var(--pos-primary);"></i>Password <span style="color:red;">*</span> <span style="color:var(--pos-text-muted); font-size:11px;">(min 6 chars)</span></label>
                            <div style="position:relative;">
                                <input type="password" name="password" id="newPwd" class="pos-form-control" placeholder="••••••••" required minlength="6" <?php echo !$canCreate ? 'disabled' : ''; ?> style="padding-right:44px;">
                                <button type="button" onclick="togglePwd('newPwd',this)" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--pos-text-muted); cursor:pointer; font-size:14px; padding:0;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- ── Permissions Section ── -->
                        <div style="border-top: 1px solid var(--pos-border); margin: 20px 0 18px; padding-top: 18px;">
                            <div style="display:flex; align-items:center; gap:8px; margin-bottom:14px;">
                                <i class="fas fa-shield-halved" style="color:var(--pos-primary); font-size:14px;"></i>
                                <span style="font-size:13px; font-weight:900; color:var(--pos-text);">Permissions</span>
                                <span style="font-size:11px; color:var(--pos-text-muted); margin-left:2px;">(applied to all cashiers)</span>
                            </div>

                            <?php foreach ($permsByModule as $module => $perms): ?>
                            <div class="perm-group">
                                <div class="perm-group-label">
                                    <?php
                                    $moduleIcons = ['pos'=>'cash-register','inventory'=>'boxes-stacked','hr'=>'users'];
                                    $icon = $moduleIcons[$module] ?? 'key';
                                    echo '<i class="fas fa-' . $icon . '" style="margin-right:4px;"></i>';
                                    echo strtoupper($module);
                                    ?>
                                </div>
                                <div class="perm-checks">
                                    <?php foreach ($perms as $perm): 
                                        $isChecked = in_array($perm['id'], $cashierPermIds);
                                        // Pre-check pos read+write by default for new form
                                        $defaultCheck = ($perm['module'] === 'pos' && in_array($perm['action'], ['read','write']));
                                    ?>
                                    <label class="perm-check-item <?php echo ($isChecked || $defaultCheck) ? 'checked' : ''; ?>" id="lbl-perm-<?php echo $perm['id']; ?>">
                                        <input type="checkbox" name="permissions[]" value="<?php echo $perm['id']; ?>"
                                               <?php echo ($isChecked || $defaultCheck) ? 'checked' : ''; ?>
                                               <?php echo !$canCreate ? 'disabled' : ''; ?>
                                               onchange="togglePermLabel(this)">
                                        <i class="fas fa-<?php echo $perm['action'] === 'read' ? 'eye' : ($perm['action'] === 'write' ? 'pen' : 'trash'); ?>"></i>
                                        <?php echo htmlspecialchars($perm['action']); ?>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <button type="submit" class="btn" style="width:100%; background:var(--pos-primary); color:#fff; border:none; padding:14px; border-radius:14px; font-size:14px; font-weight:800; cursor:pointer; <?php echo !$canCreate ? 'opacity:0.5; cursor:not-allowed;' : ''; ?>" <?php echo !$canCreate ? 'disabled' : ''; ?>>
                            <i class="fas fa-plus-circle" style="margin-right:8px;"></i> Create Cashier Account
                        </button>
                    </form>
                </div>

                <!-- ── Current Cashier Role Permissions Panel ── -->
                <div class="pos-card pad" style="margin-top:20px;">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
                        <i class="fas fa-shield-halved" style="color:var(--pos-primary);"></i>
                        <h3 class="pos-card-title" style="margin:0; font-size:14px;">Current Cashier Role Permissions</h3>
                    </div>

                    <?php if (empty($cashierRolePerms)): ?>
                        <div style="text-align:center; padding:24px; color:var(--pos-danger);">
                            <i class="fas fa-ban" style="font-size:28px; opacity:0.4; display:block; margin-bottom:10px;"></i>
                            <p style="font-weight:800; font-size:13px; margin:0;">No permissions assigned!</p>
                            <p style="font-size:11px; color:var(--pos-text-muted); margin-top:6px;">Use "Fix Cashier Permissions" above.</p>
                        </div>
                    <?php else: ?>
                        <div class="perm-badges">
                            <?php foreach ($cashierRolePerms as $cp): ?>
                                <span class="perm-badge-ok">
                                    <i class="fas fa-check" style="margin-right:4px; font-size:9px;"></i>
                                    <?php echo htmlspecialchars($cp['module']); ?> &rarr; <?php echo htmlspecialchars($cp['action']); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                        <form method="POST" style="margin-top:14px;">
                            <input type="hidden" name="_action" value="fix_permissions">
                            <button type="submit" class="btn btn-outline" style="width:100%; font-size:12px; padding:10px;">
                                <i class="fas fa-arrows-rotate" style="margin-right:6px;"></i>Re-apply Permissions to Role
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ═══ Right: Cashier List ═══ -->
            <div>
                <div class="pos-card pad">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
                        <h3 class="pos-card-title" style="margin:0;"><i class="fas fa-users"></i> Cashier Accounts <span style="font-size:12px; color:var(--pos-text-muted); font-weight:600; margin-left:6px;">(<?php echo count($cashiers); ?> total)</span></h3>
                    </div>

                    <?php if (empty($cashiers)): ?>
                        <div style="padding:60px; text-align:center; color:var(--pos-text-muted);">
                            <i class="fas fa-user-slash" style="font-size:40px; opacity:0.25; display:block; margin-bottom:14px;"></i>
                            <p style="font-weight:700; font-size:14px;">No cashier accounts yet.</p>
                            <p style="font-size:12px; margin-top:6px;">Create one using the form on the left.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cashiers as $c): ?>
                            <div class="csh-row <?php echo $c['status'] !== 'active' ? 'is-inactive' : ''; ?>">
                                <div class="csh-avatar <?php echo $c['status'] !== 'active' ? 'inactive' : ''; ?>">
                                    <?php echo strtoupper(substr($c['username'], 0, 2)); ?>
                                </div>
                                <div class="csh-info">
                                    <div class="csh-name"><?php echo htmlspecialchars($c['username']); ?></div>
                                    <div class="csh-email"><?php echo htmlspecialchars($c['email']); ?></div>
                                    <div class="csh-meta">
                                        <span>Added <?php echo date('d M Y', strtotime($c['created_at'])); ?></span>
                                        &nbsp;·&nbsp;
                                        <?php echo $c['status'] === 'active'
                                            ? '<span class="badge-active"><i class="fas fa-circle" style="font-size:7px;"></i> Active</span>'
                                            : '<span class="badge-inactive"><i class="fas fa-ban" style="font-size:9px;"></i> Inactive</span>'; ?>
                                        &nbsp;·&nbsp;
                                        <?php if (!empty($cashierRolePerms)): ?>
                                            <span class="badge-perm" title="Has POS access">
                                                <i class="fas fa-shield-halved" style="font-size:9px; margin-right:3px;"></i>
                                                <?php echo count($cashierRolePerms); ?> permission<?php echo count($cashierRolePerms) !== 1 ? 's' : ''; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="perm-badge-miss" title="No permissions assigned">
                                                <i class="fas fa-ban" style="font-size:9px; margin-right:3px;"></i>No Access
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="csh-actions">
                                    <!-- Reset Password -->
                                    <button type="button" class="pill-btn"
                                        onclick="openResetModal(<?php echo (int)$c['id']; ?>, '<?php echo htmlspecialchars(addslashes($c['username'])); ?>')">
                                        <i class="fas fa-key" style="margin-right:4px;"></i>Reset
                                    </button>

                                    <!-- Toggle Active/Inactive -->
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="_action" value="toggle_status">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$c['id']; ?>">
                                        <input type="hidden" name="new_status" value="<?php echo $c['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                        <button type="submit" class="pill-btn <?php echo $c['status'] === 'active' ? 'danger' : ''; ?>">
                                            <?php if ($c['status'] === 'active'): ?>
                                                <i class="fas fa-ban" style="margin-right:4px;"></i>Disable
                                            <?php else: ?>
                                                <i class="fas fa-check" style="margin-right:4px;"></i>Enable
                                            <?php endif; ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ─── Reset Password Modal ─── -->
    <div class="csh-modal-backdrop" id="resetModal" onclick="closeResetModal()">
        <div class="csh-modal" onclick="event.stopPropagation()">
            <h3><i class="fas fa-key" style="color:var(--pos-primary); margin-right:8px;"></i>Reset Password — <span id="resetUsername" style="color:var(--pos-primary);"></span></h3>
            <form method="POST" id="resetPasswordForm">
                <input type="hidden" name="_action" value="reset_password">
                <input type="hidden" name="user_id" id="resetUserId">
                <div class="pos-form-group">
                    <label class="pos-form-label">New Password <span style="color:red;">*</span></label>
                    <div style="position:relative;">
                        <input type="password" name="new_password" id="resetPwd" class="pos-form-control" placeholder="••••••••" required minlength="6" style="padding-right:44px;">
                        <button type="button" onclick="togglePwd('resetPwd',this)" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--pos-text-muted); cursor:pointer; font-size:14px; padding:0;">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div style="display:flex; gap:10px; margin-top:24px;">
                    <button type="button" onclick="closeResetModal()" class="btn btn-outline" style="flex:1;">Cancel</button>
                    <button type="submit" class="btn" style="flex:1; background:var(--pos-primary); color:#fff; border:none;">
                        <i class="fas fa-save" style="margin-right:6px;"></i>Save Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePwd(id, btn) {
            var inp = document.getElementById(id);
            var isText = inp.type === 'text';
            inp.type = isText ? 'password' : 'text';
            btn.innerHTML = isText ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        }

        function openResetModal(userId, username) {
            document.getElementById('resetUserId').value = userId;
            document.getElementById('resetUsername').textContent = username;
            document.getElementById('resetPwd').value = '';
            document.getElementById('resetModal').classList.add('open');
        }

        function closeResetModal() {
            document.getElementById('resetModal').classList.remove('open');
        }

        function togglePermLabel(checkbox) {
            var label = checkbox.closest('.perm-check-item');
            if (checkbox.checked) {
                label.classList.add('checked');
            } else {
                label.classList.remove('checked');
            }
        }
    </script>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
