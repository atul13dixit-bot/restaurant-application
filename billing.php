<?php
// billing.php
require_once 'includes/csv_helper.php';
$tables = readCSV('tables.csv');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flavors Billing Desk</title>
    <style>
        :root {
            --bg-main: #f4f6f9; --surface: #ffffff; --success: #10b981; --success-hover: #059669;
            --dark: #1e293b; --text-main: #334155; --text-muted: #64748b; --border: #e2e8f0; --radius: 12px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-main); padding: 2rem 1rem; }
        .container { max-width: 900px; margin: 0 auto; }
        header { display: flex; justify-content: space-between; align-items: center; background: var(--dark); color: white; padding: 1.25rem 2rem; border-radius: var(--radius); margin-bottom: 2rem; }
        .status-badge { background: #16a34a; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; color: white;}
        .tabs-nav { display: flex; gap: 0.5rem; background: #e2e8f0; padding: 0.4rem; border-radius: var(--radius); margin-bottom: 1.5rem; }
        .tab-link { flex: 1; padding: 0.75rem 1rem; text-align: center; color: var(--text-muted); font-weight: 600; text-decoration: none; border-radius: calc(var(--radius) - 4px); transition: all 0.2s; }
        .tab-link.active { background: var(--surface); color: #4f46e5; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .tab-content { background: var(--surface); padding: 2rem; border-radius: var(--radius); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid var(--border); }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--dark); font-size: 0.9rem; }
        .form-select { width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 8px; outline: none; background: white; font-size: 1rem; color: var(--text-main); }
        .btn { display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 0.85rem; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; background: var(--success); color: white; text-decoration: none; transition: background 0.2s; }
        .btn:hover { background: var(--success-hover); }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>🍽️ Flavors Terminal</h1>
        <?php if (isset($_GET['status']) && $_GET['status'] === 'settled'): ?>
            <span class="status-badge">✅ Invoice Settled</span>
        <?php endif; ?>
    </header>

    <div class="tabs-nav">
        <a href="index.php" class="tab-link">📝 Place Order</a>
        <a href="billing.php" class="tab-link active">🧾 Billing Desk</a>
        <a href="reports.php" class="tab-link">📊 Management Logs</a>
    </div>

    <div class="tab-content">
        <div style="max-width: 500px; margin: 0 auto;">
            <h3 style="margin-bottom: 1.5rem; color: var(--dark); font-size: 1.25rem; font-weight: 700;">Settle Table Invoice Accounts</h3>
            <form action="settle_invoice.php" method="POST">
                <div class="form-group">
                    <label>Active Target Table:</label>
                    <select class="form-select" name="table_id" required>
                        <option value="" disabled selected>-- Select Table to Clear --</option>
                        <?php foreach($tables as $t): ?>
                            <option value="<?= htmlspecialchars($t['id']); ?>">
                                <?= htmlspecialchars($t['table_number']); ?> <?= (trim($t['status']) === 'Occupied') ? '🔴 (Pending Order Open)' : '🟢 (Available)'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Settlement Payment Method:</label>
                    <select class="form-select" name="payment_mode" required>
                        <option value="Cash">Cash Payment</option>
                        <option value="Card">Card Swipe Terminal</option>
                        <option value="UPI">UPI Digital Transfer</option>
                    </select>
                </div>
                <button type="submit" class="btn">Finalize & Settle Invoice Statement</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
