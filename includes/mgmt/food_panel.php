<?php // includes/mgmt/food_panel.php ?>
<div class="tab-content">    
    <!-- Add Food Item Form -->
    <form action="process_menu_action.php?action=add_item" method="POST" class="form-row">
        <div class="form-group">
            <label>Parent Category Group:</label>
            <select name="category_id" required class="form-select">
                <option value="" disabled selected>-- Map to Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['id']); ?>"><?= htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="flex: 2;">
            <label>Dish / Beverage Name Description:</label>
            <input type="text" name="item_name" placeholder="e.g. Chhole Bhature or Paneer do Pyaja" required class="form-control">
        </div>
        <div class="form-group">
            <label>Base Price (₹):</label>
            <input type="number" name="price" step="0.01" min="0.00" placeholder="0.00" required class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">🍲 Save Item</button>
    </form>

    <table class="table-list">
        <thead>
            <tr>
                <th>Dish Title Name</th>
                <th>Category Type</th>
                <th>Base Price Rate</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($menuItems as $item): 
                $catId = trim($item['category_id']);
                $resolvedCatName = isset($categoryMap[$catId]) ? $categoryMap[$catId] : 'Unknown Group';
            ?>
                <tr>
                    <td style="font-weight: 600; color: var(--dark);"><?= htmlspecialchars($item['name']); ?></td>
                    <td><span class="badge badge-cat"><?= htmlspecialchars($resolvedCatName); ?></span></td>
                    <td><span class="badge badge-price">₹<?= number_format((float)$item['price'], 2); ?></span></td>
                    <td style="text-align: right;">
                        <a href="process_menu_action.php?action=delete_item&id=<?= urlencode($item['id']); ?>" 
                           class="btn-danger-sm" onclick="return confirm('Remove this food item from active grids?');">
                           🗑️ Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
