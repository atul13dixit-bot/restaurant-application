<?php
// view_invoice.php
require_once 'includes/csv_helper.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: Missing Invoice Identifier parameter. <a href='billing.php'>Go Back</a>");
}

$invoiceId = trim($_GET['id']);
$invoices = readCSV('invoices.csv');
$currentInvoice = null;

foreach ($invoices as $inv) {
    if (trim($inv['id']) === $invoiceId) {
        $currentInvoice = $inv;
        break;
    }
}

if (!$currentInvoice) {
    die("Error: Invoice record not found in system databases. <a href='billing.php'>Go Back</a>");
}

$orders = readCSV('orders.csv');
$associatedOrder = null;
foreach ($orders as $o) {
    if (trim($o['id']) === trim($currentInvoice['order_id'])) {
        $associatedOrder = $o;
        break;
    }
}

$quantities = [];
if ($associatedOrder) {
    $quantities = json_decode(base64_decode($associatedOrder['items']), true);
}
$menu = readCSV('menu.csv');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice Receipt #<?= htmlspecialchars($invoiceId); ?></title>
    <style>
        :root {
            --bg-main: #f4f6f9; --surface: #ffffff; --primary: #4f46e5; --primary-hover: #4338ca;
            --dark: #1e293b; --text-main: #334155; --text-muted: #64748b; --border: #e2e8f0; --radius: 12px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-main); padding: 2rem 1rem; }
        .receipt-card { max-width: 500px; margin: 0 auto; background: var(--surface); padding: 2rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .header-section { text-align: center; margin-bottom: 1.5rem; border-bottom: 2px dashed var(--border); padding-bottom: 1rem; }
        .meta-table, .items-table { width: 100%; margin-bottom: 1rem; border-collapse: collapse; }
        .items-table th { background: var(--bg-main); text-align: left; padding: 0.5rem; font-size: 0.85rem; color: var(--dark); }
        .items-table td { padding: 0.6rem 0.5rem; border-bottom: 1px solid var(--border); font-size: 0.9rem; }
        .total-row { display: flex; justify-content: space-between; padding: 0.4rem 0; font-size: 0.95rem; }
        .grand-total { font-size: 1.3rem; font-weight: bold; color: #10b981; border-top: 2px solid var(--border); padding-top: 0.5rem; margin-top: 0.5rem; }
        .nav-actions { max-width: 500px; margin: 0 auto 1rem auto; display: flex; justify-content: space-between; }
        .btn-nav { text-decoration: none; padding: 0.5rem 1rem; font-weight: 600; border-radius: 6px; font-size: 0.85rem; border: none; cursor: pointer; }
        .btn-secondary { background: #e2e8f0; color: var(--text-main); }
        .btn-primary { background: var(--primary); color: white; }
        @media print { .no-print { display: none !important; } body { background: white; padding: 0; } .receipt-card { border: none; box-shadow: none; } }
    </style>
</head>
<body>
<div class="container">
    <div class="nav-actions no-print">
        <a href="billing.php" class="btn-nav btn-secondary">⬅️ Back to Billing Desk</a>
        <button onclick="window.print();" class="btn-nav btn-primary">🖨️ Print Receipt</button>
    </div>

    <div class="receipt-card">
        <div class="header-section">
            <h2 style="color: var(--dark); font-weight: 700;">🍽️ FLAVORS KITCHEN</h2>
            <p style="font-size: 0.85rem; color: var(--text-muted);">123 Culinary Drive, Downtown Metro</p>
        </div>

        <div style="font-size: 0.85rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between;">
            <div><strong>INVOICE:</strong> #<?= htmlspecialchars($currentInvoice['id']); ?></div>
            <div style="color: var(--text-muted);"><?= htmlspecialchars($currentInvoice['created_at']); ?></div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Rate</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quantities as $itemId => $qty):
                    foreach ($menu as $mItem):
                        if (trim($mItem['id']) === trim($itemId)):
                            $rowTotal = (float)$mItem['price'] * (int)$qty; ?>
                            <tr>
                                <td><?= htmlspecialchars($mItem['name']); ?></td>
                                <td style="text-align: center; font-weight: 600;"><?= htmlspecialchars($qty); ?></td>
                                <td style="text-align: right;">$<?= number_format((float)$mItem['price'], 2); ?></td>
                                <td style="text-align: right; font-weight: 600;">$<?= number_format($rowTotal, 2); ?></td>
                            </tr>
                <?php endif; endforeach; endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top: 1.5rem;">
            <div class="total-row"><span>Subtotal Balance:</span><strong>$<?= number_format((float)$currentInvoice['subtotal'], 2); ?></strong></div>
            <div class="total-row"><span>Surcharge Tax (5%):</span><strong>$<?= number_format((float)$currentInvoice['tax'], 2); ?></strong></div>
            <div class="total-row grand-total"><span>Grand Total Net:</span><span>$<?= number_format((float)$currentInvoice['grand_total'], 2); ?></span></div>
            <div class="total-row" style="margin-top: 0.75rem; font-size: 0.85rem; color: var(--text-muted);">
                <span>Payment Instrument:</span><span style="font-weight: 600; color: var(--primary);"><?= htmlspecialchars($currentInvoice['payment_mode']); ?></span>
            </div>
        </div>

        <div style="text-align: center; margin-top: 2rem; font-size: 0.85rem; color: var(--text-muted); border-top: 1px dashed var(--border); padding-top: 1rem;">
            Thank you for dining with us! Please visit again.
        </div>
    </div>
</div>
</body>
</html>
