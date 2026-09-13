<?php 
// reports.php 
require_once 'includes/csv_helper.php';
$tables = readCSV('tables.csv');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flavors Management Logs</title>
    <style>
        :root {
            --bg-main: #f4f6f9; --surface: #ffffff; --dark: #1e293b; --text-main: #334155;
            --text-muted: #64748b; --border: #e2e8f0; --radius: 12px; --primary: #4f46e5;
            --danger: #ef4444; --danger-hover: #dc2626;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-main); padding: 2rem 1rem; }
        .container { max-width: 900px; margin: 0 auto; }
        header { display: flex; justify-content: space-between; align-items: center; background: var(--dark); color: white; padding: 1.25rem 2rem; border-radius: var(--radius); margin-bottom: 2rem; }
        .tabs-nav { display: flex; gap: 0.5rem; background: #e2e8f0; padding: 0.4rem; border-radius: var(--radius); margin-bottom: 1.5rem; }
        .tab-link { flex: 1; padding: 0.75rem 1rem; text-align: center; color: var(--text-muted); font-weight: 600; text-decoration: none; border-radius: calc(var(--radius) - 4px); transition: all 0.2s; }
        .tab-link.active { background: var(--surface); color: var(--primary); box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .tab-content { background: var(--surface); padding: 2rem; border-radius: var(--radius); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid var(--border); margin-bottom: 1.5rem; }
        .action-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .action-card { text-align: center; padding: 1.5rem; background: var(--bg-main); border-radius: 8px; border: 1px solid var(--border); }
        .btn { display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 0.85rem; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; text-decoration: none; margin-top: 1rem; }
        .btn-dark { background: var(--dark); color: white; }
        .btn-outline { border: 1px solid var(--border); background: transparent; color: var(--text-main); }
        .btn-outline:hover { background: #e2e8f0; }
        .btn-danger { background: var(--danger); color: white; padding: 0.4rem 0.8rem; font-size: 0.85rem; width: auto; border-radius: 6px; margin-top: 0; }
        .btn-danger:hover { background: var(--danger-hover); }
        
        /* Table Layout Matrix styling */
        .mgmt-title { font-size: 1.25rem; font-weight: 700; color: var(--dark); margin-bottom: 1rem; }
        .table-list { width: 100%; border-collapse: collapse; margin-top: 0.5rem; }
        .table-list th, .table-list td { padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--border); }
        .table-list th { background: var(--bg-main); color: var(--dark); font-weight: 600; }
        .badge { padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600; }
        .badge-occupied { background: #fee2e2; color: #ef4444; }
        .badge-available { background: #d1fae5; color: #10b981; }
        .badge-status { background: #fef3c7; color: #d97706; }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>🍽️ Flavors Terminal</h1>
        <?php if (isset($_GET['status']) && $_GET['status'] === 'cleared'): ?>
            <span style="background: #16a34a; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; color: white;">🔄 Table Reset Complete</span>
        <?php endif; ?>
    </header>

    <div class="tabs-nav">
        <a href="index.php" class="tab-link">📝 Place Order</a>
        <a href="billing.php" class="tab-link">🧾 Billing Desk</a>
        <a href="reports.php" class="tab-link active">📊 Management Logs</a>
    </div>

    <!-- Section A: Financial Utilities -->
    <div class="tab-content">
        <h3 class="mgmt-title">Data Exports & History</h3>
        <div class="action-grid">
            <div class="action-card">
                <h4>📜 Historical Records</h4>
                <p style="color: var(--text-muted); font-size: 0.85rem;">View, browse and manage previously generated invoices and receipts.</p>
                <a href="invoices_list.php" class="btn btn-dark">Open Invoice Ledger Vault</a>
            </div>
            <div class="action-card">
                <h4>📊 Spreadsheet Reports</h4>
                <p style="color: var(--text-muted); font-size: 0.85rem;">Export operational transaction histories directly into Excel formats.</p>
                <a href="export_report.php" class="btn btn-outline">Download Excel Data Report</a>
            </div>
        </div>
    </div>

    <!-- Section B: Administrative Table Override Board -->
    <div class="tab-content">
        <h3 class="mgmt-title">⚙️ Table Operational Grid (Status Overrides)</h3>
        <p class="small text-muted mb-3" style="font-size: 0.85rem; margin-bottom: 1rem;">Use the override controls below to release stuck tables or clear expired bookings.</p>
        
        <table class="table-list">
            <thead>
                <tr>
                    <th>Table Name</th>
                    <th>Seating Capacity</th>
                    <th>Current Status Status</th>
                    <th>Administrative Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tables as $t): ?>
                    <tr>
                        <td class="fw-bold" style="font-weight:600; color: var(--dark);"><?= htmlspecialchars($t['table_number']); ?></td>
                        <td><?= htmlspecialchars($t['capacity']); ?> Guests</td>
                        <td>
                            <?php if (trim($t['status']) === 'Occupied'): ?>
                                <span class="badge badge-occupied">🔴 Occupied / Booked</span>
                            <?php elseif (trim($t['status']) === 'Reserved'): ?>
                                <span class="badge badge-status">🟡 Reserved</span>
                            <?php else: ?>
                                <span class="badge badge-available">🟢 Available</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (trim($t['status']) !== 'Available'): ?>
                                <a href="reset_table.php?id=<?= urlencode($t['id']); ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Force release this table back to Available status? Any pending order flag will be bypassed.');">
                                   🔓 Clear & Release Table
                                </a>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-size: 0.85rem; font-style: italic;">No actions needed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
