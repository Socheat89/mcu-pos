<?php
require_once __DIR__ . '/../../../../core/helpers/url.php';
// modules/pos/views/settings/index.php
$pageTitle = 'POS Settings';
$subdomain = Tenant::getCurrent()['subdomain'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Settings - <?php echo htmlspecialchars(Tenant::getCurrent()['name']); ?></title>
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
            background: #f8fafc;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
        }
        .pos-form-control:focus { 
            border-color: var(--pos-primary); 
            background: white;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }
        
        .pos-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 32px;
            padding: 6px;
            background: #f1f5f9;
            border-radius: 18px;
            width: fit-content;
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
            background: white; 
            color: var(--pos-primary); 
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
            background: white;
            transition: all 0.2s;
        }
        .user-card:hover { transform: translateY(-2px); border-color: var(--pos-primary); box-shadow: var(--pos-shadow-md); }
        
        .pos-small { font-size: 12px; color: var(--pos-text-muted); font-weight: 600; }
        .pos-card-sub { font-size: 14px; color: var(--pos-text-muted); font-weight: 500; margin-bottom: 24px; }

        /* Toggle Switch Premium */
        .pos-toggle { position: relative; display: inline-block; width: 48px; height: 26px; }
        .pos-toggle input { opacity: 0; width: 0; height: 0; }
        .pos-toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #e2e8f0; transition: .3s; border-radius: 34px; }
        .pos-toggle-slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        input:checked + .pos-toggle-slider { background-color: var(--pos-primary); }
        input:checked + .pos-toggle-slider:before { transform: translateX(22px); }
        
        .preview-pane { 
            background: #1e293b; 
            padding: 40px; 
            border-radius: 24px; 
            display: flex; 
            justify-content: center; 
            align-items: flex-start; 
            min-height: 600px;
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'settings'; include __DIR__ . '/../partials/navbar.php'; ?>
    
    <div class="pos-row" style="margin-bottom: 32px; align-items: flex-end;">
        <div class="pos-title">
            <h1>Intelligence Settings</h1>
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
                <div class="pos-tab-link active" onclick="switchTab('users', this)">
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
            </div>

            <!-- User Control Tab -->
            <div id="tab-users" class="tab-content active">
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
                                
                                <div style="text-align: center; margin-top: 15px; border-top: 1px dashed #000; padding-top: 10px;">
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
                                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #f1f5f9; display: grid; place-items: center; color: #475569;"><i class="fas fa-money-bill-wave"></i></div>
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
                                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #fef2f2; display: grid; place-items: center; color: #dc2626;"><i class="fas fa-qrcode"></i></div>
                                    <div>
                                        <div style="font-weight: 700;">KHQR / Bakong</div>
                                        <div class="pos-small">Scan to pay with mobile app</div>
                                    </div>
                                </div>
                                <label class="pos-toggle">
                                    <input type="checkbox" name="pos_method_khqr_enabled" <?php echo ($settings['pos_method_khqr_enabled'] == '1') ? 'checked' : ''; ?>>
                                    <span class="pos-toggle-slider"></span>
                                </label>
                            </label>

                            <label class="pos-card" style="padding: 15px; display: flex; align-items: center; justify-content: space-between; border-color: var(--pos-border); cursor: pointer;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #eff6ff; display: grid; place-items: center; color: #2563eb;"><i class="fas fa-credit-card"></i></div>
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
                                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #f0fdf4; display: grid; place-items: center; color: #16a34a;"><i class="fas fa-university"></i></div>
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

                    <div style="background: white; border: 1px solid var(--pos-border); border-radius: 16px; padding: 24px;">
                        <p class="pos-card-title">KHQR Configuration</p>
                        <p class="pos-card-sub" style="margin-bottom: 20px;">Upload your static KHQR image for customers to scan.</p>
                        
                        <div style="text-align: center; background: #f8fafc; padding: 20px; border-radius: 16px; border: 1px dashed var(--pos-border); margin-bottom: 20px;">
                            <img src="<?php echo htmlspecialchars($settings['pos_method_khqr_image']); ?>" style="width: 200px; height: 200px; object-fit: contain; background: white; padding: 10px; border-radius: 12px; border: 1px solid #ddd; display: block; margin: 0 auto; box-shadow: var(--pos-shadow-sm);">
                            <div style="margin-top: 15px;">
                                <input type="file" name="khqr_upload" class="pos-form-control" accept="image/*" style="max-width: 250px; font-size: 12px; padding: 8px;">
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 12px; background: #fff9db; padding: 15px; border-radius: 12px; border: 1px solid #fab005;">
                            <i class="fas fa-lightbulb" style="color: #f08c00; font-size: 18px;"></i>
                            <p class="pos-small" style="color: #856404; margin: 0; line-height: 1.4;">
                                This QR code will be displayed in the checkout modal when 'KHQR' is selected as the payment method.
                            </p>
                        </div>
                    </div>
                </div>
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

    </script>
    
    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
