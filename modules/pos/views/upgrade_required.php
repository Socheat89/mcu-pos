<?php
require_once __DIR__ . '/../../../core/helpers/url.php';
$subdomain = Tenant::getCurrent()['subdomain'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('upgrade_required_title'); ?> - Mekong CyberUnit</title>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body, h1, h2, h3, h4, h5, h6, p, span, a, button, input, select, textarea {
            font-family: 'Battambang', sans-serif !important;
        }
        .upgrade-bg {
            min-height: calc(100vh - 120px);
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(246, 247, 251, 0.5);
            padding: 20px;
        }

        .upgrade-card {
            background: white;
            padding: 50px 40px;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(106, 92, 255, 0.1);
        }

        .upgrade-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #6a5cff, #8a3ffc);
        }

        .upgrade-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(106, 92, 255, 0.1), rgba(138, 63, 252, 0.1));
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            color: #6a5cff;
            font-size: 45px;
            transform: rotate(-5deg);
        }

        .upgrade-card h2 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 12px;
            color: #1e293b;
        }

        .upgrade-card p {
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .plan-badge {
            display: inline-flex;
            align-items: center;
            background: #f1f0ff;
            color: #6a5cff;
            padding: 6px 16px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 13px;
            margin-bottom: 25px;
            gap: 6px;
        }

        .upgrade-footer {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn-upgrade {
            background: linear-gradient(135deg, #6a5cff 0%, #8a3ffc 100%);
            color: white;
            text-decoration: none;
            padding: 16px 30px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(106, 92, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-upgrade:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(106, 92, 255, 0.3);
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #64748b;
            text-decoration: none;
            padding: 14px 30px;
            border-radius: 14px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .sparkle {
            position: absolute;
            color: #ffca28;
            font-size: 20px;
            animation: sparkle-anim 2s infinite ease-in-out;
        }

        @keyframes sparkle-anim {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.3); opacity: 1; }
        }
    </style>
</head>
<body class="pos-app">
    <?php 
    $activeNav = $activeNav ?? '';
    include __DIR__ . '/partials/navbar.php'; 
    ?>

    <div class="upgrade-bg">
        <div class="upgrade-card">
            <i class="fas fa-star sparkle" style="top: 40px; right: 50px; animation-delay: 0.2s;"></i>
            <i class="fas fa-sparkles sparkle" style="top: 100px; left: 60px;"></i>
            
            <div class="upgrade-icon">
                <i class="fas fa-crown"></i>
            </div>
            
            <div class="plan-badge">
                <i class="fas fa-shield-halved"></i> 
                <?php echo __('pro_feature'); ?>
            </div>

            <h2><?php echo __('level_up_business'); ?></h2>
            <p><?php echo __('customer_management_upgrade_msg'); ?></p>

            <div class="upgrade-footer">
                <a href="<?php echo mc_url('tenant/settings.php?section=subscription'); ?>" class="btn-upgrade">
                    <i class="fas fa-rocket"></i> <?php echo __('upgrade_to_standard'); ?>
                </a>
                <a href="<?php echo mc_url($subdomain . '/pos/dashboard'); ?>" class="btn-secondary">
                    <?php echo __('maybe_later'); ?>
                </a>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
