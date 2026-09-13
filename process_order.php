<?php
// process_order.php
require_once 'includes/csv_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['items'])) {
    $table_id = $_POST['table_id'];
    $selectedItems = $_POST['items'];

    // Compress item fields to look directly for selected item totals
    $orderedPayload = array_filter($selectedItems, function($qty) { return $qty > 0; });

    if (empty($orderedPayload)) {
        die("Error: Please select at least one item before updating the kitchen queue. <a href='index.php'>Go Back</a>");
    }

    $orderId = getNextID('orders.csv');
    $serializedItems = base64_encode(json_encode($orderedPayload)); // Safe CSV packaging injection

    // Append order pipeline tracking data
    // Scheme: id, table_id, encoded_items, status
    writeCSV('orders.csv', [$orderId, $table_id, $serializedItems, 'Pending'], ['id', 'table_id', 'items', 'status']);

    // Flip table designation status tracking matrix records
    $tables = readCSV('tables.csv');
    foreach ($tables as &$t) {
        if ($t['id'] == $table_id) {
            $t['status'] = 'Occupied';
        }
    }
    rewriteFullCSV('tables.csv', $tables, ['id', 'table_number', 'capacity', 'status']);

    header('Location: billing.php?status=settled');
    exit();
}
