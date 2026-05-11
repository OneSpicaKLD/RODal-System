/* 
   RODAL SYSTEM - SWEETALERT UX CONFIG
   Ino-overwrite nito lahat ng Swal calls para maging uniform ang design.
*/
const RodalSwal = Swal.mixin({
    customClass: {
        popup: 'rodal-swal-popup',
        confirmButton: 'rodal-swal-confirm',
        cancelButton: 'rodal-swal-cancel',
        title: 'rodal-swal-title'
    },
    buttonsStyling: false // Disable default styles para sumunod sa CSS natin
});

// Ito ang pinaka-importante: Ino-overwrite ang window.Swal object
window.Swal = RodalSwal;

let mySalesChart;

document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('revenueChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    const labels = JSON.parse(canvas.getAttribute('data-weekly-labels'));
    const dataValues = JSON.parse(canvas.getAttribute('data-weekly-values'));

    mySalesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue',
                data: dataValues,
                borderColor: '#f1c40f',
                tension: 0.4,
                fill: true,
                backgroundColor: 'rgba(241, 196, 15, 0.1)',
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: (val) => '₱' + val.toLocaleString() }
                }
            }
        }
    });
});

function updateChart(timeframe) {
    console.log("Switching to:", timeframe);

    const canvas = document.getElementById('revenueChart');
    if (!canvas || !mySalesChart) {
        console.error("Chart or Canvas not found!");
        return;
    }

    const labels = JSON.parse(canvas.getAttribute(`data-${timeframe}-labels`));
    const values = JSON.parse(canvas.getAttribute(`data-${timeframe}-values`));

    mySalesChart.data.labels = labels;
    mySalesChart.data.datasets[0].data = values;
    mySalesChart.data.datasets[0].label = (timeframe === 'weekly') ? 'Daily Revenue' : 'Monthly Revenue';

    mySalesChart.update();

    document.querySelectorAll('.chart-btn').forEach(btn => btn.classList.remove('active'));


    const targetBtn = document.getElementById(`btn-${timeframe}`);
    if (targetBtn) targetBtn.classList.add('active');
}


document.addEventListener('DOMContentLoaded', () => {
    const notifBtn = document.getElementById('notifBtn');
    const notifDropdown = document.getElementById('notifDropdown');
    const badge = document.querySelector('.notif-badge');

    notifBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        notifDropdown.classList.toggle('active');

        document.getElementById('profileDropdown')?.classList.remove('show');
    });

    document.addEventListener('click', (e) => {
        if (!notifBtn.contains(e.target) && !notifDropdown.contains(e.target)) {
            console.log("Hello world");
            notifDropdown.classList.remove('active');
        }
    });

    document.getElementById('markRead').addEventListener('click', () => {
        const unreadItems = document.querySelectorAll('.notif-list li.unread');
        unreadItems.forEach(item => item.classList.remove('unread'));
        if (badge) badge.style.display = 'none';
    });

    // 1. Get the Profile elements
    const profileBtn = document.getElementById('profileBtn');
    const profileDropdown = document.getElementById('profileDropdown');

    // 2. Add the click listener (This is the missing piece!)
    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation(); // Stop it from closing immediately
            profileDropdown.classList.toggle('active'); // This matches your CSS .active { display: block }

            // Close the notification dropdown if it happens to be open
            const notifDropdown = document.getElementById('notifDropdown');
            if (notifDropdown) notifDropdown.classList.remove('active');
        });
    }

});

function refreshNotifications() {
    fetch('check_alerts.php')
        .then(() => {
            return fetch('get_notif_count.php');
        })
        .then(response => response.text())
        .then(count => {
            const badge = document.getElementById('notifCount');
            if (badge) {
                badge.innerText = count;
                badge.style.display = (parseInt(count) > 0) ? 'flex' : 'none';
            }
            return fetch('fetch_notifications.php');
        })
        .then(response => response.text())
        .then(html => {
            const list = document.getElementById('notifList');
            if (list) {
                list.innerHTML = html;
            }
        })
        .catch(err => console.log("Alert Error:", err));
}


