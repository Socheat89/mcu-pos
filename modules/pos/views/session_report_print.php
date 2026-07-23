<?php
// modules/pos/views/session_report_print.php
// Clean printable daily report — Professional Khmer POS Style
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Sales Report — <?php echo htmlspecialchars($companyName); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700;900&family=Kantumruy+Pro:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Kantumruy Pro', 'Battambang', 'Courier New', monospace, sans-serif;
            font-size: 13px;
            color: #000000;
            background: #f8fafc;
            padding: 24px 16px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .report-container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 32px 36px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .report-container {
                max-width: 100% !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: portrait;
                margin: 10mm;
            }
        }

        /* Top Action Bar */
        .toolbar {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }
        .btn-print {
            padding: 10px 28px;
            background: #06b6d4;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.25);
            transition: all 0.2s;
        }
        .btn-print:hover {
            background: #0891b2;
            transform: translateY(-1px);
        }

        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 16px;
            border-bottom: 2px solid #000000;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 4px;
            color: #000000;
            letter-spacing: 0.5px;
        }
        .header .sub-date {
            font-size: 12px;
            font-weight: 500;
            color: #444444;
            margin-bottom: 2px;
        }
        .header .sub-session {
            font-size: 11px;
            font-weight: 500;
            color: #666666;
        }

        /* Info Row */
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding: 4px 0;
        }
        .info-box {
            flex: 1;
        }
        .info-box strong {
            display: block;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #555555;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .info-box span {
            font-size: 13px;
            font-weight: 600;
            color: #000000;
        }

        /* Section Title Header Bar */
        .section-title {
            font-size: 13px;
            font-weight: 700;
            margin: 18px 0 8px 0;
            padding: 6px 12px;
            background: #f4f4f4;
            border-left: 4px solid #000000;
            color: #000000;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Table Styling */
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 12px;
        }
        table.report-table th {
            background: #f8f8f8;
            padding: 6px 8px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #000000;
            border-top: 1px solid #000000;
            border-bottom: 2px solid #000000;
        }
        table.report-table td {
            padding: 6px 8px;
            border-bottom: 1px dotted #cccccc;
            color: #000000;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: 700; }

        .total-row td {
            border-top: 2px solid #000000 !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            color: #000000 !important;
            padding: 7px 8px;
        }

        .rate-note {
            font-size: 10px;
            color: #666666;
            text-align: right;
            margin-top: -6px;
            margin-bottom: 14px;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 12px;
            border-top: 1px dashed #cccccc;
            font-size: 11px;
            color: #666666;
        }
    </style>
</head>
<body>

<!-- Print Button Toolbar -->
<div class="toolbar no-print">
    <button class="btn-print" onclick="window.print()">🖨️ Print Daily Report / បោះពុម្ព</button>
</div>

<div class="report-container">
    <!-- Header -->
    <div class="header">
        <h1><?php echo htmlspecialchars($companyName); ?></h1>
        <div class="sub-date">Daily Sales Report — <?php echo $sessionDate; ?></div>
        <div class="sub-session">Session #<?php echo (int)$session['id']; ?></div>
    </div>

    <!-- Info Row -->
    <div class="info-row">
        <div class="info-box">
            <strong>STAFF</strong>
            <span><?php echo $staffName; ?></span>
        </div>
        <div class="info-box">
            <strong>CHECK IN</strong>
            <span><?php echo $sessionTime; ?></span>
        </div>
        <div class="info-box">
            <strong>CHECK OUT</strong>
            <span><?php echo $closeTime; ?></span>
        </div>
        <div class="info-box">
            <strong>TOTAL CUPS</strong>
            <span><?php echo $totalItems; ?> items</span>
        </div>
    </div>

    <!-- Section 1: Items Sold -->
    <div class="section-title">📋 មុខទំនិញលក់ចេញ / Items Sold</div>
    <table class="report-table">
        <thead>
            <tr>
                <th style="width:30px;">#</th>
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
                <td class="text-center bold"><?php echo (int)$p['qty_sold']; ?></td>
                <td class="text-right">$<?php echo number_format($unitPrice, 2); ?></td>
                <td class="text-right"><?php echo number_format($unitPrice * $rate, 0); ?>៛</td>
                <td class="text-right bold">$<?php echo number_format($p['total_revenue'], 2); ?></td>
            </tr>
            <?php endforeach; endif; ?>
            <tr class="total-row">
                <td colspan="3" class="bold">Total / សរុប</td>
                <td></td>
                <td></td>
                <td class="text-right bold">$<?php echo number_format($totalRevenue, 2); ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Section 2: Sale Income Today -->
    <div class="section-title">💰 ចំណូលថ្ងៃនេះ / Sale Income Today</div>
    <table class="report-table">
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
                $icon = ($method === 'card') ? '💳' : '🏦';
            ?>
            <tr>
                <td><?php echo $icon . ' ' . htmlspecialchars($label); ?></td>
                <td class="text-right bold">$<?php echo number_format($amt, 2); ?></td>
                <td class="text-right">—</td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td>📊 Grand Total / សរុបរួម</td>
                <td class="text-right bold">$<?php echo number_format($totalRevenue, 2); ?></td>
                <td class="text-right bold"><?php echo number_format($totalRevenue * $rate, 0); ?>៛</td>
            </tr>
        </tbody>
    </table>
    <div class="rate-note">អត្រាប្តូរ: 1$ = <?php echo number_format($rate, 0); ?>៛</div>

    <!-- Section 3: Cash Balance -->
    <div class="section-title">🔒 សាច់ប្រាក់ / Cash Balance</div>
    <table class="report-table">
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
</div>

<script>
    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('autoprint')) {
            window.print();
        }
    };
</script>
</body>
</html>

