<?php
// modules/pos/views/receipt.php
require_once __DIR__ . '/../../../core/classes/Settings.php';
require_once __DIR__ . '/../../../core/helpers/url.php';

// Get receipt settings
$receiptSettings = Settings::getAll();
$showLogo = ($receiptSettings['receipt_show_logo'] ?? '1') === '1';
$logoPath = $receiptSettings['receipt_logo_path'] ?? '';
$headerText = $receiptSettings['receipt_header_text'] ?? 'Point of Sale Receipt';
$footerText = $receiptSettings['receipt_footer_text'] ?? 'Thank you for your business!';
$fontSize = (int) ($receiptSettings['receipt_font_size'] ?? 12);
$paperWidth = (int) ($receiptSettings['receipt_paper_width'] ?? 400);

$currentTenant = Tenant::getCurrent();
$subdomain = $currentTenant['subdomain'] ?? '';

$autoPrint = (($_GET['autoprint'] ?? '') === '1');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('pos_receipt_title'); ?> <?php echo $order['id']; ?></title>
    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
<<<<<<< HEAD
        body { font-family: 'Battambang', 'Courier New', monospace; margin: 0; padding: 0; background: #f8f9fa; }
        .receipt-wrap { padding: 20px; }
        .receipt { max-width: <?php echo $paperWidth; ?>px; margin: 0 auto; background: white; padding: 20px; border: 1px solid #ddd; font-size: <?php echo $fontSize; ?>px; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 1px dashed #000; padding-bottom: 10px; }
        .header img { display: block; margin: 0 auto 10px; }
        .header h1 { margin: 0; font-size: <?php echo $fontSize + 6; ?>px; }
        .header p { margin: 5px 0; }
        .order-info { margin-bottom: 15px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .items { margin-bottom: 15px; }
=======
        body { font-family: 'Battambang', 'Courier New', monospace; margin: 0; padding: 0; background: var(--pos-bg, #080A10); color: var(--pos-text, #f8fafc); }
        .receipt-wrap { padding: 20px; }
        .receipt { 
            max-width: <?php echo $paperWidth; ?>px; 
            margin: 0 auto; 
            background: white; 
            color: #000;
            padding: 20px; 
            border: 1px solid rgba(0,0,0,0.1); 
            font-size: <?php echo $fontSize; ?>px; 
            line-height: 1.4; 
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 10px 10px -5px rgba(0, 0, 0, 0.5);
            border-radius: 12px;
        }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 1px dashed #000; padding-bottom: 10px; }
        .header img { display: block; margin: 0 auto 10px; }
        .header h1 { margin: 0; font-size: <?php echo $fontSize + 6; ?>px; color: #000; }
        .header p { margin: 5px 0; color: #333; }
        .order-info { margin-bottom: 15px; color: #000; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .items { margin-bottom: 15px; color: #000; }
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
        .item { display: flex; justify-content: space-between; margin-bottom: 5px; padding-bottom: 5px; border-bottom: 1px dotted #ccc; }
        .item:last-child { border-bottom: none; }
        .item-name { flex: 2; }
        .item-qty { flex: 1; text-align: center; }
        .item-price { flex: 1; text-align: right; }
<<<<<<< HEAD
        .total { border-top: 1px solid #000; padding-top: 10px; font-weight: bold; }
        .footer { text-align: center; margin-top: 20px; border-top: 1px dashed #000; padding-top: 10px; }
        .btn { background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 5px; }
        .btn:hover { background: #218838; }
        @media print {
            body { background: white; }
            .receipt { box-shadow: none; border: none; }
            .btn { display: none; }
=======
        .total { border-top: 1px solid #000; padding-top: 10px; font-weight: bold; color: #000; }
        .footer { text-align: center; margin-top: 20px; border-top: 1px dashed #000; padding-top: 10px; color: #333; }
        
        .receipt-actions {
            text-align: center;
            margin-top: 25px;
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .btn { 
            padding: 12px 24px; 
            text-decoration: none; 
            border-radius: 12px; 
            display: inline-flex; 
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 700;
            transition: all 0.2s;
            cursor: pointer;
        }
        .btn-primary {
            background: var(--pos-primary, #06b6d4);
            background: var(--pos-gradient-primary, linear-gradient(135deg, #06b6d4 0%, #7c3aed 100%));
            color: white;
            border: none;
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.25);
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(6, 182, 212, 0.4);
        }
        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: var(--pos-text, #f8fafc) !important;
            border: 1.5px solid var(--pos-border, rgba(255,255,255,0.08));
        }
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--pos-primary, #06b6d4) !important;
            border-color: var(--pos-primary, #06b6d4);
            transform: translateY(-1px);
        }
        
        @media print {
            body { background: white; color: #000; }
            .receipt { box-shadow: none; border: none; padding: 0; margin: 0; }
            .receipt-actions, .btn { display: none !important; }
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
            .pos-shell, .pos-sidebar, .pos-topbar, .pos-overlay, .pos-footer { display: none !important; }
            .receipt-wrap { padding: 0; }
            .header img { max-width: 120px !important; max-height: 60px !important; }
            .header img[src=""] { display: none; }
<<<<<<< HEAD
            .header img[src=""] { display: none; }
=======
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
        }
    </style>
</head>
<body class="pos-app">
    <?php if (!$autoPrint): ?>
        <?php $activeNav = 'orders'; include __DIR__ . '/partials/navbar.php'; ?>
    <?php endif; ?>



    <div class="receipt-wrap">
    <div class="receipt">
        <div class="header">
            <?php if ($showLogo && !empty($logoPath)): ?>
                <div style="text-align: center; margin-bottom: 10px;">
                    <img src="<?php echo htmlspecialchars($logoPath); ?>" 
                         alt="Company Logo" 
                         style="max-width: 150px; max-height: 80px; object-fit: contain;" 
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';" />
                    <div style="display: none; font-size: <?php echo $fontSize + 4; ?>px; font-weight: bold;">
                        <?php echo htmlspecialchars($currentTenant['name'] ?? ''); ?>
                    </div>
                </div>
            <?php endif; ?>
            <h1><?php echo htmlspecialchars($currentTenant['name'] ?? ''); ?></h1>
            <p><?php echo htmlspecialchars($headerText); ?></p>
            <p><?php echo __('receipt_order_label'); ?><?php echo $order['id']; ?></p>
        </div>

        <?php
        $companyAddress = $receiptSettings['company_address'] ?? '';
        $companyPhone = $receiptSettings['company_phone'] ?? '';
        $companyEmail = $receiptSettings['company_email'] ?? '';
        $companyTaxId = $receiptSettings['company_tax_id'] ?? '';
        $companyWebsite = $receiptSettings['company_website'] ?? '';
        if (!empty($companyAddress) || !empty($companyPhone) || !empty($companyEmail) || !empty($companyTaxId) || !empty($companyWebsite)):
        ?>
        <div style="text-align: center; margin-bottom: 10px; font-size: <?php echo $fontSize - 2; ?>px; border-bottom: 1px dotted #ccc; padding-bottom: 5px;">
            <?php if (!empty($companyAddress)): ?>
                <div><?php echo htmlspecialchars($companyAddress); ?></div>
            <?php endif; ?>
            <?php if (!empty($companyPhone)): ?>
                <div><?php echo __('phone'); ?>: <?php echo htmlspecialchars($companyPhone); ?></div>
            <?php endif; ?>
            <?php if (!empty($companyEmail)): ?>
                <div><?php echo __('email'); ?>: <?php echo htmlspecialchars($companyEmail); ?></div>
            <?php endif; ?>
            <?php if (!empty($companyTaxId)): ?>
                <div><?php echo __('tax_id'); ?>: <?php echo htmlspecialchars($companyTaxId); ?></div>
            <?php endif; ?>
            <?php if (!empty($companyWebsite)): ?>
                <div><?php echo htmlspecialchars($companyWebsite); ?></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="order-info">
            <div class="info-row">
                <span><?php echo __('date'); ?>:</span>
                <span><?php echo date('M j, Y H:i', strtotime($order['created_at'])); ?></span>
            </div>
            <div class="info-row">
                <span><?php echo __('customer'); ?>:</span>
                <span><?php echo htmlspecialchars($order['customer_name'] ?? __('walk_in_customer')); ?></span>
            </div>
            <?php if ($order['email']): ?>
            <div class="info-row">
                <span>Email:</span>
                <span><?php echo htmlspecialchars($order['email']); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($order['phone']): ?>
            <div class="info-row">
                <span>Phone:</span>
                <span><?php echo htmlspecialchars($order['phone']); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="items">
            <div class="item" style="font-weight: bold; border-bottom: 1px solid #000;">
                <span class="item-name"><?php echo __('item'); ?></span>
                <span class="item-qty"><?php echo __('qty'); ?></span>
                <span class="item-price"><?php echo __('total'); ?></span>
            </div>
            <?php foreach ($order['items'] as $item): ?>
                <div class="item">
                    <span class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></span>
                    <span class="item-qty"><?php echo $item['quantity']; ?></span>
                    <span class="item-price">$<?php echo number_format($item['total'], 2); ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="total">
            <div class="info-row">
                <span><?php echo __('subtotal'); ?>:</span>
                <span>$<?php echo number_format($order['total'] - ($order['tax'] ?? 0) - ($order['discount'] ?? 0), 2); ?></span>
            </div>
            <?php if ($order['tax'] ?? 0 > 0): ?>
            <div class="info-row">
                <span><?php echo __('tax'); ?>:</span>
                <span>$<?php echo number_format($order['tax'], 2); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($order['discount'] ?? 0 > 0): ?>
            <div class="info-row">
                <span><?php echo __('discount'); ?>:</span>
                <span>-$<?php echo number_format($order['discount'], 2); ?></span>
            </div>
            <?php endif; ?>
            <div class="info-row" style="font-size: 14px;">
                <span><?php echo __('total_all_caps', ['default' => 'TOTAL']); ?>:</span>
                <span>$<?php echo number_format($order['total'], 2); ?></span>
            </div>
        </div>

        <?php if (!empty($order['payments'])): ?>
        <div style="margin-top: 15px; border-top: 1px dashed #000; padding-top: 10px;">
            <div style="font-weight: bold; margin-bottom: 5px;"><?php echo __('payment'); ?>:</div>
            <?php foreach ($order['payments'] as $payment): ?>
                <div class="info-row">
                    <span><?php echo ucfirst($payment['method']); ?>:</span>
                    <span>$<?php echo number_format($payment['amount'], 2); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="footer">
            <p><?php echo htmlspecialchars($footerText); ?></p>
            <p><?php echo date('M j, Y H:i:s'); ?></p>
        </div>
    </div>

<<<<<<< HEAD
    <div style="text-align: center; margin-top: 20px;">
        <a href="javascript:window.print()" class="btn" style="background: var(--pos-gradient-primary); border: none; font-weight: 800; padding: 14px 28px; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);">
            <i class="fas fa-print"></i> <?php echo __('print_receipt'); ?>
        </a>
        <a href="<?php echo mc_url($subdomain . '/pos/pos'); ?>" class="btn" style="background: #f8fafc; color: var(--pos-text); border: 1.5px solid var(--pos-border); font-weight: 700; padding: 14px 28px; border-radius: 12px;">
=======
    <div class="receipt-actions">
        <a href="javascript:window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> <?php echo __('print_receipt'); ?>
        </a>
        <a href="<?php echo mc_url($subdomain . '/pos/pos'); ?>" class="btn btn-secondary">
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
            <i class="fas fa-arrow-left"></i> <?php echo __('back_to_terminal'); ?>
        </a>
    </div>

    <script>
        window.onafterprint = function() {
            // Redirect back to POS terminal after printing or canceling the dialog
            setTimeout(function() {
                window.location.href = "<?php echo mc_url($subdomain . '/pos/pos'); ?>";
            }, 500);
        };

        <?php if ($autoPrint): ?>
            window.addEventListener('load', function () {
                setTimeout(function () { 
                    window.print(); 
                }, 500);
            });
        <?php endif; ?>
    </script>

    </div>

    <?php if (!$autoPrint): ?>
        <?php include __DIR__ . '/partials/footer.php'; ?>
    <?php endif; ?>
</body>
</html>
