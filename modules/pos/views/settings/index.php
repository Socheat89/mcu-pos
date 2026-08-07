<?php
require_once __DIR__ . '/../../../../core/helpers/url.php';
require_once __DIR__ . '/../../../../core/classes/Database.php';
// modules/pos/views/settings/index.php
$pageTitle = __('settings');

$tenantId = Tenant::getId();
$db = Database::getInstance();
$telegramConfig = $db->fetchOne("SELECT * FROM tenant_telegram_config WHERE tenant_id = ?", [$tenantId]) ?: [];
$subdomain = Tenant::getCurrent()['subdomain'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('settings'); ?> - <?php echo htmlspecialchars(Tenant::getCurrent()['name']); ?></title>

    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Modernized Styles for Premium POS Settings */
        .pos-form-group { margin-bottom: 24px; }
        .pos-form-label { display: block; margin-bottom: 10px; font-weight: 800; color: var(--pos-text); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        .pos-form-control { 
            width: 100%; 
            padding: 14px 18px; 
            border: 1.5px solid var(--pos-border); 
            border-radius: 16px; 
            font-size: 15px; 
            font-weight: 600;
            color: var(--pos-text);
            background: #ffffff;

            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
        }
        .pos-form-control:focus { 
            border-color: var(--pos-primary); 
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(var(--pos-primary-rgb), 0.15);

        }
        
        .pos-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 32px;
            padding: 6px;
            background: #eaecef;
            border-radius: 18px;
            width: fit-content;
            border: 1px solid var(--pos-border);

        }
        
        .pos-tab-link {
            padding: 12px 24px;
            border-radius: 14px;
            font-weight: 800;
            cursor: pointer;
            color: var(--pos-text-muted);
            transition: all 0.25s;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }
        
        .pos-tab-link:hover { color: var(--pos-text); }
        .pos-tab-link.active { 
            background: rgba(var(--pos-primary-rgb), 0.15); 
            color: var(--pos-primary); 
            border: 1px solid rgba(var(--pos-primary-rgb), 0.25);

            box-shadow: var(--pos-shadow-sm);
        }
        
        .tab-content { display: none; animation: fadeIn 0.4s ease-out; }
        .tab-content.active { display: block; }
        
        .user-list { 
            display: grid;
            gap: 12px;
            max-height: 480px; 
            overflow-y: auto; 
            padding-right: 10px;
        }
        .user-card {
            display: flex;
            align-items: center;
            padding: 16px;
            border: 1.5px solid var(--pos-border);
            border-radius: 18px;
            background: var(--pos-card);
            transition: all 0.2s;
            color: var(--pos-text);
        }
        .user-card:hover { transform: translateY(-2px); border-color: var(--pos-primary); box-shadow: var(--pos-shadow-md), var(--pos-shadow-glow); }
        
        .pos-small { font-size: 12px; color: var(--pos-text-muted); font-weight: 600; }
        .pos-card-sub { font-size: 14px; color: var(--pos-text-muted); font-weight: 500; margin-bottom: 24px; }
        
        .pos-badge { display: inline-block; padding: 4px 8px; border-radius: 8px; font-size: 11px; font-weight: 700; }


        /* Toggle Switch Premium */
        .pos-toggle { position: relative; display: inline-block; width: 48px; height: 26px; }
        .pos-toggle input { opacity: 0; width: 0; height: 0; }
        .pos-toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #e5e7eb; transition: .3s; border-radius: 34px; border: 1px solid var(--pos-border); }
        .pos-toggle-slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.3); }
        input:checked + .pos-toggle-slider { background-color: var(--pos-primary); border-color: var(--pos-primary); }
        input:checked + .pos-toggle-slider:before { transform: translateX(22px); }
        
        .preview-pane { 
            background: #f3f4f6; 

            padding: 40px; 
            border-radius: 24px; 
            display: flex; 
            justify-content: center; 
            align-items: flex-start; 
            min-height: 600px;
            border: 1.5px solid var(--pos-border);
            box-shadow: none;

        }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'settings'; include __DIR__ . '/../partials/navbar.php'; ?>
    
    <div class="pos-row" style="margin-bottom: 32px; align-items: flex-end;">
        <div class="pos-title">
            <h1><?php echo __('settings'); ?></h1>

            <p>Configure ecosystem preferences and security policy</p>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
       <script>
       document.addEventListener('DOMContentLoaded', function() {
           if(window.POSUI) window.POSUI.toast({type: 'success', title: 'Settings Synchronized', message: 'Your hardware and access preferences have been updated.'});
       });
       </script>
    <?php endif; ?>

    <form action="<?php echo mc_url($subdomain . '/pos/settings/update'); ?>" method="POST" enctype="multipart/form-data">
        
        <div class="pos-card pad" style="margin-bottom: 40px; border-radius: 28px;">
            <div class="pos-tabs">
                <div class="pos-tab-link active" onclick="switchTab('business', this)">
                    <i class="fas fa-building"></i> ព័ត៌មានអាជីវកម្ម
                </div>
                <div class="pos-tab-link" onclick="switchTab('users', this)">
                    <i class="fas fa-shield-halved"></i> User Access
                </div>
                <?php if (Tenant::getPosLevel() >= 2): ?>
                <div class="pos-tab-link" onclick="switchTab('receipt', this)">
                    <i class="fas fa-file-invoice"></i> Receipt Design
                </div>
                <?php endif; ?>
                <div class="pos-tab-link" onclick="switchTab('payment', this)">
                    <i class="fas fa-credit-card"></i> Pay Methods
                </div>
                <div class="pos-tab-link" onclick="switchTab('general', this)">
                    <i class="fas fa-cog"></i> <?php echo __('general'); ?>
                </div>
                <div class="pos-tab-link" onclick="switchTab('telegram', this)">
                    <i class="fab fa-telegram-plane" style="color: #0088cc;"></i> Telegram Setup
                </div>
            </div>

            <!-- Business Info Tab -->
            <div id="tab-business" class="tab-content active">
                <div class="pos-grid cols-2">
                    <div>
                        <p class="pos-card-title"><i class="fas fa-building" style="color:var(--pos-primary);margin-right:8px;"></i>ព័ត៌មានអាជីវកម្ម / Business Information</p>
                        <p class="pos-card-sub" style="margin-bottom:20px;">ដាក់ព័ត៌មានអំពីអាជីវកម្មរបស់អ្នក ដែលនឹងបង្ហាញលើ Receipt និងទូទៅ</p>

                        <div class="pos-form-group">
                            <label class="pos-form-label"><i class="fas fa-store" style="color:var(--pos-primary);"></i> ឈ្មោះអាជីវកម្ម / Business Name</label>
                            <input type="text" name="business_name" class="pos-form-control" 
                                value="<?php echo htmlspecialchars($settings['business_name'] ?? ''); ?>" 
                                placeholder="e.g. My Cafe, Super Mart...">
                        </div>

                        <!-- Business Type Selector -->
                        <div class="pos-form-group">
                            <label class="pos-form-label"><i class="fas fa-tags" style="color:var(--pos-primary);"></i> ប្រភេទអាជីវកម្ម / Business Type</label>
                            <p class="pos-small" style="margin-bottom:12px;">ជ្រើសរើសប្រភេទអាជីវកម្ម — នឹងប្ដូររចនាសម្ព័ន្ធ menu, product, ingredients ដោយស្វ័យប្រវត្តិ</p>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <?php $bizType = $settings['business_type'] ?? 'coffee'; ?>
                                <label style="cursor:pointer;">
                                    <input type="radio" name="business_type" value="coffee" <?php echo $bizType === 'coffee' ? 'checked' : ''; ?> style="display:none;" id="biz_coffee">
                                    <div class="biz-type-card" id="biz_coffee_card" onclick="selectBizType('coffee')" style="border:2px solid <?php echo $bizType==='coffee'?'var(--pos-primary)':'var(--pos-border)'; ?>; border-radius:16px; padding:20px; text-align:center; transition:all 0.2s; background:<?php echo $bizType==='coffee'?'rgba(var(--pos-primary-rgb),0.06)':'#fff'; ?>;">
                                        <div style="font-size:32px; margin-bottom:8px;">☕</div>
                                        <div style="font-weight:800; font-size:14px; color:var(--pos-text);">Coffee / Cafe</div>
                                        <div style="font-size:11px; color:var(--pos-text-muted); margin-top:4px;">Sizes, Ingredients, Full POS</div>
                                    </div>
                                </label>
                                <label style="cursor:pointer;">
                                    <input type="radio" name="business_type" value="mart" <?php echo $bizType === 'mart' ? 'checked' : ''; ?> style="display:none;" id="biz_mart">
                                    <div class="biz-type-card" id="biz_mart_card" onclick="selectBizType('mart')" style="border:2px solid <?php echo $bizType==='mart'?'var(--pos-primary)':'var(--pos-border)'; ?>; border-radius:16px; padding:20px; text-align:center; transition:all 0.2s; background:<?php echo $bizType==='mart'?'rgba(var(--pos-primary-rgb),0.06)':'#fff'; ?>;">
                                        <div style="font-size:32px; margin-bottom:8px;">🛒</div>
                                        <div style="font-weight:800; font-size:14px; color:var(--pos-text);">Mart / Shop</div>
                                        <div style="font-size:11px; color:var(--pos-text-muted); margin-top:4px;">Stock In-Out, No Sizes</div>
                                    </div>
                                </label>
                            </div>
                            <div id="biz_type_notice" style="margin-top:12px; padding:10px 14px; border-radius:12px; font-size:12px; font-weight:700; <?php echo $bizType==='mart' ? 'background:rgba(245,158,11,0.1); color:#d97706; border:1px solid rgba(245,158,11,0.3);' : 'background:rgba(var(--pos-primary-rgb),0.07); color:var(--pos-primary); border:1px solid rgba(var(--pos-primary-rgb),0.2);'; ?>">
                                <?php if ($bizType === 'mart'): ?>🛒 Mart Mode: Ingredients & Sizes columns ត្រូវបានលាក់ show only Stock In-Out
                                <?php else: ?>☕ Coffee/Cafe Mode: មុខងារពេញលេញ — Ingredients, Sizes, Sessions, Reports
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="pos-form-group">
                            <label class="pos-form-label"><i class="fas fa-image" style="color:var(--pos-primary);"></i> Logo អាជីវកម្ម / Business Logo</label>
                            <div style="display:flex; align-items:center; gap:14px; margin-bottom:8px;">
                                <?php if (!empty($settings['business_logo_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($settings['business_logo_path']); ?>" alt="Business Logo" style="width:64px;height:64px;object-fit:cover;border-radius:12px;border:1px solid var(--pos-border);">
                                <?php else: ?>
                                    <div style="width:64px;height:64px;border-radius:12px;border:1.5px dashed var(--pos-border);display:grid;place-items:center;color:var(--pos-text-muted);font-size:22px;">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="business_logo_upload" class="pos-form-control" accept="image/*" style="padding:8px; max-width:260px;">
                            </div>
                            <div class="pos-small">Recommended: PNG, max 200×200px, transparent background</div>
                        </div>
                    </div>

                    <div style="background:rgba(99,102,241,0.04); border-radius:20px; padding:28px; border:1.5px dashed rgba(99,102,241,0.2);">
                        <p class="pos-card-title" style="margin-bottom:16px;"><i class="fas fa-circle-info" style="color:var(--pos-primary);"></i> Business Type Guide</p>

                        <div style="display:grid; gap:12px;">
                            <div style="background:#fff; border-radius:14px; padding:16px; border:1px solid var(--pos-border);">
                                <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                                    <span style="font-size:20px;">☕</span>
                                    <span style="font-weight:800; color:var(--pos-text);">Coffee / Cafe Mode</span>
                                </div>
                                <ul style="margin:0; padding-left:18px; font-size:12px; color:var(--pos-text-muted); line-height:1.8; font-weight:600;">
                                    <li>Product Sizes (S/M/L)</li>
                                    <li>Ingredients management</li>
                                    <li>Full POS session features</li>
                                    <li>Sales & session reports</li>
                                    <li>Stock In/Out tracking</li>
                                </ul>
                            </div>
                            <div style="background:#fff; border-radius:14px; padding:16px; border:1px solid var(--pos-border);">
                                <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                                    <span style="font-size:20px;">🛒</span>
                                    <span style="font-weight:800; color:var(--pos-text);">Mart / Shop Mode</span>
                                </div>
                                <ul style="margin:0; padding-left:18px; font-size:12px; color:var(--pos-text-muted); line-height:1.8; font-weight:600;">
                                    <li>Stock In/Out control only</li>
                                    <li>Pic, Product, Status columns</li>
                                    <li>Cost & Price columns</li>
                                    <li>No Sizes / Ingredients shown</li>
                                    <li>Simplified product table</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Control Tab -->
            <div id="tab-users" class="tab-content">
                <div class="pos-grid cols-2">
                    <div>
                        <p class="pos-card-title">Authorized Users</p>
                        <p class="pos-card-sub" style="margin-bottom: 15px;">Select users who can access the POS interface.</p>
                        
                        <div class="user-list">
                            <?php 
                            $allowedUsers = json_decode($settings['pos_allowed_users'] ?? '[]', true);
                            if (!is_array($allowedUsers)) $allowedUsers = [];
                            
                            foreach ($users as $user): 
                                $isChecked = in_array($user['id'], $allowedUsers) ? 'checked' : '';
                            ?>
                            <label class="user-card" style="cursor: pointer;">
                                <div style="margin-right: 12px;">
                                    <input type="checkbox" name="pos_allowed_users[]" value="<?php echo $user['id']; ?>" <?php echo $isChecked; ?> style="width: 18px; height: 18px; cursor: pointer;">
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 700; font-size: 14px;"><?php echo htmlspecialchars($user['username']); ?></div>
                                    <div class="pos-small"><?php echo htmlspecialchars($user['email']); ?></div>
                                </div>
                                <div>
                                    <span class="pos-badge warn" style="background: rgba(106, 92, 255, 0.1); color: rgb(86, 72, 235);">
                                        <?php echo htmlspecialchars($user['role_name'] ?? 'User'); ?>
                                    </span>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div style="background: rgba(99, 102, 241, 0.04); border-radius: 20px; padding: 28px; border: 1.5px dashed rgba(99, 102, 241, 0.2);">
                        <div style="display: flex; gap: 12px; margin-bottom: 16px; align-items: center;">
                            <div style="width: 40px; height: 40px; border-radius: 12px; background: var(--pos-primary); color: white; display: grid; place-items: center; font-size: 18px;">
                                <i class="fas fa-shield-check"></i>
                            </div>
                            <span style="font-weight: 800; color: var(--pos-text); font-size: 16px;">Security Policy</span>
                        </div>
                        <p class="pos-small" style="line-height: 1.7; font-size: 13px;">
                            Only users selected here will be authorized to access the POS terminal. 
                            Unauthorized members will be restricted from entering transactions.
                            <br><br>
                            <strong style="color: var(--pos-primary);">Pro Tip:</strong> Super Admins always retain core access, but explicit selection is recommended for clear auditing.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Receipt Design Tab -->
            <?php if (Tenant::getPosLevel() >= 2): ?>
            <div id="tab-receipt" class="tab-content">
                <div class="pos-grid cols-2">
                    <div>
                        <p class="pos-card-title" style="margin-bottom: 15px;">Configuration</p>
                        
                        <div class="pos-card" style="padding: 15px; margin-bottom: 20px; border-color: var(--pos-border);">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                                <span class="pos-form-label" style="margin:0;">Show Logo on Receipt</span>
                                <label class="pos-toggle">
                                    <input type="checkbox" name="receipt_show_logo" id="receipt_show_logo" <?php echo ($settings['receipt_show_logo'] == '1') ? 'checked' : ''; ?>>
                                    <span class="pos-toggle-slider"></span>
                                </label>
                            </div>

                            <div id="logo-upload-group" style="<?php echo ($settings['receipt_show_logo'] != '1') ? 'display:none;' : ''; ?>">
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="file" name="logo_upload" class="pos-form-control" accept="image/*" style="padding: 8px;">
                                    <?php if (!empty($settings['receipt_logo_path'])): ?>
                                        <div style="width: 40px; height: 40px; border-radius: 8px; border: 1px solid #ddd; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                            <img src="<?php echo htmlspecialchars($settings['receipt_logo_path']); ?>" alt="Current" style="max-width: 100%; max-height: 100%;">
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="pos-small" style="margin-top: 5px;">Recommended: Black & white, max height 100px.</div>
                            </div>
                        </div>

                        <div class="pos-form-group">
                            <label class="pos-form-label">Header Text</label>
                            <textarea name="receipt_header_text" class="pos-form-control" rows="2" placeholder="e.g. Store Name, Welcome"><?php echo htmlspecialchars($settings['receipt_header_text']); ?></textarea>
                        </div>

                        <div class="pos-form-group">
                            <label class="pos-form-label">Footer Text</label>
                            <textarea name="receipt_footer_text" class="pos-form-control" rows="3" placeholder="e.g. Thank you, No Returns"><?php echo htmlspecialchars($settings['receipt_footer_text']); ?></textarea>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="pos-form-group">
                                <label class="pos-form-label">Paper Width (px)</label>
                                <input type="number" name="receipt_paper_width" class="pos-form-control" value="<?php echo htmlspecialchars($settings['receipt_paper_width']); ?>">
                            </div>
                            <div class="pos-form-group">
                                <label class="pos-form-label">Font Size (px)</label>
                                <input type="number" name="receipt_font_size" class="pos-form-control" value="<?php echo htmlspecialchars($settings['receipt_font_size']); ?>">
                            </div>
                        </div>

                        <hr style="border: 0; border-top: 1px solid var(--pos-border); margin: 20px 0;">
                        
                        <p class="pos-card-title" style="margin-bottom: 15px;">Company Details</p>
                        
                        <div class="pos-form-group">
                            <label class="pos-form-label">Address</label>
                            <input type="text" name="company_address" class="pos-form-control" value="<?php echo htmlspecialchars($settings['company_address']); ?>" placeholder="123 Main St...">
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="pos-form-group">
                                <label class="pos-form-label">Phone</label>
                                <input type="text" name="company_phone" class="pos-form-control" value="<?php echo htmlspecialchars($settings['company_phone']); ?>">
                            </div>
                            <div class="pos-form-group">
                                <label class="pos-form-label">Tax ID / VAT</label>
                                <input type="text" name="company_tax_id" class="pos-form-control" value="<?php echo htmlspecialchars($settings['company_tax_id']); ?>">
                            </div>
                        </div>
                        
                        <div class="pos-form-group">
                            <label class="pos-form-label">Website / Email</label>
                            <input type="text" name="company_email" class="pos-form-control" value="<?php echo htmlspecialchars($settings['company_email']); ?>" placeholder="contact@example.com">
                        </div>
                    </div>
                    
                    <div>
                        <p class="pos-card-title" style="margin-bottom: 15px;">Dynamic Preview</p>
                        <div class="preview-pane">
                            <style>
                                .preview-receipt { 
                                    background: white; 
                                    padding: 32px; 
                                    font-family: 'Courier New', Courier, monospace; 
                                    box-shadow: 0 20px 40px rgba(0,0,0,0.4); 
                                    line-height: 1.5;
                                    color: #000;
                                }
                            </style>
                            <div class="preview-receipt" id="receipt-box" style="width: <?php echo ($settings['receipt_paper_width'] ? $settings['receipt_paper_width'].'px' : '300px'); ?>; font-size: <?php echo ($settings['receipt_font_size'] ? $settings['receipt_font_size'].'px' : '12px'); ?>;">
                                <div style="text-align: center; padding-bottom: 10px; border-bottom: 1px dashed #000; mb-3">
                                    <div id="preview-logo-container" style="<?php echo ($settings['receipt_show_logo'] != '1') ? 'display:none;' : ''; ?>; margin-bottom: 10px;">
                                        <img id="preview-logo-img" src="<?php echo !empty($settings['receipt_logo_path']) ? htmlspecialchars($settings['receipt_logo_path']) : 'https://via.placeholder.com/150x50?text=LOGO'; ?>" style="max-width: 80%; max-height: 50px;">
                                    </div>
                                    <h2 style="margin: 5px 0; font-size: 1.4em; font-weight: bold;"><?php echo htmlspecialchars(Tenant::getCurrent()['name']); ?></h2>
                                    <p id="preview-header" style="margin: 5px 0;"><?php echo nl2br(htmlspecialchars($settings['receipt_header_text'])); ?></p>
                                    <div style="margin-top: 10px; font-size: 0.9em;">
                                        <div id="preview-address"><?php echo htmlspecialchars($settings['company_address']); ?></div>
                                        <div id="preview-contact">
                                            <?php 
                                            $contact = [];
                                            if ($settings['company_phone']) $contact[] = $settings['company_phone'];
                                            if ($settings['company_email']) $contact[] = $settings['company_email'];
                                            echo implode(' | ', array_map('htmlspecialchars', $contact)); 
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div style="margin: 10px 0;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                        <span>Date:</span>
                                        <span><?php echo date('d/m/Y H:i'); ?></span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                        <span>Receipt #:</span>
                                        <span>00123</span>
                                    </div>
                                </div>
                                
                                <div style="border-bottom: 1px solid #000; padding-bottom: 5px; font-weight: bold; display: flex;">
                                    <span style="flex: 2;">Item</span>
                                    <span style="flex: 1; text-align: center;">Qty</span>
                                    <span style="flex: 1; text-align: right;">Total</span>
                                </div>
                                
                                <div style="padding: 5px 0; border-bottom: 1px dotted #ccc;">
                                    <div style="display: flex;">
                                        <span style="flex: 2;">Espresso</span>
                                        <span style="flex: 1; text-align: center;">1</span>
                                        <span style="flex: 1; text-align: right;">$2.50</span>
                                    </div>
                                </div>
                                <div style="padding: 5px 0; border-bottom: 1px dotted #ccc;">
                                    <div style="display: flex;">
                                        <span style="flex: 2;">Cappuccino</span>
                                        <span style="flex: 1; text-align: center;">2</span>
                                        <span style="flex: 1; text-align: right;">$7.00</span>
                                    </div>
                                </div>
                                
                                <div style="margin-top: 10px; border-top: 1px solid #000; padding-top: 5px;">
                                    <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.1em;">
                                        <span>TOTAL</span>
                                        <span>$9.50</span>
                                    </div>
                                    <div style="font-size: 0.9em; margin-top: 5px;">
                                        <div style="display: flex; justify-content: space-between;">
                                            <span>Cash</span>
                                            <span>$20.00</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between;">
                                            <span>Change</span>
                                            <span>$10.50</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div style="text-align: center; margin-top: 15px; border-bottom: 1px dashed #000; padding-bottom: 15px;">
                                    <p id="preview-footer" style="white-space: pre-wrap; margin: 0;"><?php echo htmlspecialchars($settings['receipt_footer_text']); ?></p>
                                </div>
                                
                                <div style="text-align: center; margin-top: 20px;">
                                    <!-- Barcode Mock -->
                                    <div style="height: 30px; background: repeating-linear-gradient(90deg, #000, #000 2px, #fff 2px, #fff 4px); width: 150px; margin: 0 auto; opacity: 0.8;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Payment Methods Tab -->
            <div id="tab-payment" class="tab-content">
                <div class="pos-grid cols-2">
                    <div>
                        <p class="pos-card-title">Enable Payment Methods</p>
                        <p class="pos-card-sub" style="margin-bottom: 20px;">Choose which payment options are available during checkout.</p>

                        <div style="display: grid; gap: 15px;">
                            <label class="pos-card" style="padding: 15px; display: flex; align-items: center; justify-content: space-between; border-color: var(--pos-border); cursor: pointer;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--pos-border); display: grid; place-items: center; color: var(--pos-primary);"><i class="fas fa-money-bill-wave"></i></div>
                                    <div>
                                        <div style="font-weight: 700;">Cash Payment</div>
                                        <div class="pos-small">Accept physical currency</div>
                                    </div>
                                </div>
                                <label class="pos-toggle">
                                    <input type="checkbox" name="pos_method_cash_enabled" <?php echo ($settings['pos_method_cash_enabled'] == '1') ? 'checked' : ''; ?>>
                                    <span class="pos-toggle-slider"></span>
                                </label>
                            </label>

                            <label class="pos-card" style="padding: 15px; display: flex; align-items: center; justify-content: space-between; border-color: var(--pos-border); cursor: pointer;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2); display: grid; place-items: center; color: var(--pos-secondary);"><i class="fas fa-credit-card"></i></div>
                                    <div>
                                        <div style="font-weight: 700;">Credit / Debit Card</div>
                                        <div class="pos-small">Visa, Mastercard, etc.</div>
                                    </div>
                                </div>
                                <label class="pos-toggle">
                                    <input type="checkbox" name="pos_method_card_enabled" <?php echo ($settings['pos_method_card_enabled'] == '1') ? 'checked' : ''; ?>>
                                    <span class="pos-toggle-slider"></span>
                                </label>
                            </label>

                            <label class="pos-card" style="padding: 15px; display: flex; align-items: center; justify-content: space-between; border-color: var(--pos-border); cursor: pointer;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); display: grid; place-items: center; color: var(--pos-success);"><i class="fas fa-university"></i></div>
                                    <div>
                                        <div style="font-weight: 700;">Bank Transfer</div>
                                        <div class="pos-small">Direct bank-to-bank transfer</div>
                                    </div>
                                </div>
                                <label class="pos-toggle">
                                    <input type="checkbox" name="pos_method_transfer_enabled" <?php echo ($settings['pos_method_transfer_enabled'] == '1') ? 'checked' : ''; ?>>
                                    <span class="pos-toggle-slider"></span>
                                </label>
                            </label>
                        </div>
                    </div>

                    <!-- Custom Payment Methods Manager -->
                    <div style="background: var(--pos-card); backdrop-filter: blur(12px); border: 1px solid var(--pos-border); border-radius: 16px; padding: 24px;">
                        <p class="pos-card-title"><i class="fas fa-plus-circle" style="color: var(--pos-primary);"></i> Custom Payment Methods</p>
                        <p class="pos-card-sub" style="margin-bottom: 20px;">Add bank or e-wallet names that cashiers can select during checkout (e.g. ABA, ACLEDA, Wing).</p>

                        <?php
                        $rawMethods = $settings['pos_custom_methods'] ?? '[]';
                        $customMethods = json_decode($rawMethods, true);
                        if (!is_array($customMethods)) $customMethods = [];
                        ?>

                        <!-- Current methods list -->
                        <div id="custom-methods-list" style="display: flex; flex-wrap: wrap; gap: 10px; min-height: 48px; margin-bottom: 20px;">
                            <?php foreach ($customMethods as $method): ?>
                            <div class="custom-method-tag" style="display: flex; align-items: center; gap: 8px; background: rgba(99,102,241,0.12); border: 1px solid rgba(99,102,241,0.25); border-radius: 10px; padding: 7px 12px; font-weight: 700; font-size: 13px; color: var(--pos-text);">
                                <i class="fas fa-university" style="font-size: 11px; color: var(--pos-secondary);"></i>
                                <span><?php echo htmlspecialchars($method); ?></span>
                                <button type="button" onclick="removeCustomMethod(this)" data-method="<?php echo htmlspecialchars($method); ?>"
                                    style="background: none; border: none; cursor: pointer; color: var(--pos-danger); font-size: 13px; line-height: 1; padding: 0; margin-left: 2px;" title="Remove">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            </div>
                            <?php endforeach; ?>
                            <?php if (empty($customMethods)): ?>
                            <div id="no-methods-hint" style="color: var(--pos-text-muted); font-size: 13px; font-style: italic; align-self: center;">No custom methods yet. Add one below.</div>
                            <?php endif; ?>
                        </div>

                        <!-- Hidden input to submit the JSON -->
                        <input type="hidden" name="pos_custom_methods" id="pos-custom-methods-input" value="<?php echo htmlspecialchars($rawMethods); ?>">

                        <!-- Add new method -->
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="text" id="new-method-input" placeholder="e.g. ABA, Wing, Bakong..."
                                style="flex: 1; background: var(--pos-elevated); border: 1px solid var(--pos-border); border-radius: 10px; padding: 10px 14px; color: var(--pos-text); font-size: 14px; font-weight: 600; outline: none;"
                                maxlength="40"
                                onkeydown="if(event.key==='Enter'){event.preventDefault();addCustomMethod();}">
                            <button type="button" onclick="addCustomMethod()"
                                style="background: var(--pos-primary); color: white; border: none; border-radius: 10px; padding: 10px 18px; font-weight: 700; font-size: 13px; cursor: pointer; white-space: nowrap; transition: opacity 0.2s;"
                                onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>

                        <div style="margin-top: 16px; display: flex; gap: 10px; background: rgba(245, 158, 11, 0.08); padding: 12px 14px; border-radius: 10px; border: 1px solid rgba(245, 158, 11, 0.18); color: var(--pos-warning); align-items: flex-start;">
                            <i class="fas fa-lightbulb" style="font-size: 15px; margin-top: 1px;"></i>
                            <p class="pos-small" style="color: var(--pos-warning); margin: 0; line-height: 1.5;">
                                These methods appear as selectable buttons in the POS checkout screen. Save settings after adding or removing methods.
                            </p>
                        </div>
                    </div>
                </div>
            </div>


            <!-- General Settings Tab (Exchange Rate) -->
            <div id="tab-general" class="tab-content">
                <div style="max-width: 700px; margin: 0 auto;">
                    <p class="pos-card-title"><i class="fas fa-cog"></i> <?php echo __('general_settings'); ?></p>
                    <p class="pos-card-sub" style="margin-bottom: 24px;"><?php echo __('general_settings_hint'); ?></p>

                    <div style="background: var(--pos-elevated); border: 1px solid var(--pos-border); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
                        <div class="pos-form-group" style="margin-bottom: 0;">
                            <label class="pos-form-label">
                                <i class="fas fa-money-bill-wave" style="color: var(--pos-primary);"></i> 
                                <?php echo __('exchange_rate'); ?> (USD → KHR)
                            </label>
                            <p class="pos-small" style="margin-bottom: 10px;">
                                <?php echo __('exchange_rate_hint'); ?>
                            </p>
                            <div style="display: flex; align-items: center; gap: 12px; max-width: 400px;">
                                <span style="font-weight: 800; font-size: 16px; color: var(--pos-text);">1 USD =</span>
                                <input type="number" name="exchange_rate_usd_khr" step="1" class="pos-form-control" 
                                    value="<?php echo htmlspecialchars($settings['exchange_rate_usd_khr'] ?? '4100'); ?>" 
                                    style="max-width: 200px; text-align: center; font-size: 20px; font-weight: 900;" required>
                                <span style="font-weight: 800; font-size: 16px; color: var(--pos-text);">៛ KHR</span>
                            </div>
                            <?php $rate = (float)($settings['exchange_rate_usd_khr'] ?? 4100); ?>
                            <div style="margin-top: 12px; font-size: 12px; color: var(--pos-text-muted); font-weight: 600;">
                                <?php echo __('exchange_rate_example'); ?>: $1.00 = <?php echo number_format($rate, 0); ?>៛ &nbsp;|&nbsp; 
                                $5.00 = <?php echo number_format(5 * $rate, 0); ?>៛ &nbsp;|&nbsp; 
                                $10.00 = <?php echo number_format(10 * $rate, 0); ?>៛
                            </div>
                        </div>

                        <hr style="border: 0; border-top: 1px solid var(--pos-border); margin: 24px 0;">

                        <div class="pos-form-group" style="margin-bottom: 0;">
                            <label class="pos-form-label">
                                <i class="fas fa-coins" style="color: var(--pos-primary);"></i> 
                                <?php echo __('price_decimal_places'); ?>
                            </label>
                            <p class="pos-small" style="margin-bottom: 10px;">
                                <?php echo __('price_decimal_places_hint'); ?>
                            </p>
                            <select name="price_decimal_places" class="pos-form-control pos-form-select" style="max-width: 250px; font-weight: 700; height: auto; padding: 10px 14px;">
                                <?php 
                                $currentDec = (int)($settings['price_decimal_places'] ?? 2);
                                for ($i = 0; $i <= 4; $i++): 
                                    $example = '0';
                                    if ($i > 0) {
                                        $example .= '.' . str_repeat('0', $i);
                                    }
                                ?>
                                    <option value="<?php echo $i; ?>" <?php echo $currentDec === $i ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> (<?php echo $example; ?>)
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Telegram Setup Tab -->
            <div id="tab-telegram" class="tab-content">
                <div style="max-width: 900px; margin: 0 auto;">
                    <p class="pos-card-title"><i class="fab fa-telegram-plane" style="color: #0088cc;"></i> ការកំណត់ Telegram / Telegram Setup</p>
                    <p class="pos-card-sub" style="margin-bottom: 24px;">Connect Telegram Bot to get session open/close notifications, sales report, and GPS tracking updates in your group.</p>

                    <?php $isConnected = !empty($telegramConfig['chat_id']); ?>

                    <!-- Connection Card -->
                    <div style="background: var(--pos-elevated); border: 1px solid var(--pos-border); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
                        <?php if ($isConnected): ?>
                            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                                <div style="display: flex; align-items: center; gap: 16px;">
                                    <div style="width: 50px; height: 50px; background: rgba(16, 185, 129, 0.15); border-radius: 50%; display: grid; place-items: center; color: #10b981; font-size: 22px;">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div>
                                        <div style="font-size: 1.1rem; font-weight: 800; color: var(--pos-text); margin-bottom: 2px;">✅ បានភ្ជាប់ Telegram ដោយជោគជ័យ!</div>
                                        <div style="font-size: 0.9rem; color: var(--pos-text-muted);">
                                            ក្រុម Telegram៖ <strong style="color: #0088cc;"><?php echo htmlspecialchars($telegramConfig['chat_title'] ?? 'Telegram Group'); ?></strong>
                                        </div>
                                    </div>
                                </div>
                                <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 6px 14px; border-radius: 20px; font-weight: 700; font-size: 0.85rem; border: 1px solid rgba(16, 185, 129, 0.3);">
                                    🟢 Active / ដំណើរការ
                                </span>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; margin-bottom: 20px;">
                                <div style="width: 56px; height: 56px; background: rgba(0, 136, 204, 0.1); border-radius: 50%; display: inline-grid; place-items: center; color: #0088cc; font-size: 24px; margin-bottom: 8px;">
                                    <i class="fab fa-telegram-plane"></i>
                                </div>
                                <div style="font-size: 1.15rem; font-weight: 800; color: var(--pos-text); margin-bottom: 4px;">ភ្ជាប់ Telegram Bot ក្នុង ៣ ជំហានងាយៗ</div>
                                <p style="color: var(--pos-text-muted); font-size: 0.9rem; margin: 0;">មិនចាំបាច់ស្វែងរក Chat ID ដោយដៃទេ! គ្រាន់តែធ្វើតាមជំហានខាងក្រោម៖</p>
                            </div>

                            <!-- 3 Step Cards -->
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
                                <div style="background: #ffffff; border: 1px solid var(--pos-border); border-radius: 14px; padding: 18px; text-align: center;">
                                    <div style="width: 38px; height: 38px; background: #0088cc; color: white; border-radius: 10px; display: inline-grid; place-items: center; font-size: 16px; font-weight: 800; margin-bottom: 10px;">1</div>
                                    <div style="font-weight: 800; font-size: 0.9rem; color: var(--pos-text); margin-bottom: 4px;">បន្ថែម Bot ចូលក្រុម</div>
                                    <div style="font-size: 0.8rem; color: var(--pos-text-muted); margin-bottom: 12px;">Add bot as admin to group</div>
                                    <a href="https://t.me/mcuPOS_bot?startgroup=true" target="_blank" style="display: inline-block; background: #0088cc; color: white; padding: 8px 14px; border-radius: 8px; font-size: 0.8rem; font-weight: 700; text-decoration: none; width: 100%;">
                                        <i class="fab fa-telegram-plane"></i> បើក Telegram
                                    </a>
                                </div>

                                <div style="background: #ffffff; border: 1px solid var(--pos-border); border-radius: 14px; padding: 18px; text-align: center;">
                                    <div style="width: 38px; height: 38px; background: #f59e0b; color: white; border-radius: 10px; display: inline-grid; place-items: center; font-size: 16px; font-weight: 800; margin-bottom: 10px;">2</div>
                                    <div style="font-weight: 800; font-size: 0.9rem; color: var(--pos-text); margin-bottom: 4px;">ទទួលលេខកូដ</div>
                                    <div style="font-size: 0.8rem; color: var(--pos-text-muted); margin-bottom: 10px;">Bot ផ្ញើលេខកូដ ៦ ខ្ទង់ក្នុងក្រុម</div>
                                    <div style="background: rgba(245,158,11,0.1); border-radius: 8px; padding: 6px; font-size: 0.75rem; color: #d97706; font-weight: 700;">
                                        💡 វាយ <b>/code</b> ក្នុងក្រុមដើម្បីទទួលបានកូដថ្មី
                                    </div>
                                </div>

                                <div style="background: #ffffff; border: 1px solid var(--pos-border); border-radius: 14px; padding: 18px; text-align: center;">
                                    <div style="width: 38px; height: 38px; background: #10b981; color: white; border-radius: 10px; display: inline-grid; place-items: center; font-size: 16px; font-weight: 800; margin-bottom: 10px;">3</div>
                                    <div style="font-weight: 800; font-size: 0.9rem; color: var(--pos-text); margin-bottom: 4px;">បញ្ចូលលេខកូដ</div>
                                    <div style="font-size: 0.8rem; color: var(--pos-text-muted); margin-bottom: 10px;">បញ្ចូលលេខកូដ ៦ ខ្ទង់ខាងក្រោម</div>
                                    <div style="background: rgba(16,185,129,0.1); border-radius: 8px; padding: 6px; font-size: 0.75rem; color: #059669; font-weight: 700;">
                                        ⚡ ភ្ជាប់ភ្លាមៗ រហ័ស និងងាយស្រួល
                                    </div>
                                </div>
                            </div>

                            <!-- Code Input Area -->
                            <div style="background: #ffffff; border: 2px dashed rgba(0,136,204,0.3); border-radius: 16px; padding: 20px; text-align: center;">
                                <label style="display: block; font-weight: 800; font-size: 0.95rem; color: var(--pos-text); margin-bottom: 4px;">
                                    🔑 បញ្ចូលលេខកូដ ៦ ខ្ទង់ / Enter 6-Digit Code
                                </label>
                                <p style="color: var(--pos-text-muted); font-size: 0.8rem; margin-bottom: 14px;">លេខកូដដែល Bot បានផ្ញើក្នុងក្រុម Telegram របស់អ្នក</p>
                                
                                <div style="display: flex; gap: 10px; justify-content: center; align-items: center; flex-wrap: wrap;">
                                    <input type="text" id="posSetupCodeInput" maxlength="6" placeholder="_ _ _ _ _ _"
                                           style="width: 200px; padding: 12px; text-align: center; font-size: 22px; font-weight: 900; letter-spacing: 6px; border: 2px solid var(--pos-border); border-radius: 12px; background: #f8fafc; color: #0088cc; text-transform: uppercase; outline: none; font-family: monospace;"
                                           oninput="this.value=this.value.replace(/[^A-Za-z0-9]/g,'').toUpperCase()">
                                    <button type="button" class="btn btn-primary" style="background: #0088cc; padding: 12px 24px; font-size: 0.9rem; border-radius: 12px; border: none;" onclick="claimPosSetupCode()">
                                        <i class="fas fa-link"></i> ភ្ជាប់ / Connect
                                    </button>
                                </div>
                                <div id="posTgClaimMsg" style="font-size: 0.85rem; font-weight: 700; margin-top: 10px; min-height: 20px;"></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Notification Options Form -->
                    <div style="display: grid; gap: 10px; margin-bottom: 24px;">
                        <p class="pos-card-title"><i class="fas fa-bell" style="color: var(--pos-warning);"></i> ជ្រើសរើសការជូនដំណឹង / Notification Options</p>
                        <?php
                        $checkboxes = [
                            'notify_session_open'  => ['label' => '🔔 ពេលបើកវគ្គលក់ (POS Session Open)', 'desc' => 'ផ្ញើសារជូនដំណឹងនៅពេលបុគ្គលិកបើកវគ្គលក់'],
                            'notify_session_close' => ['label' => '📊 ពេលបិទវគ្គលក់ + របាយការណ៍ (Session Close & Sales Report)', 'desc' => 'ផ្ញើសាររួមមានសរុបប្រាក់លក់ ការបែងចែកសាច់ប្រាក់/QR/កាត'],
                            'notify_gps_start'     => ['label' => '📍 ពេលចាប់ផ្តើមតាមដាន GPS (GPS Tracking Start)', 'desc' => 'ផ្ញើទីតាំង GPS នៅពេលបុគ្គលិកចាប់ផ្តើមតាមដាន'],
                            'notify_gps_stop'      => ['label' => '🛑 ពេលបញ្ឈប់តាមដាន GPS (GPS Tracking Stop)', 'desc' => 'ផ្ញើសារនៅពេលការតាមដានទីតាំង GPS ត្រូវបានបញ្ឈប់'],
                        ];
                        foreach ($checkboxes as $key => $info):
                            $checked = ($telegramConfig[$key] ?? 1) ? 'checked' : '';
                        ?>
                        <label class="pos-card" style="padding: 14px; display: flex; align-items: center; justify-content: space-between; border-color: var(--pos-border); cursor: pointer;">
                            <div>
                                <div style="font-weight: 700; color: var(--pos-text); font-size: 0.9rem;"><?php echo $info['label']; ?></div>
                                <div class="pos-small"><?php echo $info['desc']; ?></div>
                            </div>
                            <label class="pos-toggle">
                                <input type="checkbox" id="pos_<?php echo $key; ?>" <?php echo $checked; ?>>
                                <span class="pos-toggle-slider"></span>
                            </label>
                        </label>
                        <?php endforeach; ?>
                    </div>

                    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                        <button type="button" class="btn btn-primary" style="background: #0088cc; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 700;" onclick="savePosTelegramConfig()">
                            <i class="fas fa-save"></i> រក្សាទុក / Save Preferences
                        </button>

                        <?php if ($isConnected): ?>
                            <button type="button" class="btn" style="background: #475569; color: white; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 700; cursor: pointer;" onclick="testPosTelegram()">
                                <i class="fas fa-paper-plane"></i> សាកល្បងផ្ញើសារ / Test Notification
                            </button>
                            <button type="button" class="btn" style="background: rgba(239,68,68,0.1); color: var(--pos-danger); border: 1px solid rgba(239,68,68,0.3); padding: 12px 24px; border-radius: 12px; font-weight: 700; cursor: pointer;" onclick="disconnectPosTelegram()">
                                <i class="fas fa-unlink"></i> ផ្តាច់ Telegram / Disconnect
                            </button>
                        <?php endif; ?>
                    </div>
                    <div id="posTgConfigMsg" style="font-size: 0.85rem; font-weight: 700; margin-top: 12px; min-height: 20px;"></div>
                </div>

                <script>
                    function claimPosSetupCode() {
                        const code = document.getElementById('posSetupCodeInput').value.trim();
                        const msgEl = document.getElementById('posTgClaimMsg');
                        if (!code || code.length !== 6) {
                            msgEl.innerHTML = '<span style="color:#ef4444;">❌ សូមបញ្ចូលលេខកូដ ៦ ខ្ទង់!</span>';
                            return;
                        }
                        msgEl.innerHTML = '<span style="color:#0088cc;">⏳ កំពុងភ្ជាប់...</span>';
                        fetch('<?php echo mc_base_path(); ?>/public/api/gps_claim_code.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify({ setup_code: code })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                msgEl.innerHTML = '<span style="color:#10b981;">✅ ភ្ជាប់ជោគជ័យ! កំពុងផ្លាស់ប្តូរទិន្នន័យ...</span>';
                                setTimeout(() => location.reload(), 1000);
                            } else {
                                msgEl.innerHTML = `<span style="color:#ef4444;">❌ ${data.error || 'លេខកូដមិនត្រឹមត្រូវ'}</span>`;
                            }
                        })
                        .catch(() => {
                            msgEl.innerHTML = '<span style="color:#ef4444;">❌ មានបញ្ហាបច្ចេកទេស ក្នុងការតភ្ជាប់។</span>';
                        });
                    }

                    function savePosTelegramConfig() {
                        const msgEl = document.getElementById('posTgConfigMsg');
                        const data = {
                            notify_session_open: document.getElementById('pos_notify_session_open').checked ? 1 : 0,
                            notify_session_close: document.getElementById('pos_notify_session_close').checked ? 1 : 0,
                            notify_gps_start: document.getElementById('pos_notify_gps_start').checked ? 1 : 0,
                            notify_gps_stop: document.getElementById('pos_notify_gps_stop').checked ? 1 : 0,
                        };
                        msgEl.innerHTML = '<span style="color:#0088cc;">⏳ កំពុងរក្សាទុក...</span>';
                        fetch('<?php echo mc_base_path(); ?>/public/api/gps_telegram_config.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify(data)
                        })
                        .then(res => res.json())
                        .then(res => {
                            if (res.success) {
                                msgEl.innerHTML = '<span style="color:#10b981;">✅ បានរក្សាទុកការកំណត់ Telegram ដោយជោគជ័យ!</span>';
                                setTimeout(() => { msgEl.innerHTML = ''; }, 3000);
                            } else {
                                msgEl.innerHTML = `<span style="color:#ef4444;">❌ ${res.error || 'បរាជ័យក្នុងការរក្សាទុក'}</span>`;
                            }
                        })
                        .catch(() => {
                            msgEl.innerHTML = '<span style="color:#ef4444;">❌ មានបញ្ហាបច្ចេកទេស។</span>';
                        });
                    }

                    function disconnectPosTelegram() {
                        if (!confirm('តើអ្នកពិតជាចង់ផ្តាច់ Telegram ក្រុមនេះមែនទេ?')) return;
                        const msgEl = document.getElementById('posTgConfigMsg');
                        msgEl.innerHTML = '<span style="color:#ef4444;">⏳ កំពុងផ្តាច់...</span>';
                        fetch('<?php echo mc_base_path(); ?>/public/api/gps_telegram_config.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify({ chat_id: '', chat_title: '', setup_code: '', is_active: 0 })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                msgEl.innerHTML = `<span style="color:#ef4444;">❌ ${data.error || 'បរាជ័យ'}</span>`;
                            }
                        });
                    }

                    function testPosTelegram() {
                        const msgEl = document.getElementById('posTgConfigMsg');
                        msgEl.innerHTML = '<span style="color:#0088cc;">⏳ កំពុងផ្ញើសារសាកល្បង...</span>';
                        fetch('<?php echo mc_base_path(); ?>/public/api/gps_telegram_config.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            credentials: 'same-origin',
                            body: JSON.stringify({ action: 'test' })
                        })
                        .then(res => res.json())
                        .then(res => {
                            if (res && res.success) {
                                msgEl.innerHTML = '<span style="color:#10b981;">✅ បានផ្ញើសារសាកល្បងទៅកាន់ក្រុម Telegram រួចរាល់!</span>';
                            } else {
                                msgEl.innerHTML = `<span style="color:#ef4444;">❌ ${res.error || 'មិនអាចផ្ញើសារបានទេ។'}</span>`;
                            }
                        })
                        .catch(() => {
                            msgEl.innerHTML = '<span style="color:#ef4444;">❌ មិនអាចផ្ញើសារបានទេ។</span>';
                        });
                    }
                </script>
            </div>

            <div class="pos-row" style="margin-top: 32px; justify-content: flex-end; gap: 16px;">
                 <a href="<?php echo mc_url($subdomain . '/pos/dashboard'); ?>" style="text-decoration: none; color: var(--pos-text-muted); font-weight: 700; font-size: 14px;">Cancel Operation</a>
                 <button type="submit" class="btn btn-primary" style="padding: 14px 32px; border-radius: 16px; font-size: 15px; font-weight: 800; box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);">
                    <i class="fas fa-cloud-upload-alt"></i> Commit Changes
                 </button>
            </div>
            
        </div>
    </form>

    <script>
        function switchTab(tabName, clickedTab) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            // Deactivate all nav tabs
            document.querySelectorAll('.pos-tab-link').forEach(el => el.classList.remove('active'));
            
            // Show selected tab
            document.getElementById('tab-' + tabName).classList.add('active');
            clickedTab.classList.add('active');
        }

        // Live Preview Logic
        const settings = {
            headerText: document.querySelector('[name="receipt_header_text"]'),
            footerText: document.querySelector('[name="receipt_footer_text"]'),
            showLogo: document.querySelector('[name="receipt_show_logo"]'),
            paperWidth: document.querySelector('[name="receipt_paper_width"]'),
            fontSize: document.querySelector('[name="receipt_font_size"]'),
            address: document.querySelector('[name="company_address"]'),
            phone: document.querySelector('[name="company_phone"]'),
            email: document.querySelector('[name="company_email"]')
        };

        const preview = {
            header: document.getElementById('preview-header'),
            footer: document.getElementById('preview-footer'),
            logoContainer: document.getElementById('preview-logo-container'),
            receiptBox: document.getElementById('receipt-box'),
            address: document.getElementById('preview-address'),
            contact: document.getElementById('preview-contact')
        };

        if(settings.headerText) {
            settings.headerText.addEventListener('input', (e) => preview.header.textContent = e.target.value);
        }
        
        if(settings.footerText) {
            settings.footerText.addEventListener('input', (e) => preview.footer.textContent = e.target.value);
        }
        
        if(settings.showLogo) {
            settings.showLogo.addEventListener('change', (e) => {
                preview.logoContainer.style.display = e.target.checked ? 'block' : 'none';
                document.getElementById('logo-upload-group').style.display = e.target.checked ? 'block' : 'none';
            });
        }
        
        if(settings.paperWidth) {
            settings.paperWidth.addEventListener('input', (e) => {
                let w = e.target.value;
                if(w > 50) preview.receiptBox.style.width = w + 'px';
            });
        }
        
        if(settings.fontSize) {
            settings.fontSize.addEventListener('input', (e) => {
                let s = e.target.value;
                if(s > 6) preview.receiptBox.style.fontSize = s + 'px';
            });
        }

        function updateContact() {
            let contact = [];
            if(settings.phone.value) contact.push(settings.phone.value);
            if(settings.email.value) contact.push(settings.email.value);
            preview.contact.textContent = contact.join(' | ');
            preview.address.textContent = settings.address.value;
        }

        if(settings.phone) settings.phone.addEventListener('input', updateContact);
        if(settings.email) settings.email.addEventListener('input', updateContact);
        if(settings.address) settings.address.addEventListener('input', updateContact);

        // Business Type Card Selector
        function selectBizType(type) {
            document.getElementById('biz_coffee').checked = (type === 'coffee');
            document.getElementById('biz_mart').checked = (type === 'mart');

            const coffeeCard = document.getElementById('biz_coffee_card');
            const martCard   = document.getElementById('biz_mart_card');
            const notice     = document.getElementById('biz_type_notice');

            if (!coffeeCard || !martCard || !notice) return;

            const activeStyle  = 'border:2px solid var(--pos-primary); border-radius:16px; padding:20px; text-align:center; transition:all 0.2s; background:rgba(var(--pos-primary-rgb),0.06);';
            const inactiveStyle= 'border:2px solid var(--pos-border); border-radius:16px; padding:20px; text-align:center; transition:all 0.2s; background:#fff;';

            coffeeCard.style.cssText = (type === 'coffee') ? activeStyle : inactiveStyle;
            martCard.style.cssText   = (type === 'mart')   ? activeStyle : inactiveStyle;

            if (type === 'mart') {
                notice.style.cssText = 'margin-top:12px; padding:10px 14px; border-radius:12px; font-size:12px; font-weight:700; background:rgba(245,158,11,0.1); color:#d97706; border:1px solid rgba(245,158,11,0.3);';
                notice.innerHTML = '🛒 Mart Mode: Ingredients & Sizes columns ត្រូវបានលាក់ — show only Stock In-Out';
            } else {
                notice.style.cssText = 'margin-top:12px; padding:10px 14px; border-radius:12px; font-size:12px; font-weight:700; background:rgba(var(--pos-primary-rgb),0.07); color:var(--pos-primary); border:1px solid rgba(var(--pos-primary-rgb),0.2);';
                notice.innerHTML = '☕ Coffee/Cafe Mode: មុខងារពេញលេញ — Ingredients, Sizes, Sessions, Reports';
            }
        }

        // ── Custom Payment Methods ─────────────────────────────────────────
        function getCustomMethodsArray() {
            const input = document.getElementById('pos-custom-methods-input');
            try { return JSON.parse(input.value) || []; } catch(e) { return []; }
        }

        function syncCustomMethodsInput(arr) {
            document.getElementById('pos-custom-methods-input').value = JSON.stringify(arr);
        }

        function addCustomMethod() {
            const inp = document.getElementById('new-method-input');
            const name = inp.value.trim();
            if (!name) return;

            const arr = getCustomMethodsArray();
            if (arr.includes(name)) {
                inp.value = '';
                inp.focus();
                return;
            }
            arr.push(name);
            syncCustomMethodsInput(arr);

            // Remove the "no methods" hint if present
            const hint = document.getElementById('no-methods-hint');
            if (hint) hint.remove();

            // Append tag to DOM
            const list = document.getElementById('custom-methods-list');
            const tag = document.createElement('div');
            tag.className = 'custom-method-tag';
            tag.style.cssText = 'display:flex;align-items:center;gap:8px;background:rgba(99,102,241,0.12);border:1px solid rgba(99,102,241,0.25);border-radius:10px;padding:7px 12px;font-weight:700;font-size:13px;color:var(--pos-text);animation:fadeIn 0.2s ease;';
            tag.innerHTML = `<i class="fas fa-university" style="font-size:11px;color:var(--pos-secondary);"></i>
                <span>${name.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</span>
                <button type="button" onclick="removeCustomMethod(this)" data-method="${name.replace(/"/g,'&quot;')}"
                    style="background:none;border:none;cursor:pointer;color:var(--pos-danger);font-size:13px;line-height:1;padding:0;margin-left:2px;" title="Remove">
                    <i class="fas fa-times-circle"></i>
                </button>`;
            list.appendChild(tag);
            inp.value = '';
            inp.focus();
        }

        function removeCustomMethod(btn) {
            const methodName = btn.getAttribute('data-method');
            let arr = getCustomMethodsArray();
            arr = arr.filter(m => m !== methodName);
            syncCustomMethodsInput(arr);

            // Remove tag from DOM
            btn.closest('.custom-method-tag').remove();

            // Show hint if list is now empty
            const list = document.getElementById('custom-methods-list');
            if (list.querySelectorAll('.custom-method-tag').length === 0) {
                list.innerHTML = '<div id="no-methods-hint" style="color:var(--pos-text-muted);font-size:13px;font-style:italic;align-self:center;">No custom methods yet. Add one below.</div>';
            }
        }

    </script>
    
    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
