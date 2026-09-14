<?php
// process_table_action.php
require_once 'includes/csv_helper.php';

$action = isset($_GET['action']) ? trim($_GET['action']) : '';

// --- ROUTE 1: APPENDING A NEW ROW RECORD DATA ---
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $table_number = trim($_POST['table_number']);
    $capacity     = trim($_POST['capacity']);

    if (empty($table_number) || empty($capacity)) {
        die("Error: Form parameters cannot be blank. <a href='reports.php'>Go Back</a>");
    }

    $newId = getNextID('tables.csv');
    
    // Scheme definition: id, table_number, capacity, status
    writeCSV('tables.csv', 
        [$newId, $table_number, $capacity, 'Available'], 
        ['id', 'table_number', 'capacity', 'status']
    );

    header('Location: reports.php?msg=added');
    exit();
}

// --- ROUTE 2: PERMANENTLY REMOVING A TABLE RECORD ROW ---
if ($action === 'delete' && isset($_GET['id'])) {
    $table_id = trim($_GET['id']);
    $tables = readCSV('tables.csv');
    
    $filteredTables = [];
    $found = false;

    foreach ($tables as $t) {
        if (trim($t['id']) === $table_id) {
            $found = true;
            continue; // Skip appending this row to drop it entirely
        }
        $filteredTables[] = $t;
    }

    if ($found) {
        rewriteFullCSV('tables.csv', $filteredTables, ['id', 'table_number', 'capacity', 'status']);
        header('Location: reports.php?msg=deleted');
        exit();
    } else {
        die("Error: Target Table ID not located inside file system registries. <a href='reports.php'>Go Back</a>");
    }
}

// Fallback protection redirection boundary
header('Location: reports.php');
exit();
