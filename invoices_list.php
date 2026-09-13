<?php
// invoices_list.php
require_once 'includes/csv_helper.php';

$invoices = readCSV('invoices.csv');
$orders   = readCSV('orders.csv');
$tables   = readCSV('tables.csv');

// Create dynamic reference maps to look up records instantly without complex nested loops
$orderToTableMap = [];
foreach ($orders as $o) {
    $orderToTableMap[trim($o['id'])] = trim($o['table_id']);
}

$tableIdToNameMap = [];
foreach ($tables as $t) {
    $tableIdToNameMap[trim($t['id'])] = trim($t['table_number']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Audit Log</title>
    <style>
        :root {
            --bg-main: #f4f6f9;
            --surface: #ffffff;
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --success: #10b981;
            --dark: #1e293b;
            --text-main: #334155;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --radius: 12px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            background-color: var(--bg-main);
            color: var(--text-main);
            padding: 2rem 1rem;
            line-height: 1.5;
        }

        .container {
            max-width: 1000px; /* Slightly wider to easily accommodate the new column */
            margin: 0 auto;
        }

        /* Modern Dark Header */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--dark);
            color: white;
            padding: 1.25rem 2rem;
            border-radius: var(--radius);
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        header h1 {
            font-size: 1.25rem;
            font-weight: 700;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: background 0.2s;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Central Card Container */
        .tab-content {
            background: var(--surface);
            padding: 2rem;
            border-radius: var(--radius);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border);
        }

        h3 {
            margin-bottom: 1.5rem;
            color: var(--dark);
            font-size: 1.25rem;
            font-weight: 700;
        }

        .alert-box {
            background-color: #fffbeb;
            color: #b45309;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            font-weight: 500;
            border: 1px solid #fef3c7;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        /* Styled Matrix Data Table */
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .invoice-table th, .invoice-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.95rem;
            vertical-align: middle;
        }

        .invoice-table th {
            background-color: var(--bg-main);
            color: var(--dark);
            font-weight: 600;
        }

        .badge-payment {
            background: #e2e8f0;
            color: var(--dark);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-table {
            background: #eff6ff;
            color: #1d4ed8;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid #bfdbfe;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.4rem 0.8rem;
            border: 1px solid var(--primary);
            color: var(--primary);
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s;
            background: #fff;
        }

        .btn-action:hover {
            background: var(--primary);
            color: white;
        }

        .timestamp-text {
            font-size: 0.85rem;
            color: var(--text-muted);
            white-space: nowrap;
        }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>🧾 Invoice Ledger Vault</h1>
        <a href="reports.php" class="btn-back">⬅️ Back to Logs</a>
    </header>

    <div class="tab-content">
        <h3>Settled Invoices Matrix</h3>
        
        <?php if (empty($invoices)): ?>
            <div class="alert-box">No invoices have been settled yet today. Get started from the Billing Desk!</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th>Invoice ID</th>
                            <th>Order ID</th>
                            <th>Table Number</th> <!-- 🆕 Table number column header -->
                            <th>Subtotal</th>
                            <th>Tax (5%)</th>
                            <th>Grand Total</th>
                            <th>Payment Mode</th>
                            <th>Timestamp</th>
                            <th style="text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_reverse($invoices) as $inv): 
                            // Cross-reference data maps to resolve specific table numbers securely
                            $associatedOrderId = trim($inv['order_id']);
                            $resolvedTableId = isset($orderToTableMap[$associatedOrderId]) ? $orderToTableMap[$associatedOrderId] : '';
                            $resolvedTableName = isset($tableIdToNameMap[$resolvedTableId]) ? $tableIdToNameMap[$resolvedTableId] : 'N/A';
                        ?>
                            <tr>
                                <td style="font-weight: 700; color: var(--dark);">#<?= htmlspecialchars($inv['id']); ?></td>
                                <td><?= htmlspecialchars($inv['order_id']); ?></td>
                                <td>
                                    <span class="badge-table"> <?= htmlspecialchars($resolvedTableName); ?></span>
                                </td>
                                <td>$<?= number_format((float)$inv['subtotal'], 2); ?></td>
                                <td>$<?= number_format((float)$inv['tax'], 2); ?></td>
                                <td style="color: var(--success); font-weight: 700;">$<?= number_format((float)$inv['grand_total'], 2); ?></td>
                                <td><span class="badge-payment"><?= htmlspecialchars($inv['payment_mode']); ?></span></td>
                                <td class="timestamp-text"><?= htmlspecialchars($inv['created_at']); ?></td>
                                <td style="text-align: center;">
                                    <a href="view_invoice.php?id=<?= htmlspecialchars($inv['id']); ?>" class="btn-action">🔍 View & Print</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
