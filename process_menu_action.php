<?php
// process_menu_action.php
require_once 'includes/csv_helper.php';

$action = isset($_GET['action']) ? trim((string)$_GET['action']) : '';

// 1. ADD NEW CLASSIFICATION GROUPS
if ($action === 'add_category' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = isset($_POST['category_name']) ? trim((string)$_POST['category_name']) : '';
    
    if ($category_name !== '') {
        $newId = getNextID('categories.csv');
        writeCSV('categories.csv', [$newId, $category_name], ['id', 'name']);
        header('Location: reports.php?msg=cat_added');
        exit();
    }
    die("Error: Category name cannot be empty. <a href='reports.php'>Go Back</a>");
}

// 2. DELETE CLASSIFICATION GROUPS
if ($action === 'delete_category' && isset($_GET['id'])) {
    $cat_id = trim((string)$_GET['id']);
    $categories = readCSV('categories.csv');
    $filtered = [];
    
    foreach ($categories as $cat) {
        if (isset($cat['id']) && trim((string)$cat['id']) !== $cat_id) { 
            $filtered[] = $cat; 
        }
    }
    rewriteFullCSV('categories.csv', $filtered, ['id', 'name']);
    header('Location: reports.php?msg=cat_deleted');
    exit();
}

// 3. ADD INDEPENDENT DISH ROW RECORDS
if ($action === 'add_item' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = isset($_POST['category_id']) ? trim((string)$_POST['category_id']) : '';
    $item_name   = isset($_POST['item_name']) ? trim((string)$_POST['item_name']) : '';
    $price       = isset($_POST['price']) ? trim((string)$_POST['price']) : '';

    if ($category_id !== '' && $item_name !== '' && $price !== '') {
        $newItemId = getNextID('menu.csv');
        // Schema definition sequence: id, category_id, name, price, status
        writeCSV('menu.csv', 
            [$newItemId, $category_id, $item_name, $price, 'Available'], 
            ['id', 'category_id', 'name', 'price', 'status']
        );
        header('Location: reports.php?msg=item_added');
        exit();
    }
    die("Error: All fields are required to save an item. <a href='reports.php'>Go Back</a>");
}

// 4. PERMANENTLY REMOVE INDEPENDENT DISH RECORDS
if ($action === 'delete_item' && isset($_GET['id'])) {
    $item_id = trim((string)$_GET['id']);
    $menuItems = readCSV('menu.csv');
    $filtered = [];
    
    foreach ($menuItems as $item) {
        if (isset($item['id']) && trim((string)$item['id']) !== $item_id) { 
            $filtered[] = $item; 
        }
    }
    rewriteFullCSV('menu.csv', $filtered, ['id', 'category_id', 'name', 'price', 'status']);
    header('Location: reports.php?msg=item_deleted');
    exit();
}

header('Location: reports.php');
exit();