// Keep your DOMContentLoaded listener as is
document.addEventListener('DOMContentLoaded', () => {
    refreshNotifications();
    setInterval(refreshNotifications, 60000);
});

/**
 * Unified updateStock function. 
 * Triggered directly by the 'onclick' in your PHP/HTML table rows.
 */
// 1. Add currentStock to the parameters
function updateStock(productId, type, currentStock = 0) {
    const qtyInput = document.getElementById('qty-' + productId);
    const expiryInput = document.getElementById('expiry-' + productId);

    const qty = qtyInput ? parseInt(qtyInput.value) : 0;
    const expiry = expiryInput ? expiryInput.value : '';

    if (qty < 1) {
        Swal.fire('Invalid Quantity', 'Please enter a number greater than 0.', 'warning');
        return;
    }

    // --- NEW VALIDATION CODE STARTS HERE ---
    if (type === 'OUT' && qty > currentStock) {
        Swal.fire({
            title: 'Insufficient Stock',
            text: `You only have ${currentStock} units available, but you're trying to sell ${qty}.`,
            icon: 'error',
            confirmButtonColor: '#d33'
        });
        return; // Stops the function from showing the confirmation popup
    }
    // --- NEW VALIDATION CODE ENDS HERE ---

    // 4. Show Confirmation FIRST
    Swal.fire({
        title: (type === 'IN') ? 'Restock Item?' : 'Confirm Sale?',
        text: `Are you sure you want to process ${qty} unit(s)?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: (type === 'IN') ? '#2e7d32' : '#d33',
        confirmButtonText: 'Yes, proceed',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // ... (rest of your fetch code stays exactly the same)
            fetch('process_stock.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    'product_id': productId,
                    'quantity': qty,
                    'type': type,
                    'expiry_date': expiry
                })
            })
                .then(res => res.text())
                .then(data => {
                    if (data.trim() === "success") {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Inventory updated successfully.',
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#2e7d32'
                        }).then((successRes) => {
                            if (successRes.isConfirmed) {
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Error', 'Database error: ' + data, 'error');
                    }
                })
                .catch(err => {
                    Swal.fire('Error', 'Could not connect to the server.', 'error');
                });
        }
    });
}



// document.addEventListener('DOMContentLoaded', function () {
//     if (typeof jQuery !== 'undefined') {
//         jQuery(document).ready(function ($) {
//             $('.grid-table').DataTable({
//                 "pageLength": 10
//             });
//         });
//     } else {
//         console.error("jQuery failed to load. Check your script tags in stocks.php.");
//     }
// });

// do not touch

// This works even with 'defer' and even if the button is injected via fetch
document.addEventListener('click', function (e) {
    // Check if the clicked element (or its parent) has the ID 'markRead'
    if (e.target && e.target.id === 'markRead') {
        console.log("Mark as Read clicked!");

        fetch('mark_read.php')
            .then(response => response.text())
            .then(data => {
                if (data.trim() === "success") {
                    const badge = document.getElementById('notifCount');
                    if (badge) badge.style.display = 'none';

                    const unreadItems = document.querySelectorAll('.notif-list li.unread');
                    unreadItems.forEach(item => item.classList.remove('unread'));

                    console.log("Database updated and UI cleared.");
                }
            })
            .catch(err => console.error("Fetch error:", err));
    }
});

// document.addEventListener("DOMContentLoaded", function () {

//     const buttons = document.querySelectorAll(".add-btn, .sell-btn");

//     buttons.forEach(button => {
//         button.addEventListener("click", function () {

//             let productId = this.dataset.id;
//             let type = this.dataset.type;

//             Swal.fire({
//                 title: 'Are you sure?',
//                 text: (type === 'IN') ? 'Add stock?' : 'Sell product?',
//                 icon: 'question',
//                 showCancelButton: true,
//                 confirmButtonText: 'Yes',
//                 cancelButtonText: 'Cancel'
//             }).then((result) => {

//                 if (result.isConfirmed) {

//                     updateStock(productId, type);

//                     Swal.fire({
//                         title: 'Success!',
//                         text: 'Transaction completed.',
//                         icon: 'success',
//                         timer: 1500,
//                         showConfirmButton: false
//                     });

//                 }

//             });

//         });
//     });

// });

// ... (mga dati mong functions tulad ng updateStock, refreshNotifications, etc.)

/**
 * LOGOUT CONFIRMATION FUNCTION
 * Nilalagay ito sa pinakababa para madaling mahanap
 */
function confirmLogout(event) {
    // 1. Pigilan ang default na pag-click (hindi muna pupunta sa logout.php)
    event.preventDefault();

    const dropdown = document.getElementById("profileDropdown");
    if (dropdown) {
        dropdown.style.display = "none";
    }
    // 2. Kunin ang URL mula sa href attribute
    const url = event.currentTarget.getAttribute('href');

    // 3. I-trigger ang SweetAlert2 popup
    Swal.fire({
        title: 'Logout Request',
        text: "Are you sure you want to log out?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d33',    // Red color for logout button
        cancelButtonColor: '#3085d6',   // Blue color for cancel button
        confirmButtonText: 'Yes',
        cancelButtonText: 'No'
    }).then((result) => {
        // 4. Kung ang user ay nag-click ng "Yes", ituloy ang pagpunta sa logout.php
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}

function filterTable() {
    // 1. Get the text from the search bar and convert to lowercase
    const input = document.getElementById("searchInput");
    const filter = input.value.toLowerCase();

    // 2. Target the table and all its data rows
    const table = document.querySelector("table"); // Or use an ID if your table has one
    const rows = table.getElementsByTagName("tr");

    // 3. Loop through all table rows (starting from index 1 to skip the header)
    for (let i = 1; i < rows.length; i++) {
        let rowVisible = false;
        // Get all cells (td) in the current row
        const cells = rows[i].getElementsByTagName("td");

        // 4. Check if the search text matches the Product Name (usually cell index 1)
        // You can loop through all cells if you want to search by Category or SKU too
        for (let j = 0; j < cells.length; j++) {
            if (cells[j]) {
                const textValue = cells[j].textContent || cells[j].innerText;
                if (textValue.toLowerCase().indexOf(filter) > -1) {
                    rowVisible = true;
                    break;
                }
            }
        }

        // 5. Show or hide the row based on the match
        rows[i].style.display = rowVisible ? "" : "none";
    }
}

function changeQty(button, amount) {
    let input = button.parentElement.querySelector("input");
    let value = parseInt(input.value) + amount;

    if (value < 1) value = 1;

    input.value = value;
}

// Category Jscript here

/**
 * Modern Filter Logic for Rodal System
 * This handles the custom curvy dropdown and the table row filtering.
 */
function filterCategory() {
    // We target the hidden input which holds the actual value
    const hiddenInput = document.getElementById("realCategoryInput");
    if (!hiddenInput) return;

    const selected = hiddenInput.value;
    const rows = document.querySelectorAll("#productBody tr");

    rows.forEach(row => {
        const category = row.getAttribute("data-category");
        // Show row if "all" is selected or if the category matches exactly
        if (selected === "all" || category === selected) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filterForm');
    if (!filterForm) return;

    // We find ALL modern-dropdowns (Category AND Sort)
    const dropdowns = document.querySelectorAll('.modern-dropdown');

    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('.dropdown-trigger');
        const menuItems = dropdown.querySelectorAll('.dropdown-menu li');
        const hiddenInput = dropdown.querySelector('input[type="hidden"]');
        const displaySpan = dropdown.querySelector('.dropdown-trigger span');

        // 1. Toggle Open/Close
        trigger.addEventListener('click', function (e) {
            e.stopPropagation();

            // Close other dropdowns first so they don't overlap
            dropdowns.forEach(d => {
                if (d !== dropdown) d.classList.remove('is-open');
            });

            dropdown.classList.toggle('is-open');
        });

        // 2. Handle Item Selection
        menuItems.forEach(item => {
            item.addEventListener('click', function () {
                const val = this.getAttribute('data-value');
                const text = this.innerText;

                // Update UI & Hidden Input
                hiddenInput.value = val;
                if (displaySpan) displaySpan.innerText = text;

                // Update 'active' class on list items
                menuItems.forEach(li => li.classList.remove('active'));
                this.classList.add('active');

                // Close and Submit
                dropdown.classList.remove('is-open');
                filterForm.submit(); // This refreshes the page with Category + Sort + Search
            });
        });
    });

    // 3. Global click to close dropdowns
    window.addEventListener('click', function () {
        dropdowns.forEach(d => d.classList.remove('is-open'));
    });
});


// reverse faulty transaction

function confirmReversal(transactionId, qty, productName) {
    Swal.fire({
        title: 'Reverse Transaction?',
        text: `Creating a correction for ${productName} (${-qty} units).`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, Reverse it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // This is the "POST" part
            fetch('reverse_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                // This sends "transaction_id=52" to PHP
                body: new URLSearchParams({ 'transaction_id': transactionId })
            })
                .then(res => res.text())
                .then(data => {
                    if (data.trim() === "success") {
                        Swal.fire('Success!', 'Adjustment entry added.', 'success')
                            .then(() => location.reload()); // Refresh to see the new row
                    } else {
                        Swal.fire('Error', data, 'error');
                    }
                });
        }
    });
}


// Add Product Modal Functions
function openAddProductModal() {
    document.getElementById('addProductOverlay').classList.add('active');
}

function closeAddProductModal(e) {
    // If called from overlay click, only close if the overlay itself was clicked
    if (e && e.target !== document.getElementById('addProductOverlay')) return;
    document.getElementById('addProductOverlay').classList.remove('active');
    document.getElementById('addProductForm').reset();
}

function submitAddProduct(e) {
    e.preventDefault();

    const form = document.getElementById('addProductForm');
    const data = new URLSearchParams(new FormData(form));

    fetch('add_product.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: data
    })
        .then(res => res.text())
        .then(result => {
            if (result.trim() === 'success') {
                Swal.fire({
                    title: 'Product Added!',
                    text: 'The new product has been saved successfully.',
                    icon: 'success',
                    confirmButtonColor: '#f1c40f',
                    confirmButtonText: 'OK'
                }).then(() => {
                    document.getElementById('addProductOverlay').classList.remove('active');
                    location.reload();
                });
            } else {
                Swal.fire('Error', 'Could not save product: ' + result, 'error');
            }
        })
        .catch(() => {
            Swal.fire('Error', 'Could not connect to the server.', 'error');
        });
}


// ── EDIT MODAL ──────────────────────────────────────────
function openEditModal(id, name, sku, categoryId, cost, price) {
    document.getElementById('edit_product_id').value = id;
    document.getElementById('edit_product_name').value = name;
    document.getElementById('edit_sku').value = sku;
    document.getElementById('edit_category').value = categoryId;
    document.getElementById('edit_cost').value = cost;
    document.getElementById('edit_price').value = price;

    document.getElementById('editProductOverlay').classList.add('active');
}

function closeEditModal(event) {
    if (event && event.target !== document.getElementById('editProductOverlay')) return;
    document.getElementById('editProductOverlay').classList.remove('active');
}

function submitEditProduct(e) {
    e.preventDefault();
    const form = document.getElementById('editProductForm');
    const data = new FormData(form);

    fetch('edit_product.php', {
        method: 'POST',
        body: data
    })
        .then(res => res.text())
        .then(response => {
            if (response.trim() === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Product Updated!',
                    text: 'The product details have been saved.',
                    confirmButtonColor: '#1565c0'
                }).then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: response });
            }
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Request Failed', text: 'Could not reach the server.' }));
}

// ── ARCHIVE ─────────────────────────────────────────────
function archiveProduct(productId, productName) {
    Swal.fire({
        title: 'Archive "' + productName + '"?',
        html: 'This product will be moved to the <b>Archive</b>.<br>Its stock will be reset to <b>0</b>.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e65100',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-archive"></i> Yes, Archive It',
        cancelButtonText: 'Cancel'
    }).then(result => {
        if (!result.isConfirmed) return;

        const data = new FormData();
        data.append('product_id', productId);

        fetch('archive_product.php', {
            method: 'POST',
            body: data
        })
            .then(res => res.text())
            .then(response => {
                if (response.trim() === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Archived!',
                        text: '"' + productName + '" has been moved to the archive.',
                        confirmButtonColor: '#e65100'
                    }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response });
                }
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'Request Failed', text: 'Could not reach the server.' }));
    });
}

function toggleProfileMenu(event) {
    event.stopPropagation();
    var menu = document.getElementById("profileDropdown");
    menu.style.display = (menu.style.display === "none" || menu.style.display === "") ? "block" : "none";
}

window.addEventListener('click', function (e) {
    var menu = document.getElementById("profileDropdown");
    var btn = document.getElementById("profileBtn");
    if (menu && menu.style.display === "block") {
        if (!menu.contains(e.target) && !btn.contains(e.target)) {
            menu.style.display = "none";
        }
    }
});

window.addEventListener('keydown', function (e) {
    if (e.key === "Escape") {
        document.getElementById("profileDropdown").style.display = "none";
    }
});

function toggleMenu(event, id) {
    event.stopPropagation(); // Prevents the window click listener from firing

    const menuId = 'menu-' + id;
    const allMenus = document.querySelectorAll('.dropdown-menu');

    allMenus.forEach(menu => {
        if (menu.id === menuId) {
            menu.classList.toggle('show');
        } else {
            menu.classList.remove('show'); // Close other open menus
        }
    });
}

// Close the menu if you click anywhere else on the screen
window.onclick = function () {
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        menu.classList.remove('show');
    });
};

document.addEventListener('DOMContentLoaded', function () {
    // Select all modern dropdowns on the page
    const allDropdowns = document.querySelectorAll('.modern-dropdown');

    allDropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('.dropdown-trigger');
        const menuItems = dropdown.querySelectorAll('.dropdown-menu li');
        const hiddenInput = dropdown.querySelector('input[type="hidden"]');
        const displaySpan = trigger.querySelector('span');
        const parentForm = dropdown.closest('form');

        // Toggle Open/Close
        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            // Close other dropdowns first
            allDropdowns.forEach(other => {
                if (other !== dropdown) other.classList.remove('is-open');
            });
            dropdown.classList.toggle('is-open');
        });

        // Selection Logic
        menuItems.forEach(item => {
            item.addEventListener('click', function () {
                const val = this.getAttribute('data-value');
                const text = this.innerText;

                // Update UI
                hiddenInput.value = val;
                displaySpan.innerText = text;

                // Close and handle actions
                dropdown.classList.remove('is-open');

                // If it's a form-based dropdown (like Year), submit it
                if (parentForm) {
                    parentForm.submit();
                }

                // If you have the filterCategory function active on this page, run it
                if (typeof filterCategory === "function") {
                    filterCategory();
                }
            });
        });
    });

    // Close all if clicking anywhere else
    window.addEventListener('click', () => {
        allDropdowns.forEach(d => d.classList.remove('is-open'));
    });
});


function restoreProduct(productId, productName) {
    Swal.fire({
        title: 'Restore "' + productName + '"?',
        text: 'This product will be moved back to the active inventory.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2e7d32',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-undo"></i> Yes, Restore It',
        cancelButtonText: 'Cancel'
    }).then(result => {
        if (!result.isConfirmed) return;

        const data = new FormData();
        data.append('product_id', productId);

        fetch('restore_product.php', {
            method: 'POST',
            body: data
        })
            .then(res => res.text())
            .then(response => {
                if (response.trim() === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Restored!',
                        text: '"' + productName + '" is now active again.',
                        confirmButtonColor: '#2e7d32'
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response
                    });
                }
            })
            .catch(() => Swal.fire({
                icon: 'error',
                title: 'Request Failed',
                text: 'Could not reach the server.'
            }));
    });
}