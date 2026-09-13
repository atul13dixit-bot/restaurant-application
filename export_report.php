<?php
// export_report.php
require_once 'includes/csv_helper.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Sales_Ledger_Export_' . date('Y-m-d') . '.csv');

// Stream standard output parameters straight out to downstream client windows
$out = fopen('php://output', 'w');

// Add descriptive row descriptors manually to help sort your spreadsheet
fputcsv($out, ['Invoice Serial Num', 'Internal Order System Identifier ID', 'Subtotal Balance ($)', 'Taxes Aggregated ($)', 'Total Net Volume ($)', 'Settlement Instrument Used', 'System Processing Timestamp']);

$invoices = readCSV('invoices.csv');

foreach ($invoices as $inv) {
    fputcsv($out, array_values($inv));
}

fclose($out);
exit();
