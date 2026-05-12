<?php
session_start();

if (!isset($_SESSION['isLoggedIn'])) {
    header("Location: index.php");
    exit();
}

require 'db_connect.php';

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$conditions = [];
$conditions[] = "p.is_active = 0";
$conditions[] = "p.product_name LIKE '%$search%'";
$whereClause = "WHERE " . implode(" AND ", $conditions);

$total_query = "SELECT COUNT(*) as total FROM product p JOIN category c ON p.category_id = c.category_id $whereClause";
$total_res = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_res);
$total_pages = ceil($total_row['total'] / $limit);

$sql = "SELECT p.product_id, p.product_name, p.product_sku, c.category_name, p.price
        FROM product p
        JOIN category c ON p.category_id = c.category_id
        $whereClause
        ORDER BY p.product_id ASC
        LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rodal Store - Archived Products</title>

    <link rel="icon" type="image/png" href="rodal-icon.png">

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="script.js"></script>
    <style>
        .restore-btn {
            background: #2e7d32;
            color: #fff;
            border: none;
            padding: 5px 11px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: background 0.2s;
        }

        .restore-btn:hover {
            background: #1b5e20;
        }

        .archive-badge {
            display: inline-block;
            background: #fff3e0;
            color: #e65100;
            border: 1px solid #e65100;
            border-radius: 20px;
            padding: 2px 10px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 8px;
            vertical-align: middle;
        }
    </style>
</head>

