<?php
// reset_table.php
require_once 'includes/csv_helper.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $table_id = trim($_GET['id']);

    // 1. Force release the table state entry inside tables.csv
    $tables = readCSV('tables.csv');
    foreach ($tables as &$t) {
        if (trim($t['id']) === $table_id) {
            $t['status'] = 'Available';
        }
    }
    unset($t);
    rewriteFullCSV('tables.csv', $tables, ['id', 'table_number', 'capacity', 'status']);

    // 2. Clear any lingering unbilled "Pending" orders for this table to prevent system confusion
    $orders = readCSV('orders.csv');
    $orderUpdated = false;
    foreach ($orders as &$o) {
        if (trim($o['table_id']) === $table_id && trim($o['status']) === 'Pending') {
            $o['status'] = 'Cancelled'; // Mark leftover unbilled items as canceled
            $orderUpdated = true;
        }
    }
    unset($o);
    
    if ($orderUpdated) {
        rewriteFullCSV('orders.csv', $orders, ['id', 'table_id', 'items', 'status']);
    }

    // Redirect cleanly back to the management interface tab
    header('Location: reports.php?status=cleared');
    exit();
} else {
    header('Location: reports.php');
    exit();
}
