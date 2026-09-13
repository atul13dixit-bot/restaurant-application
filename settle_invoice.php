<?php
// settle_invoice.php
require_once 'includes/csv_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['table_id']) || empty($_POST['table_id'])) {
        die("Error: Please select a valid table from the dropdown dashboard. <a href='billing.php'>Go Back</a>");
    }

    $table_id = trim($_POST['table_id']);
    $payment_mode = isset($_POST['payment_mode']) ? trim($_POST['payment_mode']) : 'Cash';

    // 1. Scan and Locate matching Pending Orders
    $orders = readCSV('orders.csv');
    $targetOrderKey = null;
    
    foreach ($orders as $key => $o) {
        if (trim($o['table_id']) === $table_id && trim($o['status']) === 'Pending') {
            $targetOrderKey = $key;
            break;
        }
    }

    if ($targetOrderKey === null) {
        die("Notice: This table has no open orders active in the system pipeline. It might already be settled! <a href='billing.php'>Go Back</a>");
    }

    // 2. Extract and Decode Item Objects
    $orderData = $orders[$targetOrderKey];
    $quantities = json_decode(base64_decode($orderData['items']), true);
    
    if (!is_array($quantities)) {
        die("Error: Order structural matrix data corrupt. <a href='billing.php'>Go Back</a>");
    }

    // 3. Calculate Financial Metrics from Menu Master File
    $menu = readCSV('menu.csv');
    $subtotal = 0.00;
    
    foreach ($quantities as $itemId => $qty) {
        foreach ($menu as $mItem) {
            if (trim($mItem['id']) === trim($itemId)) {
                $subtotal += (float)$mItem['price'] * (int)$qty;
            }
        }
    }

    $tax = $subtotal * 0.05; 
    $grandTotal = $subtotal + $tax;
    
    // Generate the unique invoice ID sequentially
    $invoiceId = getNextID('invoices.csv');

    // 4. Append Settle Records directly to Invoice Ledgers
    writeCSV('invoices.csv', 
        [$invoiceId, $orderData['id'], $subtotal, $tax, $grandTotal, $payment_mode, date('Y-m-d H:i:s')],
        ['id', 'order_id', 'subtotal', 'tax', 'grand_total', 'payment_mode', 'created_at']
    );

    // 5. Flip Order State Flag down to 'Paid'
    $orders[$targetOrderKey]['status'] = 'Paid';
    rewriteFullCSV('orders.csv', $orders, ['id', 'table_id', 'items', 'status']);

    // 6. Re-initialize Table availability states back to 'Available'
    $tables = readCSV('tables.csv');
    foreach ($tables as &$t) {
        if (trim($t['id']) === $table_id) {
            $t['status'] = 'Available';
        }
    }
    unset($t);
    rewriteFullCSV('tables.csv', $tables, ['id', 'table_number', 'capacity', 'status']);

    // 🔥 DYNAMIC REDIRECT: Automatically jump straight into the newly created itemized bill layout!
    header('Location: view_invoice.php?id=' . urlencode($invoiceId));
    exit();
} else {
    header('Location: billing.php');
    exit();
}