<body>

    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <a href="dashboard.php" class="logo-r">
                    <div class="logo-r">R</div>
                </a>
                <div class="logo-text">
                    <h2>Rodal Store</h2>
                    <p>Inventory System</p>
                </div>
            </div>
            <ul class="nav-links">
                <li><a href="dashboard.php"><i class="fas fa-th-large"></i> <span>Dashboard</span></a></li>
                <li><a href="stocks.php"><i class="fas fa-boxes"></i> <span>Products</span></a></li>
                <li><a href="profit_status.php"><i class="fas fa-coins"></i> <span>Profit Status</span></a></li>
                <li><a href="purchase-history.php"><i class="fas fa-history"></i><span>Transactions History</span></a>
                </li>
                <li class="active"><a href="archived_products.php"><i class="fas fa-archive"></i> <span>Archived
                            Products</span></a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header>
                <div class="header-title">
                    <h1>Archived Products</h1>
                </div>

                <div class="header-right">
                    <div class="search-bar">
                        <form method="GET" action="archived_products.php">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Search archived..."
                                value="<?php echo htmlspecialchars($search); ?>">
                            <?php if (!empty($search)): ?>
                                <a href="archived_products.php" class="clear-search" title="Clear Search">
                                    <i class="fas fa-times"></i>
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="user-actions">
                        <!-- NOTIFICATION WRAPPER -->
                        <div class="notification-container">
                            <button class="icon-btn" id="notifBtn">
                                <i class="fas fa-bell"></i>
                                <span class="notif-badge" id="notifCount">0</span>
                            </button>

                            <div class="notif-dropdown" id="notifDropdown">
                                <div class="notif-header">
                                    <h3>Notifications</h3>
                                    <span id="markRead" style="cursor:pointer;">Mark all as read</span>
                                </div>
                                <!-- This is where the separate file will inject the <li> items -->
                                <ul class="notif-list" id="notifList"></ul>
                                <div class="notif-footer">
                                    <a href="all_notifications.php">View all alerts</a>
                                </div>
                            </div>
                        </div>
                        <!-- Notification Ends here... -->

                        <!-- DROPDOWN WRAPPER -->
                        <div class="user-profile-wrapper"
                            style="position: relative !important; display: inline-block !important; vertical-align: middle;">

                            <button class="icon-btn" id="profileBtn" style="cursor: pointer; 
                                padding: 2px; 
                                display: flex; 
                                align-items: center; 
                                justify-content: center; 
                                transition: opacity 0.2s;" onmouseover="this.style.opacity='0.8'"
                                onmouseout="this.style.opacity='1'">
                                <i class="fas fa-user-circle"
                                    style="font-size: 24px !important; color: #333 !important;"></i>
                            </button>

                            <div class="notif-dropdown" id="profileDropdown" style="display: none; 
                                position: absolute !important; 
                                top: 40px !important; 
                                right: 0 !important; 
                                left: auto !important; 
                                width: 200px !important; 
                                background: #ffffff !important; 
                                border-radius: 12px !important; 
                                box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important; 
                                border: 1px solid #edf2f7 !important; 
                                padding: 0 !important; 
                                z-index: 99999 !important;
                                overflow: hidden !important;">
                                <div
                                    style="padding: 12px 18px; border-bottom: 1px solid #f0f0f0; background: #fff; text-align: left !important;">
                                    <strong
                                        style="display: block !important; color: #333 !important; font-size: 14px !important; line-height: 1.2 !important; margin: 0 !important;">
                                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                                    </strong>
                                    <span
                                        style="color: #888 !important; font-size: 12px !important; font-weight: normal !important;">
                                        <?php echo htmlspecialchars(ucfirst($_SESSION['role'])); ?>
                                    </span>
                                </div>

                                <a href="change_password.php" style="display: flex !important; 
                  align-items: center !important; 
                  gap: 10px !important; 
                  padding: 12px 18px !important; 
                  text-decoration: none !important; 
                  color: #333 !important; 
                  font-weight: 600 !important;
                  background: #fff !important;
                  font-size: 14px !important;
                  justify-content: flex-start !important;
                  width: 100% !important;
                  white-space: nowrap !important;
                  border-bottom: 1px solid #f0f0f0 !important;
                  transition: background 0.2s;" onmouseover="this.style.backgroundColor='#fffdf0'"
                                    onmouseout="this.style.backgroundColor='#ffffff'">
                                    <i class="fas fa-key" style="color: #f1c40f;"></i> Change Password
                                </a>

                                <a href="logout.php" onclick="confirmLogout(event)" style="display: flex !important; 
                  align-items: center !important; 
                  gap: 10px !important; 
                  padding: 12px 18px !important; 
                  text-decoration: none !important; 
                  color: #e74c3c !important; 
                  font-weight: bold !important;
                  background: #fff !important;
                  justify-content: flex-start !important;
                  width: 100% !important;
                  white-space: nowrap !important;
                  transition: background 0.2s;" onmouseover="this.style.backgroundColor='#fff5f5'"
                                    onmouseout="this.style.backgroundColor='#ffffff'">
                                    <i class="fas fa-sign-out-alt"></i> Log Out
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </header>

            <section class="category-section">

                <!-- Pagination -->
                <div class="top-bar">
                    <div style="color: #777; font-size: 13px; padding: 8px 0;">
                        <i class="fas fa-info-circle"></i>
                        Archived products have <strong>zero stock</strong> and are hidden from the main inventory.
                        Restore them to make them active again.
                    </div>

                    <div class="pagination-container">
                        <?php $url_params = !empty($search) ? '&search=' . urlencode($search) : ''; ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a class="nav-btn" href="?page=<?php echo ($page - 1) . $url_params; ?>">&laquo; Prev</a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a class="page-num <?php echo ($page == $i) ? 'active' : ''; ?>"
                                    href="?page=<?php echo $i . $url_params; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a class="nav-btn" href="?page=<?php echo ($page + 1) . $url_params; ?>">Next &raquo;</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <table class="grid-table">
                    <thead class="grid-thead">
                        <tr>
                            <th>Product Name</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th style="text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) === 0): ?>
                            <tr>
                                <td colspan="5" style="text-align:center; padding: 40px; color: #999;">
                                    <i class="fas fa-box-open"
                                        style="font-size: 32px; margin-bottom: 8px; display:block;"></i>
                                    No archived products found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['product_sku']); ?></td>
                                    <td><?php echo ucwords(strtolower(str_replace('_', ' ', $row['category_name']))); ?></td>
                                    <td style="font-weight: bold; color: #2e7d32;">
                                        ₱<?php echo number_format($row['price'], 2); ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <button class="restore-btn"
                                            onclick="restoreProduct(<?php echo $row['product_id']; ?>, '<?php echo addslashes(htmlspecialchars($row['product_name'])); ?>')">
                                            <i class="fas fa-undo"></i> Restore
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

            </section>
        </main>
    </div>

    <style>
        /* Ensure the numbers behave consistently */
        .pagination a.page-num {
            color: #555;
        }

        /* Next/Prev Arrow Specifics - now using a class instead of position */
        .pagination a.nav-btn {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            padding: 8px 20px;
            color: #f39c12;
            /* Keep arrows colored even when not hovered */
        }
    </style>

    <script>
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
    </script>

</body>

</html>