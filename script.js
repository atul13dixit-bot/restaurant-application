// script.js - Core POS Ordering Engine Frontend Controller

// Local state cache tracking selected items: { itemId: { name, price, qty } }
const activeQueue = {}; 

/**
 * Dynamically populates the item select dropdown array based on chosen category row parameters
 * Called automatically by the category dropdown container 'onchange' event trigger
 */
function updateItemDropdown() {
    const catSelector = document.getElementById('catSelector');
    const itemSelector = document.getElementById('itemSelector');
    
    if (!catSelector || !itemSelector) return;
    
    const selectedCat = catSelector.value;
    itemSelector.innerHTML = '';
    
    // Fallback verification if global dictionary menuData fails to load
    if (typeof menuData === 'undefined' || !menuData[selectedCat] || menuData[selectedCat].length === 0) {
        itemSelector.innerHTML = '<option value="" disabled selected>-- No Items Found --</option>';
        return;
    }

    itemSelector.innerHTML = '<option value="" disabled selected>-- Select Item --</option>';
    menuData[selectedCat].forEach(item => {
        const option = document.createElement('option');
        option.value = item.id;
        option.textContent = `${item.name} (₹${item.price.toFixed(2)})`;
        option.dataset.name = item.name;
        option.dataset.price = item.price;
        itemSelector.appendChild(option);
    });
}

/**
 * Appends an item object row array or increments the quantity count inside local memory queue
 */
function addItemToQueue() {
    const itemSelector = document.getElementById('itemSelector');
    if (!itemSelector) return;
    
    const itemId = itemSelector.value;

    if (!itemId) {
        alert('Please select a valid food or beverage item description before adding.');
        return;
    }

    const selectedOption = itemSelector.options[itemSelector.selectedIndex];
    const itemName = selectedOption.dataset.name;
    const itemPrice = parseFloat(selectedOption.dataset.price);

    // If item exists increment tracking count values, otherwise construct new object fields
    if (activeQueue[itemId]) {
        activeQueue[itemId].qty += 1;
    } else {
        activeQueue[itemId] = { name: itemName, price: itemPrice, qty: 1 };
    }

    renderQueueTable();
}

/**
 * Adjust quantities values sequentially using step up/down button references
 */
function modifyQty(itemId, amount) {
    if (!activeQueue[itemId]) return;
    
    activeQueue[itemId].qty += amount;
    
    // Completely purge record row keys if tracking totals drop below zero integers
    if (activeQueue[itemId].qty <= 0) {
        delete activeQueue[itemId];
    }
    
    renderQueueTable();
}

/**
 * Completely drop a tracking item index directly out of active arrays
 */
function deleteItem(itemId) {
    if (activeQueue[itemId]) {
        delete activeQueue[itemId];
        renderQueueTable();
    }
}

/**
 * Re-renders the entire visible queue HTML table UI based on structural state patterns
 */
function renderQueueTable() {
    const queueBody = document.getElementById('queueBody');
    if (!queueBody) return;
    
    queueBody.innerHTML = '';
    const keys = Object.keys(activeQueue);

    // Render baseline placeholder row if state arrays are empty
    if (keys.length === 0) {
        queueBody.innerHTML = '<tr id="emptyRowPlaceholder"><td colspan="5" style="color: #64748b; text-align: center; font-style: italic; padding: 1rem;">No items added to the order queue yet.</td></tr>';
        return;
    }

    // Unroll cached tracking parameters into physical row items elements mapping
    keys.forEach(itemId => {
        const item = activeQueue[itemId];
        const totalCost = item.price * item.qty;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="font-weight: 600; color: #1e293b; padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0;">${item.name}</td>
            <td style="padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0;">₹${item.price.toFixed(2)}</td>
            <td style="text-align: center; padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0;">
                <div style="display: inline-flex; align-items: center; gap: 0.25rem;">
                    <button type="button" style="background: #e2e8f0; border: none; width: 28px; height: 28px; border-radius: 4px; font-weight: bold; cursor: pointer;" onclick="modifyQty('${itemId}', -1)">-</button>
                    <input type="text" name="items[${itemId}]" value="${item.qty}" style="width: 40px; text-align: center; font-weight: 700; border: none; background: transparent; pointer-events: none;" readonly>
                    <button type="button" style="background: #e2e8f0; border: none; width: 28px; height: 28px; border-radius: 4px; font-weight: bold; cursor: pointer;" onclick="modifyQty('${itemId}', 1)">+</button>
                </div>
            </td>
            <td style="text-align: right; font-weight: 600; color: #4f46e5; padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0;">$${totalCost.toFixed(2)}</td>
            <td style="text-align: center; padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0;">
                <button type="button" style="background: transparent; border: none; color: #ef4444; font-weight: 600; cursor: pointer; font-size: 0.85rem;" onclick="deleteItem('${itemId}')">🗑️ Remove</button>
            </td>
        `;
        queueBody.appendChild(tr);
    });
}

// Intercept form submissions validation triggers once DOM loads completely
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('orderMasterForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (Object.keys(activeQueue).length === 0) {
                e.preventDefault();
                alert('Your order list is completely empty! Please select and add items before sending to the kitchen.');
            }
        });
    }
});
