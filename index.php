<?php
// index.php
require_once 'includes/csv_helper.php';

$categories = readCSV('categories.csv');
$menuItems  = readCSV('menu.csv');
$tables     = readCSV('tables.csv');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flavors POS Terminal</title>
    <style>
        :root {
            --bg-main: #f4f6f9; --surface: #ffffff; --primary: #4f46e5; --primary-hover: #4338ca;
            --success: #10b981; --dark: #1e293b; --text-main: #334155; --text-muted: #64748b;
            --border: #e2e8f0; --radius: 12px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-main); padding: 2rem 1rem; }
        .container { max-width: 900px; margin: 0 auto; }
        header { display: flex; justify-content: space-between; align-items: center; background: var(--dark); color: white; padding: 1.25rem 2rem; border-radius: var(--radius); margin-bottom: 2rem; }
        .status-badge { background: #2563eb; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
        .tabs-nav { display: flex; gap: 0.5rem; background: #e2e8f0; padding: 0.4rem; border-radius: var(--radius); margin-bottom: 1.5rem; }
        .tab-link { flex: 1; padding: 0.75rem 1rem; text-align: center; color: var(--text-muted); font-weight: 600; text-decoration: none; border-radius: calc(var(--radius) - 4px); transition: all 0.2s; }
        .tab-link.active { background: var(--surface); color: var(--primary); box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .tab-content { background: var(--surface); padding: 2rem; border-radius: var(--radius); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid var(--border); }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--dark); font-size: 0.9rem; }
        .form-select, .form-control { width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 8px; outline: none; }
        .category-title { font-size: 1.1rem; font-weight: 700; color: var(--dark); margin: 1.5rem 0 0.75rem 0; border-bottom: 2px solid var(--bg-main); padding-bottom: 0.25rem; }
        .menu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1rem; }
        .menu-card { border: 1px solid var(--border); border-radius: 8px; padding: 1rem; background: var(--bg-main); display: flex; justify-content: space-between; align-items: center; }
        .menu-info strong { display: block; color: var(--dark); }
        .menu-info span { color: var(--success); font-weight: 600; }
        .qty-input { width: 60px; text-align: center; font-weight: bold; padding: 0.4rem; }
        .btn { display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 0.85rem; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; text-decoration: none; background: var(--primary); color: white; }
        .btn:hover { background: var(--primary-hover); }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>🍽️ Flavors Terminal</h1>
        <?php if (isset($_GET['status']) && $_GET['status'] === 'ordered'): ?>
            <span class="status-badge">📦 Sent to Kitchen</span>
        <?php endif; ?>
    </header>

    <div class="tabs-nav">
        <a href="index.php" class="tab-link active">📝 Place Order</a>
        <a href="billing.php" class="tab-link">🧾 Billing Desk</a>
        <a href="reports.php" class="tab-link">📊 Management Logs</a>
    </div>

    <div class="tab-content">
        <form action="process_order.php" method="POST">
            <div class="form-group">
                <label>1. Assign Order Target Table</label>
                <select class="form-select" name="table_id" required>
                    <option value="" disabled selected>-- Choose Table --</option>
                    <?php foreach($tables as $t): ?>
                        <option value="<?= htmlspecialchars($t['id']); ?>">
                            <?= htmlspecialchars($t['table_number']); ?> (<?= htmlspecialchars($t['status']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <label>2. Select Menu Items & Quantities</label>
            <?php foreach ($categories as $cat): ?>
                <div class="category-title"><?= htmlspecialchars($cat['name']); ?> Categories</div>
                <div class="menu-grid">
                    <?php foreach ($menuItems as $item): 
                        if(trim($item['category_id']) === trim($cat['id']) && trim($item['status']) === 'Available'): ?>
                        <div class="menu-card">
                            <div class="menu-info">
                                <strong><?= htmlspecialchars($item['name']); ?></strong>
                                <span>$<?= number_format((float)$item['price'], 2); ?></span>
                            </div>
                            <input type="number" name="items[<?= htmlspecialchars($item['id']); ?>]" value="0" min="0" class="form-control qty-input">
                        </div>
                    <?php endif; endforeach; ?>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="btn" style="margin-top: 2rem;">Send Order To Kitchen Matrix</button>
        </form>
    </div>
</div>
</body>
</html>
