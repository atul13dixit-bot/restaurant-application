<?php // includes/mgmt/table_panel.php ?>
<div class="tab-content">
    
    <!-- Add Table Form -->
    <form action="process_table_action.php?action=add" method="POST" class="form-row">
        <div class="form-group">
            <label>Table Descriptor / Name:</label>
            <input type="text" name="table_number" placeholder="e.g. Table 04" required class="form-control">
        </div>
        <div class="form-group">
            <label>Guest Capacity (Max Pax):</label>
            <input type="number" name="capacity" min="1" placeholder="4" required class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">➕ Create Table</button>
    </form>

    <table class="table-list">
        <thead>
            <tr>
                <th>Table Name</th>
                <th>Max Capacity</th>
                <th style="text-align: right;">Administrative Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tables as $t): ?>
                <tr>
                    <td style="font-weight: 600; color: var(--dark);"><strong><?= htmlspecialchars($t['table_number']); ?></strong></td>
                    <td><?= htmlspecialchars($t['capacity']); ?> Seated Pax</td>
                    <td style="text-align: right;">
                        <a href="process_table_action.php?action=delete&id=<?= urlencode($t['id']); ?>" 
                           class="btn-danger-sm" onclick="return confirm('Remove this structural table item?');">🗑️ Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
