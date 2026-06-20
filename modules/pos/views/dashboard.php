<?php
require_once __DIR__ . '/../../../core/helpers/url.php';
$stats = $stats ?? [];
$recentOrders = $recentOrders ?? [];
$salesByMonth = $salesByMonth ?? [];
$topProducts = $topProducts ?? [];
$lowStockItems = $lowStockItems ?? [];
$subscriptionPlans = $subscriptionPlans ?? [];
$subscriptionExpiringSoon = $subscriptionExpiringSoon ?? false;

$tenant = class_exists('Tenant') ? (Tenant::getCurrent() ?? []) : [];
$tenantName = is_array($tenant) && !empty($tenant['name']) ? $tenant['name'] : 'Tenant';
$tenantSlug = is_array($tenant) && !empty($tenant['subdomain']) ? $tenant['subdomain'] : '';

$urlPrefix = mc_base_path();

$subscriptionManageUrl = $tenantSlug
    ? $urlPrefix . '/' . $tenantSlug . '/settings'
    : $urlPrefix . '/tenant/settings.php';
$subscriptionManageUrl .= (strpos($subscriptionManageUrl, '?') === false ? '?' : '&') . 'section=subscription';
$subscriptionPricingUrl = $urlPrefix . '/public/pricing.php';

$fmtMoney = function($value): string {
    return '$' . number_format((float)$value, 2);
};

$labels = array_keys($salesByMonth ?? []);
$values = array_values($salesByMonth ?? []);

