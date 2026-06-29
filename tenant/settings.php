<?php
// tenant/settings.php

require_once __DIR__ . '/../core/classes/Database.php';
require_once __DIR__ . '/../core/classes/Tenant.php';
require_once __DIR__ . '/../core/classes/Auth.php';
require_once __DIR__ . '/../core/classes/Settings.php';
require_once __DIR__ . '/../core/helpers/url.php';
require_once __DIR__ . '/../middleware/TenantMiddleware.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

TenantMiddleware::handle();
AuthMiddleware::handle();

$currentTenant = Tenant::getCurrent();
$subdomain = $currentTenant['subdomain'];
$urlPrefix = mc_base_path();

// Check if user has permission to manage settings
if (!Auth::isTenantAdmin()) {
    header('Location: ' . mc_url($subdomain . '/dashboard?error=' . urlencode('Access denied')));
    exit;
}

$tenantId = Tenant::getId();
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_receipt_settings'])) {
        Settings::set('receipt_show_logo', isset($_POST['receipt_show_logo']) ? '1' : '0', $tenantId);
        Settings::set('receipt_header_text', trim($_POST['receipt_header_text']), $tenantId);
        Settings::set('receipt_footer_text', trim($_POST['receipt_footer_text']), $tenantId);
        Settings::set('receipt_font_size', (int) $_POST['receipt_font_size'], $tenantId);
        Settings::set('receipt_paper_width', (int) $_POST['receipt_paper_width'], $tenantId);
        $message = 'Receipt settings updated successfully!';

        // Handle Logo Upload
        if (isset($_FILES['receipt_logo']) && $_FILES['receipt_logo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['receipt_logo'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
            
            if (in_array($file['type'], $allowedTypes)) {
                $uploadDir = __DIR__ . '/../public/uploads/logos/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $extension = 'webp';
                $filename = 'logo_' . $tenantId . '_' . time() . '.' . $extension;
                $targetPath = $uploadDir . $filename;
                
                // Create image resource based on type
                $sourceImage = null;
                switch ($file['type']) {
                    case 'image/jpeg':
                    case 'image/jpg':
                        $sourceImage = imagecreatefromjpeg($file['tmp_name']);
                        break;
                    case 'image/png':
                        $sourceImage = imagecreatefrompng($file['tmp_name']);
                        // Handle transparency for PNG
                        imagepalettetotruecolor($sourceImage);
                        imagealphablending($sourceImage, true);
                        imagesavealpha($sourceImage, true);
                        break;
                    case 'image/webp':
                        $sourceImage = imagecreatefromwebp($file['tmp_name']);
                        break;
                }

                if ($sourceImage) {
                    // Convert and save as WebP
                    // Preserve transparency if possible
                    imagepalettetotruecolor($sourceImage);
                    imagealphablending($sourceImage, true);
                    imagesavealpha($sourceImage, true);
                    
                    if (imagewebp($sourceImage, $targetPath, 80)) {
                        imagedestroy($sourceImage);
                        // Save new path to settings (relative URL)
                        Settings::set('receipt_logo_path', mc_url('public/uploads/logos/' . $filename), $tenantId);
                    } else {
                        $error = "Failed to save WebP image.";
                    }
                } else {
                    $error = "Failed to process image.";
                }
            } else {
                $error = "Invalid file type. Only JPG, PNG, and WebP are allowed.";
            }
        }
    } elseif (isset($_POST['update_company_info'])) {
        Settings::set('company_address', trim($_POST['company_address']), $tenantId);
        Settings::set('company_phone', trim($_POST['company_phone']), $tenantId);
        Settings::set('company_email', trim($_POST['company_email']), $tenantId);
        Settings::set('company_tax_id', trim($_POST['company_tax_id']), $tenantId);
        Settings::set('company_website', trim($_POST['company_website']), $tenantId);
        $message = 'Company information updated successfully!';
    } elseif (isset($_POST['update_payment_settings'])) {
        // Save default payment method
        Settings::set('default_payment_method', $_POST['default_payment_method'] ?? 'cash', $tenantId);
        
        // Save enabled payment methods (as JSON array)
        $enabledMethods = [];
        if (isset($_POST['enable_cash'])) $enabledMethods[] = 'cash';
        if (isset($_POST['enable_qr'])) $enabledMethods[] = 'qr';
        if (isset($_POST['enable_card'])) $enabledMethods[] = 'card';
        
        Settings::set('enabled_payment_methods', json_encode($enabledMethods), $tenantId);
        
        // Save QR payment provider preference
        Settings::set('qr_payment_provider', $_POST['qr_payment_provider'] ?? 'bakong', $tenantId);
        
        $message = 'Payment settings updated successfully!';
    }
}

