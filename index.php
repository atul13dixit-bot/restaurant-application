<?php
// index.php
require_once 'includes/csv_helper.php';

$categories = readCSV('categories.csv');
$menuItems  = readCSV('menu.csv');
$tables     = readCSV('tables.csv');

// Group menu items by category ID in a structured PHP array for clean JavaScript handling
$groupedMenu = [];
foreach ($menuItems as $item) {
    if (trim($item['status']) === 'Available') {
        $catId = trim($item['category_id']);
        $groupedMenu[$catId][] = [
            'id' => trim($item['id']),
            'name' => trim($item['name']),
            'price' => (float)trim($item['price'])
        ];
    }
}
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
            --success: #10b981; --success-hover: #059669; --dark: #1e293b; --text-main: #334155; --text-muted: #64748b;
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
        .form-select, .form-control { width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 8px; outline: none; background: white; font-size: 0.95rem; }
        
        .picker-box { display: flex; gap: 1rem; flex-wrap: wrap; background: #f8fafc; padding: 1.25rem; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 1.5rem; align-items: flex-end; }
        .picker-group { display: flex; flex-direction: column; gap: 0.4rem; flex: 2; min-width: 200px; }
        
        .btn-add { background: var(--primary); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer; height: 43px; display: inline-flex; align-items: center; justify-content: center; gap: 0.25rem; transition: background 0.2s; }
        .btn-add:hover { background: var(--primary-hover); }
        
        .order-table { width: 100%; border-collapse: collapse; margin-top: 1rem; text-align: left; }
        .order-table th, .order-table td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
        .order-table th { background: #f1f5f9; color: var(--dark); font-weight: 600; font-size: 0.85rem; text-uppercase: true; }
        
        .btn-submit { display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 0.85rem; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; text-decoration: none; background: var(--success); color: white; margin-top: 1.5rem; transition: background 0.2s; }
        .btn-submit:hover { background: var(--success-hover); }
        .empty-row-text { color: var(--text-muted); text-align: center; font-style: italic; padding: 1rem; }
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
        <a href="reports.php" class="tab-link">📊 Management Desk</a>
    </div>

    <div class="tab-content">
        <form action="process_order.php" method="POST" id="orderMasterForm">
            <!-- 1. Table Selection -->
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

            <!-- 2. Dynamic Selector Panel Controls -->
            <label>2. Build Menu Order Items</label>
            <div class="picker-box">
                <div class="picker-group" style="flex: 1;">
                    <label style="font-size: 0.8rem; color: var(--text-muted);">Category Type:</label>
                    <select class="form-select" id="catSelector" onchange="updateItemDropdown()">
                        <option value="" disabled selected>-- Select Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['id']); ?>"><?= htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="picker-group" style="flex: 2;">
                    <label style="font-size: 0.8rem; color: var(--text-muted);">Available Dishes:</label>
                    <select class="form-select" id="itemSelector">
                        <option value="" disabled selected>-- Choose Category First --</option>
                    </select>
                </div>
                
                <button type="button" class="btn-add" onclick="addItemToQueue()">➕ Add Item</button>
            </div>

            <!-- 3. Active Order Table Queue Breakdown Grid -->
            <table class="order-table" id="queueTable">
                <thead>
                    <tr>
                        <th>Dish Item Description</th>
                        <th>Unit Rate</th>
                        <th style="text-align: center; width: 140px;">Quantity</th>
                        <th style="text-align: right;">Total Cost</th>
                        <th style="text-align: center; width: 80px;">Action</th>
                    </tr>
                </thead>
                <tbody id="queueBody">
                    <tr id="emptyRowPlaceholder"><td colspan="5" class="empty-row-text">No items added to the order queue yet.</td></tr>
                </tbody>
            </table>

            <button type="submit" class="btn-submit">🚀 Send Selected Order To Kitchen</button>
        </form>
    </div>
</div>

<script>
    // Safely parse the compiled menu dictionary registry down to your external script engine cache
    const menuData = <?= json_encode($groupedMenu); ?>;
</script>
<!-- 🔥 LINKED EXTERNAL ENGINE MODULE -->
<script src="script.js"></script>
</body>
</html>