$friendlyLabels = [];
foreach ($labels as $ym) {
    if (!$ym) continue;
    $dt = DateTime::createFromFormat('Y-m', $ym);
    if ($dt) {
        $friendlyLabels[] = $ym; // We'll format this in JS for better localization
    } else {
        $friendlyLabels[] = $ym;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('dashboard'); ?> - <?php echo htmlspecialchars($tenantName); ?></title>
    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        .dashboard-hero {
            background: var(--pos-gradient-dark);
            border-radius: var(--pos-radius-xl);
            padding: 48px;
            color: white;
            margin-bottom: 32px;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--pos-border);
            box-shadow: var(--pos-shadow-lg), var(--pos-shadow-glow);
            display: flex;
<?php
require_once __DIR__ . '/../../../core/helpers/url.php';
$stats = $stats ?? [];
$recentOrders = $recentOrders ?? [];
$salesByMonth = $salesByMonth ?? [];
$topProducts = $topProducts ?? [];
$lowStockItems = $lowStockItems ?? [];
$subscriptionPlans = $subscriptionPlans ?? [];
$subscriptionExpiringSoon = $subscriptionExpiringSoon ?? false;

$tenant = class_exists('Tenant') ? (Tenant::getCurrent() ?? []) : [];
$tenantName = is_array($tenant) && !empty($tenant['name']) ? $tenant['name'] : 'Tenant';
$tenantSlug = is_array($tenant) && !empty($tenant['subdomain']) ? $tenant['subdomain'] : '';

$urlPrefix = mc_base_path();

$subscriptionManageUrl = $tenantSlug
    ? $urlPrefix . '/' . $tenantSlug . '/settings'
    : $urlPrefix . '/tenant/settings.php';
$subscriptionManageUrl .= (strpos($subscriptionManageUrl, '?') === false ? '?' : '&') . 'section=subscription';
$subscriptionPricingUrl = $urlPrefix . '/public/pricing.php';

$fmtMoney = function($value): string {
    return '$' . number_format((float)$value, 2);
};

$labels = array_keys($salesByMonth ?? []);
$values = array_values($salesByMonth ?? []);

$friendlyLabels = [];
foreach ($labels as $ym) {
    if (!$ym) continue;
    $dt = DateTime::createFromFormat('Y-m', $ym);
    if ($dt) {
        $friendlyLabels[] = $ym; // We'll format this in JS for better localization
    } else {
        $friendlyLabels[] = $ym;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('dashboard'); ?> - <?php echo htmlspecialchars($tenantName); ?></title>
    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        .dashboard-hero {
            background: var(--pos-gradient-dark);
            border-radius: var(--pos-radius-xl);
            padding: 48px;
            color: white;
            margin-bottom: 32px;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--pos-border);
            box-shadow: var(--pos-shadow-lg), var(--pos-shadow-glow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .dashboard-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 30% 40%, rgba(var(--pos-primary-rgb), 0.12) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(var(--pos-primary-rgb), 0.08) 0%, transparent 40%);
            pointer-events: none;
        }
        .dashboard-hero::after {
            content: ''; position: absolute; top: -50%; right: -20%; width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(var(--pos-primary-rgb), 0.15) 0%, transparent 65%); border-radius: 50%; pointer-events: none;
        }
        .dashboard-hero h1 { font-size: 40px; font-weight: 800; margin: 0; line-height: 1.1; letter-spacing: -1px; font-family: 'Space Grotesk', sans-serif; position: relative; z-index: 1; }
        .dashboard-hero p { color: rgba(255,255,255,0.5); font-size: 16px; margin: 12px 0 28px; font-weight: 500; max-width: 500px; position: relative; z-index: 1; }
        
        .notification-card {
            display: flex; gap: 16px; padding: 18px; background: var(--pos-card); backdrop-filter: blur(12px); border-radius: var(--pos-radius);
            border: 1px solid var(--pos-border); margin-bottom: 12px; transition: all 0.3s ease;
        }
        .notification-card:hover { transform: translateY(-3px); border-color: rgba(var(--pos-primary-rgb), 0.15); box-shadow: var(--pos-shadow-md); }
        .notification-icon { width: 48px; height: 48px; border-radius: 12px; display: grid; place-items: center; flex-shrink: 0; font-size: 18px; }

        .leaderboard-item { display: flex; align-items: center; gap: 14px; padding: 14px; border-radius: var(--pos-radius); background: var(--pos-card); border: 1px solid var(--pos-border); transition: all 0.25s ease; }
        .leaderboard-item:hover { border-color: rgba(var(--pos-primary-rgb), 0.15); transform: translateX(6px); box-shadow: var(--pos-shadow-sm); }
        .rank-number { width: 38px; height: 38px; border-radius: 10px; background: var(--pos-primary-light); border: 1px solid rgba(var(--pos-primary-rgb), 0.15); display: grid; place-items: center; font-weight: 800; color: var(--pos-primary); font-size: 14px; }
        .subscription-card .subscription-helper { color: var(--pos-text-muted); font-size: 12px; margin-top: 6px; font-weight: 600; }
        .subscription-card .subscription-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 20px; }
        .subscription-list { display: flex; flex-direction: column; gap: 14px; margin-bottom: 16px; }
        .subscription-plan { border: 1px solid var(--pos-border); border-radius: var(--pos-radius); padding: 18px; background: var(--pos-card); transition: all 0.25s ease; }
        .subscription-plan:hover { border-color: rgba(var(--pos-primary-rgb), 0.15); box-shadow: var(--pos-shadow-sm); }
        .subscription-plan .plan-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 14px; }
        .plan-name { font-weight: 800; font-size: 15px; margin: 0; color: var(--pos-text); }
        .plan-meta { font-size: 11px; color: var(--pos-text-muted); font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; }
        .plan-stats { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .plan-stats .stat { background: rgba(255,255,255,0.02); border: 1px dashed var(--pos-border); border-radius: 12px; padding: 12px 14px; display: flex; flex-direction: column; gap: 6px; }
        .plan-stats .stat span { font-size: 10px; font-weight: 700; color: var(--pos-text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .plan-stats .stat strong { font-size: 17px; font-weight: 800; color: var(--pos-text); font-family: 'Space Grotesk', sans-serif; }
        .subscription-empty { border: 1.5px dashed var(--pos-border); border-radius: var(--pos-radius); padding: 32px 20px; background: rgba(255,255,255,0.02); display: flex; flex-direction: column; align-items: center; gap: 12px; text-align: center; }
        .subscription-empty i { font-size: 36px; color: var(--pos-text-dim); }
        .subscription-empty p { margin: 0; }
        .subscription-empty-hint { font-size: 12px; color: var(--pos-text-muted); font-weight: 600; }
        .subscription-cta { padding: 10px 24px; font-size: 13px; font-weight: 700; border-radius: 999px; }
        .subscription-manage-btn { width: 100%; justify-content: center; border-radius: 14px; font-weight: 700; padding: 12px 24px; }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'dashboard'; include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in">
        <!-- Dashboard Hero -->
        <div class="dashboard-hero">
            <div style="position: relative; z-index: 2;">
                <h1><?php echo __('dashboard'); ?></h1>
                        tooltip: { 
                            padding: 14, 
                            cornerRadius: 12, 
                            backgroundColor: '#141830',
                            borderColor: 'rgba(255,255,255,0.06)',
                            borderWidth: 1,
                            titleColor: '#f1f5f9',
                            bodyColor: '#94a3b8',
                            bodyFont: { size: 13, weight: 'bold' } 
                        } 
                    },
                    scales: {
                        x: { 
                            grid: { display: false }, 
                            ticks: { font: { weight: '700', size: 11 }, color: '#64748b' },
                            border: { display: false }
                        },
                        y: { 
                            beginAtZero: true, 
                            grid: { color: 'rgba(255,255,255,0.04)', drawBorder: false }, 
                            ticks: { 
                                font: { weight: '700', size: 11 }, 
                                color: '#64748b',
                                callback: function(value) { return '$' + value; }
                            },
                            border: { display: false }
                        }
                    }
                }
            });
        })();
    </script>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
