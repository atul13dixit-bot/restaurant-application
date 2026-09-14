<?php // includes/mgmt/category_panel.php ?>
<div class="tab-content">
    
    <!-- Add Category Form -->
    <form action="process_menu_action.php?action=add_category" method="POST" class="form-row">
        <div class="form-group" style="flex: 3;">
            <label>New Category Name:</label>
            <input type="text" name="category_name" placeholder="e.g. Main Course, Desserts, Breads" required class="form-control">
        </div>
        <button type="submit" class="btn btn-primary" style="flex: 1;">➕ Add Category</button>
    </form>

    <table class="table-list">
        <thead>
            <tr>
                <th>Category ID</th>
                <th>Category Classification Name</th>
                <th style="text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><strong>#<?= htmlspecialchars($cat['id']); ?></strong></td>
                    <td style="font-weight: 600; color: var(--dark);"><?= htmlspecialchars($cat['name']); ?></td>
                    <td style="text-align: right;">
                        <a href="process_menu_action.php?action=delete_category&id=<?= urlencode($cat['id']); ?>" 
                           class="btn-danger-sm" onclick="return confirm('Deleting this group might orphan associated items! Proceed?');">
                           🗑️ Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
