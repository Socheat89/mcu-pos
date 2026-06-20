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
        .csh-layout { display: grid; grid-template-columns: 380px 1fr; gap: 28px; align-items: start; }
        @media (max-width: 1024px) { .csh-layout { grid-template-columns: 1fr; } }

        /* ── Form card ── */
        .csh-form { position: sticky; top: 24px; }

        /* ── Avatar ring ── */
        .csh-avatar { width: 40px; height: 40px; border-radius: 14px; background: var(--pos-primary-light); display: grid; place-items: center; font-size: 16px; font-weight: 900; color: var(--pos-primary); border: 1.5px solid rgba(var(--pos-primary-rgb), 0.2); flex-shrink: 0; }
        .csh-avatar.inactive { background: rgba(255,255,255,0.04); color: var(--pos-text-muted); border-color: var(--pos-border); }

        /* ── Status badge ── */
        .badge-active   { background: rgba(16,185,129,0.12); color: #10b981; border: 1px solid rgba(16,185,129,0.25); padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 800; }
        .badge-inactive { background: rgba(244,63,94,0.1); color: var(--pos-danger); border: 1px solid rgba(244,63,94,0.2); padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 800; }

        /* ── Cashier list row ── */
        .csh-row { display: flex; align-items: center; gap: 16px; padding: 18px 20px; border-radius: 16px; border: 1.5px solid var(--pos-border); background: rgba(255,255,255,0.02); transition: border-color 0.2s, background 0.2s; margin-bottom: 12px; }
        .csh-row:hover { border-color: rgba(var(--pos-primary-rgb), 0.3); background: rgba(var(--pos-primary-rgb), 0.03); }
        .csh-row.is-inactive { opacity: 0.55; }
        .csh-info { flex: 1; min-width: 0; }
        .csh-name { font-weight: 800; font-size: 14px; color: var(--pos-text); }
        .csh-email { font-size: 12px; color: var(--pos-text-muted); margin-top: 2px; }
        .csh-meta  { font-size: 11px; color: var(--pos-text-muted); margin-top: 4px; }
        .csh-actions { display: flex; gap: 8px; flex-shrink: 0; flex-wrap: wrap; justify-content: flex-end; }

        /* ── Pill toggle btn ── */
        .pill-btn { border: 1.5px solid var(--pos-border); background: transparent; color: var(--pos-text-muted); border-radius: 10px; padding: 6px 14px; font-size: 11px; font-weight: 700; cursor: pointer; transition: all 0.2s; white-space: nowrap; }
        .pill-btn:hover { border-color: var(--pos-primary); color: var(--pos-primary); }
        .pill-btn.danger:hover { border-color: var(--pos-danger); color: var(--pos-danger); }

        /* ── Modal ── */
        .csh-modal-backdrop { position: fixed; inset: 0; z-index: 300; background: rgba(0,0,0,0.55); backdrop-filter: blur(6px); display: none; align-items: center; justify-content: center; padding: 20px; }
        .csh-modal-backdrop.open { display: flex; }
        .csh-modal { background: var(--pos-card); border: 1.5px solid var(--pos-border); border-radius: 24px; padding: 32px; width: 100%; max-width: 400px; animation: scaleIn 0.2s ease; }
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
    ?>

    <div class="fade-in">

        <!-- ── Page Header ── -->
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:32px; flex-wrap:wrap; gap:16px;">
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

        <div class="csh-layout">

            <!-- ═══ Left: Create Form ═══ -->
            <div class="csh-form">
                <div class="pos-card pad">
                    <div style="display:flex; align-items:center; gap:14px; margin-bottom:28px;">
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

                        <button type="submit" class="btn" style="width:100%; background:var(--pos-primary); color:#fff; border:none; padding:14px; border-radius:14px; font-size:14px; font-weight:800; cursor:pointer; <?php echo !$canCreate ? 'opacity:0.5; cursor:not-allowed;' : ''; ?>" <?php echo !$canCreate ? 'disabled' : ''; ?>>
                            <i class="fas fa-plus-circle" style="margin-right:8px;"></i> Create Cashier Account
                        </button>
                    </form>
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
                                        Added <?php echo date('d M Y', strtotime($c['created_at'])); ?>
                                        &nbsp;·&nbsp;
                                        <?php echo $c['status'] === 'active'
                                            ? '<span class="badge-active"><i class="fas fa-circle" style="font-size:7px;"></i> Active</span>'
                                            : '<span class="badge-inactive"><i class="fas fa-ban" style="font-size:9px;"></i> Inactive</span>'; ?>
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
    </script>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
