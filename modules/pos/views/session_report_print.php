<?php
// modules/pos/views/session_report_print.php
// Clean printable daily report — Stella Mobile Café style
$companyName = $tenantName ?? ($currentTenant['name'] ?? 'POS');
$staffName = htmlspecialchars($session['username'] ?? '');
$sessionDate = date('d M Y', strtotime($session['opened_at']));
$sessionTime = date('H:i', strtotime($session['opened_at']));
$closeTime = $isClosed ? date('H:i', strtotime($session['closed_at'])) : '—';
$rate = $exchangeRate ?? 4100;

$totalItems = array_sum(array_column($soldProducts, 'qty_sold'));
$totalRevenue = array_sum(array_column($soldProducts, 'total_revenue'));
$cashUSD_amt = $cashUSDRaw ?? 0;
$cashKHR_usd = $cashKHRRaw ?? 0;
$bankTotal = 0;
foreach ($paymentSummary as $method => $amt) {
    if (!in_array($method, ['cash', 'cash_khr'])) $bankTotal += $amt;
}
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <title>Daily Report — <?php echo htmlspecialchars($companyName); ?></title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Battambang', 'Courier New', monospace; font-size: 13px; color: #000; background: #fff; padding: 20px; max-width: 800px; margin: 0 auto; }
        @media print { body { padding: 5mm; } .no-print { display:none!important; } }

        .header { text-align: center; margin-bottom: 16px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { font-size: 20px; margin-bottom: 2px; }
        .header .sub { font-size: 11px; color: #444; }

        .info-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 12px; }
        .info-box { flex: 1; }
        .info-box strong { display: block; font-size: 10px; text-transform: uppercase; color: #666; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 12px; }
        th { background: #f0f0f0; padding: 6px 8px; text-align: left; font-size: 11px; border-bottom: 2px solid #000; }
        td { padding: 5px 8px; border-bottom: 1px dotted #ccc; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .total-row { border-top: 2px solid #000; font-weight: bold; font-size: 14px; }

        .section-title { font-size: 14px; font-weight: bold; margin: 18px 0 8px; padding: 4px 10px; background: #f5f5f5; border-left: 4px solid #000; }

        .summary-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px; }
        .summary-card { border: 1px solid #ccc; padding: 10px; border-radius: 4px; }
        .summary-card .label { font-size: 10px; color: #666; text-transform: uppercase; }
        .summary-card .value { font-size: 18px; font-weight: bold; }
        .summary-card .sub { font-size: 11px; color: #888; }

        .footer { text-align: center; margin-top: 24px; padding-top: 12px; border-top: 1px dashed #ccc; font-size: 11px; color: #666; }

        .btn-print { display: block; margin: 16px auto; padding: 12px 32px; background:#06b6d4; color:#fff; border:none; border-radius:8px; font-size:15px; font-weight:bold; cursor:pointer; }
        .btn-print:hover { background:#0891b2; }
    </style>
</head>
<body class="no-print-override">

<!-- Print Button -->
<button class="btn-print no-print" onclick="window.print()">🖨️ Print Daily Report</button>

<!-- Header -->
<div class="header">
    <h1><?php echo htmlspecialchars($companyName); ?></h1>
    <div class="sub">Daily Sales Report — <?php echo $sessionDate; ?></div>
    <div class="sub">Session #<?php echo (int)$session['id']; ?></div>
</div>

<!-- Info -->
<div class="info-row">
    <div class="info-box">
        <strong>Staff</strong>
        <?php echo $staffName; ?>
    </div>
    <div class="info-box">
        <strong>Check In</strong>
        <?php echo $sessionTime; ?>
    </div>
    <div class="info-box">
        <strong>Check Out</strong>
        <?php echo $closeTime; ?>
    </div>
    <div class="info-box">
        <strong>Total Cups</strong>
        <?php echo $totalItems; ?> items
    </div>
</div>

<!-- Section 1: Items Sold -->
<div class="section-title">📋 មុខទំនិញលក់ចេញ / Items Sold</div>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Item / មុខទំនិញ</th>
            <th class="text-center">Qty</th>
            <th class="text-right">Price (USD)</th>
            <th class="text-right">Price (KHR)</th>
            <th class="text-right">Total (USD)</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($soldProducts)): ?>
        <tr><td colspan="6" class="text-center" style="padding:20px;color:#999;">No items sold</td></tr>
        <?php else: $i = 0; foreach ($soldProducts as $p): $i++; 
            $unitPrice = $p['qty_sold'] > 0 ? $p['total_revenue'] / $p['qty_sold'] : 0;
        ?>
        <tr>
            <td><?php echo $i; ?></td>
            <td><?php echo htmlspecialchars($p['name']); ?></td>
            <td class="text-center"><?php echo (int)$p['qty_sold']; ?></td>
            <td class="text-right">$<?php echo number_format($unitPrice, 2); ?></td>
            <td class="text-right"><?php echo number_format($unitPrice * $rate, 0); ?>៛</td>
            <td class="text-right bold">$<?php echo number_format($p['total_revenue'], 2); ?></td>
        </tr>
        <?php endforeach; endif; ?>
        <tr class="total-row">
            <td colspan="3" class="bold">Total / សរុប</td>
            <td></td>
            <td></td>
            <td class="text-right">$<?php echo number_format($totalRevenue, 2); ?></td>
        </tr>
    </tbody>
</table>

<!-- Section 2: Income Summary -->
<div class="section-title">💰 ចំណូលថ្ងៃនេះ / Sale Income Today</div>
<table>
    <thead>
        <tr>
            <th>Payment / វិធីបង់ប្រាក់</th>
            <th class="text-right">USD ($)</th>
            <th class="text-right">KHR (៛)</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($cashUSD_amt > 0): ?>
        <tr>
            <td>💵 Cash — USD</td>
            <td class="text-right bold">$<?php echo number_format($cashUSD_amt, 2); ?></td>
            <td class="text-right">—</td>
        </tr>
        <?php endif; ?>
        <?php if ($cashKHR_usd > 0): ?>
        <tr>
            <td>💵 Cash — KHR</td>
            <td class="text-right">$<?php echo number_format($cashKHR_usd, 2); ?></td>
            <td class="text-right bold"><?php echo number_format($cashKHR_usd * $rate, 0); ?>៛</td>
        </tr>
        <?php endif; ?>
        <?php foreach ($paymentSummary as $method => $amt): 
            if ($method === 'cash' || $method === 'cash_khr') continue;
            $label = $methodLabels[$method] ?? strtoupper($method);
        ?>
        <tr>
            <td>🏦 <?php echo $label; ?></td>
            <td class="text-right bold">$<?php echo number_format($amt, 2); ?></td>
            <td class="text-right">—</td>
        </tr>
        <?php endforeach; ?>
        <tr class="total-row">
            <td>📊 Grand Total / សរុបរួម</td>
            <td class="text-right">$<?php echo number_format($totalRevenue, 2); ?></td>
            <td class="text-right"><?php echo number_format($totalRevenue * $rate, 0); ?>៛</td>
        </tr>
    </tbody>
</table>
<div style="font-size:10px;color:#888;text-align:right;margin-bottom:8px;">អត្រាប្តូរ: 1$ = <?php echo number_format($rate, 0); ?>៛</div>

<!-- Section 3: Opening/Closing Cash -->
<div class="section-title">🔐 សាច់ប្រាក់ / Cash Balance</div>
<table>
    <thead>
        <tr>
            <th>Description</th>
            <th class="text-right">Amount (USD)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>💰 Opening Balance / សាច់ប្រាក់ដើម</td>
            <td class="text-right bold">$<?php echo number_format($session['opening_balance'], 2); ?></td>
        </tr>
        <tr>
            <td>➕ Total Sales / លក់បានសរុប</td>
            <td class="text-right bold">$<?php echo number_format($totalRevenue, 2); ?></td>
        </tr>
        <?php if ($isClosed): ?>
        <tr>
            <td>🔒 Closing Balance / សាច់ប្រាក់បិទ</td>
            <td class="text-right bold">$<?php echo number_format($session['closing_balance'], 2); ?></td>
        </tr>
        <?php $diff = $session['closing_balance'] - ($session['opening_balance'] + $totalRevenue); ?>
        <tr>
            <td><?php echo $diff >= 0 ? '✅' : '⚠️'; ?> Difference / ខុសគ្នា</td>
            <td class="text-right bold" style="color:<?php echo $diff >= 0 ? '#10b981' : '#ef4444'; ?>;">
                <?php echo ($diff >= 0 ? '+' : '') . number_format($diff, 2); ?>
            </td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<div class="footer">
    <p>Printed on <?php echo date('d M Y H:i'); ?> — Powered by Mekong POS</p>
</div>

</body>
</html>