// Get current settings
$settings = Settings::getAll($tenantId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?php echo htmlspecialchars($currentTenant['name']); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6a5cff;
            --primary-dark: #5648d4;
            --secondary: #8a3ffc;
            --accent: #2dd4ff;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg: #f6f7fb;
            --card-bg: #ffffff;
            --text: #1e293b;
            --text-muted: #64748b;
            --border: rgba(30, 41, 59, 0.08);
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 15px 40px rgba(0, 0, 0, 0.12);
        }

        * { 
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: "Battambang", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            background: 
                radial-gradient(900px 600px at 15% -10%, rgba(106, 92, 255, 0.15), transparent 60%),
                radial-gradient(900px 600px at 110% 10%, rgba(138, 63, 252, 0.12), transparent 60%),
                var(--bg);
            color: var(--text);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        
        .navbar-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 72px;
        }
        
        .nav-brand {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .nav-brand i {
            font-size: 1.75rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
            gap: 8px;
            align-items: center;
        }
        
        .nav-links a {
            color: var(--text);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 10px 18px;
            border-radius: 10px;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .nav-links a:hover {
            background: rgba(106, 92, 255, 0.08);
            color: var(--primary);
        }
        
        .nav-links a.active {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }
        
        .nav-links .logout-btn {
            background: rgba(239, 68, 68, 0.08);
            color: var(--danger);
        }
        
        .nav-links .logout-btn:hover {
            background: var(--danger);
            color: white;
        }

        /* Language Switcher */
        .lang-switcher {
            position: relative;
            display: inline-block;
            margin-left: 10px;
        }
        
        .lang-btn {
            background: rgba(106, 92, 255, 0.08);
            color: var(--primary);
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        
        .lang-btn:hover {
            background: var(--primary);
            color: white;
        }
        
        .lang-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            padding-top: 15px; /* Bridge gap */
            display: none;
            z-index: 1100;
        }

        .lang-dropdown-inner {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            border: 1px solid var(--border);
            min-width: 160px;
            overflow: hidden;
        }
        
        .lang-switcher:hover .lang-dropdown,
        .lang-switcher.active .lang-dropdown {
            display: block;
        }
        
        .lang-dropdown a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            text-decoration: none;
            color: var(--text);
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .lang-dropdown a:hover {
            background: rgba(106, 92, 255, 0.05);
            color: var(--primary);
        }

        .lang-dropdown a.active {
            color: var(--primary);
            font-weight: 700;
            background: rgba(106, 92, 255, 0.08);
        }

        /* Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 32px;
        }

        /* Welcome Header */
        .welcome-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 48px;
            border-radius: 20px;
            margin-bottom: 32px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .welcome-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .welcome-content { z-index: 1; }
        
        .welcome-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 8px;
        }
        
        .welcome-header p {
            font-size: 1.1rem;
            opacity: 0.95;
        }

        /* Tabs */
        .tabs { 
            background: var(--card-bg); 
            border-radius: 16px; 
            box-shadow: var(--shadow); 
            border: 1px solid var(--border);
            overflow: hidden; 
        }
        
        .tab-buttons { 
            display: flex; 
            background: rgba(0,0,0,0.02); 
            border-bottom: 1px solid var(--border); 
            padding: 0 16px;
        }
        
        .tab-button { 
            padding: 16px 24px; 
            border: none; 
            background: none; 
            cursor: pointer; 
            font-size: 0.95rem; 
            font-weight: 600; 
            color: var(--text-muted); 
            border-bottom: 3px solid transparent; 
            transition: all 0.3s ease; 
            display: flex;
            align-items: center;
            gap: 8px;
            opacity: 0.7;
        }
        
        .tab-button:hover { 
            color: var(--primary); 
            background: linear-gradient(to top, rgba(106, 92, 255, 0.05), transparent);
            opacity: 1;
        }
        
        .tab-button.active { 
            color: var(--primary); 
            border-bottom-color: var(--primary); 
            background: white; 
            border-radius: 8px 8px 0 0;
            box-shadow: 0 -4px 10px rgba(0,0,0,0.02);
            opacity: 1;
        }

        .tab-content { 
            padding: 32px; 
            display: none; 
            animation: fadeIn 0.3s ease;
        }
        
        .tab-content.active { display: block; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Forms */
        .form-section {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 32px;
        }

        .form-left {
            grid-column: span 7;
        }

        .form-right {
            grid-column: span 5;
        }

        @media (max-width: 1024px) {
            .form-left, .form-right { grid-column: span 12; }
        }

        .form-group { margin-bottom: 24px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text); font-size: 0.9rem; }
        
        input, select, textarea { 
            width: 100%; 
            padding: 12px 16px; 
            border: 2px solid var(--border); 
            border-radius: 10px; 
            font-size: 0.95rem;
            background: var(--bg);
            transition: all 0.2s;
            color: var(--text);
            font-family: inherit;
        }
        
        input:focus, select:focus, textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 4px rgba(106, 92, 255, 0.1);
            background: white;
        }
        
        textarea { resize: vertical; min-height: 100px; }

        .checkbox-group { 
            display: flex; 
            align-items: center; 
            margin-bottom: 24px; 
            padding: 16px;
            background: var(--bg);
            border-radius: 10px;
            border: 2px solid var(--border);
            cursor: pointer;
            transition: all 0.2s;
        }

        .checkbox-group:hover { border-color: var(--primary); }
        
        .checkbox-group input { 
            width: auto; 
            margin-right: 12px; 
            transform: scale(1.2); 
            accent-color: var(--primary);
        }
        
        .checkbox-group label { margin-bottom: 0; cursor: pointer; }

        .btn {
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            text-align: center;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: none;
            cursor: pointer;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(106, 92, 255, 0.3);
        }

        /* Messages */
        .message { padding: 16px; margin-bottom: 24px; border-radius: 12px; display: flex; align-items: center; gap: 12px; }
        .success { background: rgba(16, 185, 129, 0.1); color: var(--success); border: 1px solid rgba(16, 185, 129, 0.2); }
        .error { background: rgba(239, 68, 68, 0.1); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.2); }

        /* Preview Area */
        .preview-container {
            position: sticky;
            top: 100px;
        }

        .preview { 
            border: none; 
            padding: 24px; 
            background: white; 
            font-family: 'Courier New', monospace; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            border-radius: 2px;
            margin: 0 auto;
            position: relative;
        }
        
        .preview:before {
            content: '';
            position: absolute;
            top: -5px;
            left: 0;
            right: 0;
            height: 5px;
            background: radial-gradient(circle, transparent 0.25em, #fff 0.26em) top left / 1em 1em;
            background-repeat: repeat-x;
            transform: rotate(180deg);
        }
        
        .preview:after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            right: 0;
            height: 5px;
            background: radial-gradient(circle, transparent 0.25em, #fff 0.26em) bottom left / 1em 1em;
            background-repeat: repeat-x;
        }

        /* Headers */
        h3 {
            font-size: 1.5rem;
            color: var(--text);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .preview-header {
            margin-bottom: 16px;
            color: var(--text);
            font-weight: 700;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="nav-brand">
                <i class="fas fa-cube"></i> <?php echo htmlspecialchars($currentTenant['name']); ?> Admin
            </div>
            <ul class="nav-links">
                <li><a href="<?php echo $urlPrefix; ?>/<?php echo $subdomain; ?>/dashboard"><i class="fas fa-chart-line"></i> <?php echo __('dashboard'); ?></a></li>
                <li><a href="<?php echo $urlPrefix; ?>/<?php echo $subdomain; ?>/users"><i class="fas fa-users"></i> <?php echo __('profile'); ?></a></li>
                <li><a href="<?php echo $urlPrefix; ?>/<?php echo $subdomain; ?>/settings" class="active"><i class="fas fa-cog"></i> <?php echo __('settings'); ?></a></li>
                <li><a href="<?php echo $urlPrefix; ?>/<?php echo $subdomain; ?>/logout" class="logout-btn"><i class="fas fa-sign-out-alt"></i> <?php echo __('logout'); ?></a></li>
                <li class="lang-switcher" id="langSwitcher">
                    <button class="lang-btn" onclick="toggleLangDropdown(event)">
                        <i class="fas fa-globe"></i>
                        <?php 
                        $curr = Language::getCurrentLang();
                        echo $curr == 'en' ? 'English' : ($curr == 'km' ? 'ភាសាខ្មែរ' : '中文');
                        ?>
                        <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i>
                    </button>
                    <div class="lang-dropdown">
                        <div class="lang-dropdown-inner">
                            <a href="<?php echo mc_url('public/set_lang.php?lang=en'); ?>" class="<?php echo $curr == 'en' ? 'active' : ''; ?>">
                                <img src="https://flagcdn.com/w20/gb.png" width="20" alt="English"> English
                            </a>
                            <a href="<?php echo mc_url('public/set_lang.php?lang=km'); ?>" class="<?php echo $curr == 'km' ? 'active' : ''; ?>">
                                <img src="https://flagcdn.com/w20/kh.png" width="20" alt="Khmer"> ភាសាខ្មែរ
                            </a>
                            <a href="<?php echo mc_url('public/set_lang.php?lang=zh'); ?>" class="<?php echo $curr == 'zh' ? 'active' : ''; ?>">
                                <img src="https://flagcdn.com/w20/cn.png" width="20" alt="Chinese"> 中文
                            </a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-header">
            <div class="welcome-content">
                <h1><?php echo __('system_settings'); ?></h1>
                <p>Configure your tenant details and system preferences</p>
            </div>
            <a href="<?php echo $urlPrefix; ?>/<?php echo $subdomain; ?>/dashboard" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3);">
                <i class="fas fa-arrow-left"></i> <?php echo __('back_to_dashboard'); ?>
            </a>
        </div>

        <?php if ($message): ?>
            <div class="message success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="tabs">
            <div class="tab-buttons">
                <button class="tab-button active" onclick="openTab('company')"><i class="fas fa-building"></i> <?php echo __('company_info'); ?></button>
                <button class="tab-button" onclick="openTab('receipt')"><i class="fas fa-receipt"></i> <?php echo __('receipt_design'); ?></button>
                <button class="tab-button" onclick="openTab('payment')"><i class="fas fa-credit-card"></i> <?php echo __('payment_methods'); ?></button>
            </div>

            <div id="company" class="tab-content active">
                <h3><i class="fas fa-building" style="color: var(--primary);"></i> Company Details</h3>
                <form method="POST">
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> <?php echo __('company_address'); ?></label>
                        <textarea name="company_address" placeholder="Enter full business address"><?php echo htmlspecialchars($settings['company_address'] ?? ''); ?></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> <?php echo __('phone_number'); ?></label>
                            <input type="text" name="company_phone" value="<?php echo htmlspecialchars($settings['company_phone'] ?? ''); ?>" placeholder="+855 12 345 678">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> <?php echo __('email_address'); ?></label>
                            <input type="email" name="company_email" value="<?php echo htmlspecialchars($settings['company_email'] ?? ''); ?>" placeholder="contact@company.com">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label><i class="fas fa-file-invoice-dollar"></i> <?php echo __('tax_id'); ?></label>
                            <input type="text" name="company_tax_id" value="<?php echo htmlspecialchars($settings['company_tax_id'] ?? ''); ?>" placeholder="TIN-123456789">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-globe"></i> <?php echo __('website'); ?></label>
                            <input type="url" name="company_website" value="<?php echo htmlspecialchars($settings['company_website'] ?? ''); ?>" placeholder="https://www.example.com">
                        </div>
                    </div>

                    <button type="submit" name="update_company_info" class="btn"><i class="fas fa-save"></i> <?php echo __('save_settings'); ?></button>
                </form>
            </div>

            <div id="receipt" class="tab-content">
                <div class="form-section">
                    <div class="form-left">
                        <h3><i class="fas fa-sliders-h" style="color: var(--primary);"></i> Configuration</h3>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="checkbox-group">
                                <input type="checkbox" name="receipt_show_logo" id="receipt_show_logo" value="1" <?php echo ($settings['receipt_show_logo'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                <label for="receipt_show_logo">Show company logo on receipt</label>
                            </div>

                            <div class="form-group">
                                <label>Upload Logo</label>
                                <input type="file" name="receipt_logo" accept="image/jpeg,image/png,image/webp">
                                <small style="color: var(--text-muted); display: block; margin-top: 5px;">Supported: JPG, PNG, WebP. Converted to WebP automatically.</small>
                            </div>

                            <?php if (!empty($settings['receipt_logo_path'])): ?>
                                <div class="form-group">
                                    <label>Current Logo</label>
                                    <div style="background: #f9f9f9; padding: 10px; border: 1px dashed #ddd; display: inline-block; border-radius: 8px;">
                                        <img src="<?php echo htmlspecialchars($settings['receipt_logo_path']); ?>" alt="Current Logo" style="max-height: 50px; display: block;">
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label>Header Text</label>
                                <input type="text" name="receipt_header_text" value="<?php echo htmlspecialchars($settings['receipt_header_text'] ?? 'Point of Sale Receipt'); ?>" placeholder="Header displayed below logo">
                            </div>

                            <div class="form-group">
                                <label>Footer Text</label>
                                <textarea name="receipt_footer_text" placeholder="Thank you message or policy info"><?php echo htmlspecialchars($settings['receipt_footer_text'] ?? 'Thank you for your business!'); ?></textarea>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div class="form-group">
                                    <label>Font Size (px)</label>
                                    <input type="number" name="receipt_font_size" value="<?php echo htmlspecialchars($settings['receipt_font_size'] ?? '12'); ?>" min="8" max="16">
                                </div>

                                <div class="form-group">
                                    <label>Paper Width (px)</label>
                                    <input type="number" name="receipt_paper_width" value="<?php echo htmlspecialchars($settings['receipt_paper_width'] ?? '400'); ?>" min="300" max="600">
                                </div>
                            </div>

                            <button type="submit" name="update_receipt_settings" class="btn"><i class="fas fa-save"></i> Save Receipt Settings</button>
                        </form>
                    </div>
                    
                    <div class="form-right">
                        <div class="preview-container">
                            <div class="preview-header"><i class="fas fa-eye"></i> Live Preview</div>
                            <div class="preview" style="max-width: <?php echo ($settings['receipt_paper_width'] ?? '400'); ?>px; font-size: <?php echo ($settings['receipt_font_size'] ?? '12'); ?>px;">
                                <div style="text-align: center; margin-bottom: 10px; border-bottom: 1px dashed #000; padding-bottom: 5px;">
                                    <?php if (($settings['receipt_show_logo'] ?? '1') === '1'): ?>
                                        <?php if (!empty($settings['receipt_logo_path'])): ?>
                                            <div style="margin-bottom: 5px;">
                                                <img src="<?php echo htmlspecialchars($settings['receipt_logo_path']); ?>" alt="Logo" style="max-width: 80%; max-height: 80px;">
                                            </div>
                                        <?php else: ?>
                                            <div style="font-size: 1.2em; font-weight: bold; margin-bottom: 5px;"><i class="fas fa-store"></i> [LOGO]</div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <div style="font-weight: bold; font-size: 1.1em;"><?php echo htmlspecialchars($currentTenant['name']); ?></div>
                                    <div style="margin: 5px 0;"><?php echo htmlspecialchars($settings['receipt_header_text'] ?? 'Point of Sale Receipt'); ?></div>
                                    <div>Order #12345</div>
                                </div>

                                <?php if (!empty($settings['company_address']) || !empty($settings['company_phone']) || !empty($settings['company_email']) || !empty($settings['company_tax_id']) || !empty($settings['company_website'])): ?>
                                <div style="text-align: center; margin-bottom: 10px; font-size: 0.9em; line-height: 1.4;">
                                    <?php if (!empty($settings['company_address'])): ?>
                                        <div><?php echo htmlspecialchars($settings['company_address']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($settings['company_phone'])): ?>
                                        <div>Tel: <?php echo htmlspecialchars($settings['company_phone']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($settings['company_email'])): ?>
                                        <div><?php echo htmlspecialchars($settings['company_email']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($settings['company_tax_id'])): ?>
                                        <div>VAT: <?php echo htmlspecialchars($settings['company_tax_id']); ?></div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>

                                <div style="margin-bottom: 10px; display: flex; justify-content: space-between; font-size: 0.9em;">
                                    <div><?php echo date('d/m/Y H:i'); ?></div>
                                    <div>Customer: <strong>Walk-in</strong></div>
                                </div>

                                <div style="margin-bottom: 10px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px; padding-bottom: 2px; border-bottom: 1px dotted #ccc; font-weight: bold;">
                                        <span style="flex: 2;">Item</span>
                                        <span style="flex: 1; text-align: center;">Qty</span>
                                        <span style="flex: 1; text-align: right;">Total</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                        <span style="flex: 2;">Iced Latte</span>
                                        <span style="flex: 1; text-align: center;">2</span>
                                        <span style="flex: 1; text-align: right;">$6.00</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                        <span style="flex: 2;">Blueberry Muffin</span>
                                        <span style="flex: 1; text-align: center;">1</span>
                                        <span style="flex: 1; text-align: right;">$2.50</span>
                                    </div>
                                </div>

                                <div style="border-top: 1px solid #000; padding-top: 5px; margin-top: 10px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                                        <span>Subtotal:</span>
                                        <span>$8.50</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.1em; margin-top: 5px;">
                                        <span>TOTAL:</span>
                                        <span>$8.50</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; font-size: 0.9em; margin-top: 5px;">
                                        <span>Cash:</span>
                                        <span>$10.00</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; font-size: 0.9em;">
                                        <span>Change:</span>
                                        <span>$1.50</span>
                                    </div>
                                </div>

                                <div style="text-align: center; margin-top: 15px; border-top: 1px dashed #000; padding-top: 10px; font-style: italic;">
                                    <div><?php echo nl2br(htmlspecialchars($settings['receipt_footer_text'] ?? 'Thank you for your business!')); ?></div>
                                </div>
                                <div style="text-align: center; margin-top: 15px;">
                                    <div style="background: #000; height: 30px; width: 80%; margin: 0 auto;"></div>
                                    <div style="font-size: 10px; margin-top: 2px;">1234567890</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="payment" class="tab-content">
                <h3><i class="fas fa-credit-card" style="color: var(--primary);"></i> Payment Method Configuration</h3>
                <p style="color: var(--text-muted); margin-bottom: 2rem;">Configure available payment methods for your POS system. These settings control which payment options are available during checkout.</p>
                
                <form method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px; margin-bottom: 32px;">
                        <!-- Left Column: Enable/Disable Methods -->
                        <div>
                            <h4 style="margin-bottom: 1rem; color: var(--text); font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-toggle-on" style="color: var(--primary);"></i> Available Payment Methods
                            </h4>
                            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">Select which payment methods should be available at checkout</p>
                            
                            <?php
                            $enabledMethods = json_decode($settings['enabled_payment_methods'] ?? '["cash","qr","card"]', true);
                            ?>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" name="enable_cash" id="enable_cash" value="1" <?php echo in_array('cash', $enabledMethods) ? 'checked' : ''; ?>>
                                <label for="enable_cash">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <i class="fas fa-money-bill-wave" style="color: #10b981; font-size: 1.2rem;"></i>
                                        <div>
                                            <div style="font-weight: 600;">Cash Payment</div>
                                            <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 400;">Traditional cash transactions</div>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <div class="checkbox-group">
                                <input type="checkbox" name="enable_qr" id="enable_qr" value="1" <?php echo in_array('qr', $enabledMethods) ? 'checked' : ''; ?>>
                                <label for="enable_qr">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <i class="fas fa-qrcode" style="color: #E31E26; font-size: 1.2rem;"></i>
                                        <div>
                                            <div style="font-weight: 600;">QR Code Payment</div>
                                            <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 400;">Bakong or ACLEDA QR payments</div>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <div class="checkbox-group">
                                <input type="checkbox" name="enable_card" id="enable_card" value="1" <?php echo in_array('card', $enabledMethods) ? 'checked' : ''; ?>>
                                <label for="enable_card">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <i class="fas fa-credit-card" style="color: #005494; font-size: 1.2rem;"></i>
                                        <div>
                                            <div style="font-weight: 600;">Card Payment</div>
                                            <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 400;">Credit/Debit card transactions</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Right Column: Default & Provider Settings -->
                        <div>
                            <h4 style="margin-bottom: 1rem; color: var(--text); font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-star" style="color: var(--warning);"></i> Default Settings
                            </h4>
                            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">Configure default payment preferences</p>
                            
                            <div class="form-group">
                                <label><i class="fas fa-check-circle"></i> Default Payment Method</label>
                                <select name="default_payment_method" style="padding: 12px 16px; border: 2px solid var(--border); border-radius: 10px; font-size: 0.95rem; background: var(--bg); width: 100%;">
                                    <option value="cash" <?php echo ($settings['default_payment_method'] ?? 'cash') === 'cash' ? 'selected' : ''; ?>>Cash</option>
                                    <option value="qr" <?php echo ($settings['default_payment_method'] ?? 'cash') === 'qr' ? 'selected' : ''; ?>>QR Code</option>
                                    <option value="card" <?php echo ($settings['default_payment_method'] ?? 'cash') === 'card' ? 'selected' : ''; ?>>Card</option>
                                </select>
                                <small style="color: var(--text-muted); display: block; margin-top: 5px;">This method will be pre-selected at checkout</small>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-qrcode"></i> QR Payment Provider</label>
                                <select name="qr_payment_provider" style="padding: 12px 16px; border: 2px solid var(--border); border-radius: 10px; font-size: 0.95rem; background: var(--bg); width: 100%;">
                                    <option value="bakong" <?php echo ($settings['qr_payment_provider'] ?? 'bakong') === 'bakong' ? 'selected' : ''; ?>>
                                        Bakong (Dynamic QR)
                                    </option>
                                    <option value="acleda" <?php echo ($settings['qr_payment_provider'] ?? 'bakong') === 'acleda' ? 'selected' : ''; ?>>
                                        ACLEDA Bank (Static QR)
                                    </option>
                                </select>
                                <small style="color: var(--text-muted); display: block; margin-top: 5px;">Choose your preferred QR code payment gateway</small>
                            </div>

                            <!-- Info Box -->
                            <div style="margin-top: 2rem; padding: 1.5rem; background: linear-gradient(135deg, rgba(106, 92, 255, 0.05) 0%, rgba(138, 63, 252, 0.05) 100%); border: 1px solid rgba(106, 92, 255, 0.2); border-radius: 12px;">
                                <div style="display: flex; align-items: start; gap: 12px;">
                                    <i class="fas fa-info-circle" style="color: var(--primary); font-size: 1.5rem; margin-top: 2px;"></i>
                                    <div>
                                        <h5 style="margin: 0 0 8px 0; color: var(--primary); font-size: 1rem;">Professional Plan Feature</h5>
                                        <p style="margin: 0; font-size: 0.9rem; color: var(--text); line-height: 1.5;">
                                            Payment method configuration is available on the <strong>Professional ($50/mo)</strong> and <strong>Enterprise ($100/mo)</strong> plans. 
                                            Customize your checkout experience to match your business needs.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="update_payment_settings" class="btn"><i class="fas fa-save"></i> Save Payment Settings</button>
                </form>
            </div>
        </div>

        <script>
            function openTab(tabName) {
                // Hide all tab contents
                const tabContents = document.querySelectorAll('.tab-content');
                tabContents.forEach(content => content.classList.remove('active'));

                // Remove active class from all tab buttons
                const tabButtons = document.querySelectorAll('.tab-button');
                tabButtons.forEach(button => button.classList.remove('active'));

                // Show the selected tab content
                document.getElementById(tabName).classList.add('active');

                // Add active class to clicked button
                event.target.classList.add('active');
            }
        </script>
    </div>
    <script id="langToggleScript">
        function toggleLangDropdown(e) {
            e.stopPropagation();
            document.getElementById('langSwitcher').classList.toggle('active');
        }
        
        document.addEventListener('click', function(e) {
            const switcher = document.getElementById('langSwitcher');
            if (switcher && !switcher.contains(e.target)) {
                switcher.classList.remove('active');
            }
        });
    </script>
</body>
</html>
