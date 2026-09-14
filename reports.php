<?php 
// reports.php 
require_once 'includes/csv_helper.php';

$tables     = readCSV('tables.csv');
$categories = readCSV('categories.csv');
$menuItems  = readCSV('menu.csv');

// Map Category Names to IDs for easy listing lookups
$categoryMap = [];
foreach ($categories as $cat) {
    $categoryMap[trim($cat['id'])] = trim($cat['name']);
}
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
            --success: #10b981; --danger: #ef4444; --danger-hover: #dc2626;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-main); padding: 2rem 1rem; }
        .container { max-width: 1000px; margin: 0 auto; }
        header { display: flex; justify-content: space-between; align-items: center; background: var(--dark); color: white; padding: 1.25rem 2rem; border-radius: var(--radius); margin-bottom: 2rem; }
        .tabs-nav { display: flex; gap: 0.5rem; background: #e2e8f0; padding: 0.4rem; border-radius: var(--radius); margin-bottom: 1.5rem; }
        .tab-link { flex: 1; padding: 0.75rem 1rem; text-align: center; color: var(--text-muted); font-weight: 600; text-decoration: none; border-radius: calc(var(--radius) - 4px); transition: all 0.2s; }
        .tab-link.active { background: var(--surface); color: var(--primary); box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        
        /* Interactive Accordion Layout Containers */
        details.tab-content { 
            background: var(--surface); 
            border-radius: var(--radius); 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); 
            border: 1px solid var(--border); 
            margin-bottom: 1.5rem;
            overflow: hidden;
            display: block;
        }
        
        /* Modern Accordion Click Headers */
        summary.mgmt-title { 
            font-size: 1.25rem; 
            font-weight: 700; 
            color: var(--dark); 
            padding: 1.5rem 2rem; 
            cursor: pointer;
            list-style: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
            user-select: none;
            background: #fff;
            transition: background 0.2s;
        }
        summary.mgmt-title:hover {
            background: #f8fafc;
        }
        /* Custom dynamic down/up icon injection indicators */
        summary.mgmt-title::after {
            content: "▼";
            font-size: 0.85rem;
            color: var(--text-muted);
            transition: transform 0.2s;
        }
        details[open] summary.mgmt-title::after {
            transform: rotate(180deg);
        }
        
        /* Dropdown Panel Body Padding Wrap */
        .panel-body {
            padding: 0 2rem 2rem 2rem;
            border-top: 1px solid var(--border);
            margin-top: 0;
            padding-top: 1.5rem;
        }
        
        .btn { display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 0.85rem; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; text-decoration: none; margin-top: 1rem; }
        .btn-dark { background: var(--dark); color: white; }
        .btn-primary { background: var(--primary); color: white; margin-top: 0; }
        .btn-outline { border: 1px solid var(--border); background: transparent; color: var(--text-main); }
        .btn-outline:hover { background: #e2e8f0; }
        
        .btn-danger-sm { background: var(--danger); color: white; padding: 0.4rem 0.8rem; font-size: 0.85rem; width: auto; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block; }
        .btn-danger-sm:hover { background: var(--danger-hover); }

        .table-list { width: 100%; border-collapse: collapse; margin-top: 0.5rem; margin-bottom: 1rem; }
        .table-list th, .table-list td { padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--border); vertical-align: middle; }
        .table-list th { background: var(--bg-main); color: var(--dark); font-weight: 600; }
        
        .badge { padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600; }
        .badge-cat { background: #e0f2fe; color: #0369a1; }
        .badge-price { background: #d1fae5; color: #065f46; font-size: 0.9rem; }

        .form-row { display: flex; gap: 1rem; flex-wrap: wrap; background: var(--bg-main); padding: 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; align-items: flex-end; }
        .form-group { display: flex; flex-direction: column; gap: 0.4rem; flex: 1; min-width: 180px; }
        .form-group label { font-weight: 600; font-size: 0.85rem; color: var(--dark); }
        .form-control { padding: 0.75rem; border: 1px solid var(--border); border-radius: 6px; outline: none; background: white; font-size: 0.95rem; width: 100%; }
        .form-select { padding: 0.75rem; border: 1px solid var(--border); border-radius: 6px; outline: none; background: white; font-size: 0.95rem; width: 100%; }
        
        .action-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .action-card { text-align: center; padding: 1.5rem; background: var(--bg-main); border-radius: 8px; border: 1px solid var(--border); }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>🍽️ Flavors Terminal</h1>
        <?php if (isset($_GET['msg'])): ?>
            <span style="background: #16a34a; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; color: white;">
                <?php 
                    if($_GET['msg'] === 'cat_added') echo "📂 Category Added Successfully";
                    if($_GET['msg'] === 'cat_deleted') echo "🗑️ Category Removed Successfully";
                    if($_GET['msg'] === 'item_added') echo "🍲 Food Item Saved Successfully";
                    if($_GET['msg'] === 'item_deleted') echo "🗑️ Food Item Removed Successfully";
                    if($_GET['msg'] === 'added') echo "➕ Table Created Successfully";
                    if($_GET['msg'] === 'deleted') echo "🗑️ Table Removed Successfully";
                ?>
            </span>
        <?php endif; ?>
    </header>

    <div class="tabs-nav">
        <a href="index.php" class="tab-link">📝 Place Order</a>
        <a href="billing.php" class="tab-link">🧾 Billing Desk</a>
        <a href="reports.php" class="tab-link active">📊 Management Desk</a>
    </div>

    <!-- 1. CATEGORY ACCORDION -->
    <details class="tab-content" >
        <summary class="mgmt-title">📂 Menu Category Management</summary>
        <div class="panel-body">
            <?php require_once 'includes/mgmt/category_panel.php'; ?>
        </div>
    </details>

    <!-- 2. FOOD CATALOG ACCORDION -->
    <details class="tab-content" >
        <summary class="mgmt-title">🍲 Food Catalog Configuration Engine</summary>
        <div class="panel-body">
            <?php require_once 'includes/mgmt/food_panel.php'; ?>
        </div>
    </details>

    <!-- 3. TABLE SEATING ACCORDION -->
    <details class="tab-content" >
        <summary class="mgmt-title">🪑 Floor Plan Table Configuration Desk</summary>
        <div class="panel-body">
            <?php require_once 'includes/mgmt/table_panel.php'; ?>
        </div>
    </details>

    <!-- 4. UTILITIES & REPORTS ACCORDION -->
    <details class="tab-content" >
        <summary class="mgmt-title">📊 Financial Utilities & Reports</summary>
        <div class="panel-body">
            <?php require_once 'includes/mgmt/utility_panel.php'; ?>
        </div>
    </details>

</div>
</body>
</html>
