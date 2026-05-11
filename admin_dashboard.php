<?php
session_start();

// 1. Security Check
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

require 'db_connect.php';

// 2. Capture Filters (Restored to your original names)
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$category = (isset($_GET['category']) && $_GET['category'] !== 'all') ? mysqli_real_escape_string($conn, $_GET['category']) : 'all';
$sort = $_GET['sort'] ?? 'newest';

// 3. Define Sorting logic
// 3. Define Sorting logic (Updated for Audit-Proof Math)
switch ($sort) {
    case 'oldest':
        $orderBy = "st.transaction_id ASC";
        break;
    case 'highest_val':
        // Sorts by the total transaction value (buy_amount for IN, sell_amount for OUT)
        $orderBy = "(CASE WHEN st.transaction_type = 'IN' THEN st.buy_amount ELSE st.sell_amount END) DESC";
        break;
    case 'lowest_val':
        $orderBy = "(CASE WHEN st.transaction_type = 'IN' THEN st.buy_amount ELSE st.sell_amount END) ASC";
        break;
    case 'type':
        // Groups by type, then by most recent date
        $orderBy = "st.transaction_type DESC, st.transaction_date DESC";
        break;
    case 'newest':
    default:
        $orderBy = "st.transaction_id DESC";
        break;
}
// 4. Setup Pagination
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// 5. Build WHERE Clause (Fixed the category logic)
$conditions = [];
if (!empty($search)) {
    $conditions[] = "(p.product_name LIKE '%$search%' OR c.category_name LIKE '%$search%')";
}
if ($category !== 'all' && !empty($category)) {
    $conditions[] = "c.category_name = '$category'";
}
$whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

// 6. Get Total Count (Restored $total_pages for your pagination)
$count_query = "SELECT COUNT(*) as total 
                FROM stock_transaction st
                JOIN product p ON st.product_id = p.product_id
                JOIN category c ON p.category_id = c.category_id 
                $whereClause";
$count_res = mysqli_query($conn, $count_query);
$total_row_data = mysqli_fetch_assoc($count_res);
$total_rows = $total_row_data['total'];
$total_pages = ceil($total_rows / $limit); // <--- Fixed the Undefined Variable error

// 7. Main Transaction Query
$sql = "SELECT 
            st.transaction_id, 
            st.transaction_date, 
            st.transaction_type, 
            st.quantity, 
            st.related_tid,
            st.buy_amount,  -- This is the bulk cost logged at the time
            st.sell_amount, -- This is the bulk price logged at the time
            p.product_name, 
            c.category_name
        FROM stock_transaction st
        JOIN product p ON st.product_id = p.product_id
        JOIN category c ON p.category_id = c.category_id
        $whereClause
        ORDER BY $orderBy 
        LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $sql);

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rodal Store - Purchase History</title>

    <link rel="icon" type="image/png" href="http://localhost:8000/RODALSystem/rodal-icon.png">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
</head>

<body>
    <div class="container">
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
                <li class="active"><a href="admin_dashboard.php"><i class="fas fa-history"></i><span>Transactions
                            History
                        </span></a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header>
                <div class="header-title">
                    <h1>Transactions History</h1>
                </div>

                <div class="header-right">

                    <div class="header-right">
                        <div class="search-bar">
                            <form method="GET" action="admin_dashboard.php">
                                <i class="fas fa-search"></i>
                                <input type="text" name="search" placeholder="Search..."
                                    value="<?php echo htmlspecialchars($search); ?>">
                                <?php if (!empty($search)): ?>
                                    <a href="admin_dashboard.php" class="clear-search" title="Clear Search">
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

                            <div class="user-actions">

                                <!-- DROPDOWN WRAPPER -->
                                <div class="user-profile-wrapper"
                                    style="position: relative !important; display: inline-block !important; vertical-align: middle;">

                                    <div id="profileBtn" onclick="toggleProfileMenu(event)" style="cursor: pointer; 
                padding: 2px; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
                transition: opacity 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                                        <i class="fas fa-user-circle"
                                            style="font-size: 24px !important; color: #333 !important;"></i>
                                    </div>

                                    <div id="profileDropdown" style="display: none; 
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

                                        <a href="reset_password.php" style="display: flex !important; 
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
                                            <i class="fas fa-shield-alt" style="color: #f1c40f;"></i> Reset Password
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

                                <script>
                                    function toggleProfileMenu(event) {
                                        event.stopPropagation();
                                        var menu = document.getElementById("profileDropdown");
                                        menu.style.display = (menu.style.display === "none" || menu.style.display === "") ? "block" : "none";
                                    }

                                    window.addEventListener('click', function(e) {
                                        var menu = document.getElementById("profileDropdown");
                                        var btn = document.getElementById("profileBtn");
                                        if (menu && menu.style.display === "block") {
                                            if (!menu.contains(e.target) && !btn.contains(e.target)) {
                                                menu.style.display = "none";
                                            }
                                        }
                                    });

                                    window.addEventListener('keydown', function(e) {
                                        if (e.key === "Escape") {
                                            document.getElementById("profileDropdown").style.display = "none";
                                        }
                                    });
                                </script>
                            </div>
                        </div>
            </header>

            <div class="top-bar">

                <div class="pagination-container">
                    <?php
                    // 1. Prepare parameters to preserve them in the links
                    $search_param = isset($_GET['search']) ? "&search=" . urlencode($_GET['search']) : "";
                    $cat_param = isset($_GET['category']) ? "&category=" . urlencode($_GET['category']) : "";

                    // Combine them into a single string for the links
                    $url_params = $search_param . $cat_param;
                    ?>

                    <div class="pagination">
                        <!-- Previous Button -->
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo ($page - 1) . $url_params; ?>">&laquo; Prev</a>
                        <?php endif; ?>

                        <!-- Page Number Links -->
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i . $url_params; ?>"
                                class="<?php echo ($page == $i) ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <!-- Next Button -->
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo ($page + 1) . $url_params; ?>">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                </div>




                <?php
                // Top of stocks.php
                $search = $_GET['search'] ?? '';
                $currentCat = $_GET['category'] ?? 'all';
                ?>

                <form method="GET" id="filterForm" action="admin_dashboard.php" style="display: flex; gap: 15px;">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">

                    <!-- 1. Sort Dropdown -->
                    <div class="modern-dropdown" id="sortDropdown">
                        <div class="dropdown-trigger">
                            <span>
                                <?php
                                $labels = ['newest' => 'Newest First', 'oldest' => 'Oldest First', 'highest_val' => 'Value: High-Low', 'lowest_val' => 'Value: Low-High', 'type' => 'Group by Type'];
                                echo $labels[$sort] ?? 'Newest First';
                                ?>
                            </span>
                            <i class="fas fa-sort-amount-down"></i>
                        </div>
                        <ul class="dropdown-menu">
                            <li data-value="newest" class="<?php echo ($sort == 'newest') ? 'active' : ''; ?>">Newest
                                First</li>
                            <li data-value="oldest" class="<?php echo ($sort == 'oldest') ? 'active' : ''; ?>">Oldest
                                First</li>
                            <li data-value="highest_val"
                                class="<?php echo ($sort == 'highest_val') ? 'active' : ''; ?>">Value: High to Low</li>
                            <li data-value="lowest_val" class="<?php echo ($sort == 'lowest_val') ? 'active' : ''; ?>">
                                Value: Low to High</li>
                            <li data-value="type" class="<?php echo ($sort == 'type') ? 'active' : ''; ?>">Group by Type
                            </li>
                        </ul>
                        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                    </div>

                    <!-- 2. Category Dropdown -->
                    <div class="modern-dropdown" id="categoryDropdown">
                        <div class="dropdown-trigger">
                            <span><?php echo ($category === 'all') ? 'All Categories' : str_replace('_', ' ', $category); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <ul class="dropdown-menu">
                            <li data-value="all" class="<?php echo ($category == 'all') ? 'active' : ''; ?>">All
                                Categories</li>
                            <?php
                            $cats = ["TOILETRIES", "BEVERAGE", "DRINK_POWDERED", "FOOD_CANNED", "FOOD_INSTANT", "FOOD_SNACK", "FOOD_INGREDIENT", "FOOD_RICE", "CLEANING_AGENTS"];
                            foreach ($cats as $cat) {
                                $isActive = ($category == $cat) ? 'active' : '';
                                echo "<li data-value='$cat' class='$isActive'>" . str_replace('_', ' ', $cat) . "</li>";
                            }
                            ?>
                        </ul>
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                    </div>
                </form>




            </div>

            <div class="history-container">
                <table class="grid-table" id="productTable">
                    <thead class="grid-thead">
                        <tr>
                            <th>Date and Time</th>
                            <th>Transaction ID</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Products</th>
                            <th>Price/Cost</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Actions</th> <!-- Far right is best -->
                        </tr>
                    </thead>


                    <tbody>
                        <?php

                        if ($conn->connect_error) {
                            echo "<tr><td colspan='6' style='text-align:center; color:red;'>Connection Error: " . $conn->connect_error . "</td></tr>";
                        } else {

                            /* SEARCH */
                            $search = isset($_GET['search']) ? trim($_GET['search']) : '';

                            /* CATEGORY */
                            $selectedCat = $_GET['category'] ?? 'all';

                            /* PAGINATION */
                            $limit = 10;
                            $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
                            $offset = ($page - 1) * $limit;

                            /* WHERE CONDITIONS */
                            $where = [];

                            if (!empty($search)) {
                                $searchSafe = $conn->real_escape_string($search);
                                $where[] = "p.product_name LIKE '%$searchSafe%'";
                            }

                            if ($selectedCat !== 'all' && !empty($selectedCat)) {
                                $catSafe = $conn->real_escape_string($selectedCat);
                                $where[] = "c.category_name = '$catSafe'";
                            }

                            $whereSQL = '';
                            if (!empty($where)) {
                                $whereSQL = "WHERE " . implode(" AND ", $where);
                            }

                            /* COUNT FOR PAGINATION */
                            $countSql = "SELECT COUNT(*) AS total
                 FROM stock_transaction st
                 JOIN product p ON st.product_id = p.product_id
                 JOIN category c ON p.category_id = c.category_id
                 $whereSQL";

                            $countRes = $conn->query($countSql);
                            $countRow = $countRes->fetch_assoc();
                            $total_rows = $countRow['total'];
                            $total_pages = ceil($total_rows / $limit);

                            /* MAIN QUERY WITH LIMIT */
                            // $sql = "SELECT 
                            //         st.transaction_id, 
                            //         st.transaction_date, 
                            //         st.transaction_type, 
                            //         st.quantity, 
                            //         st.related_tid, 
                            //         p.product_name, 
                            //         p.price, 
                            //         c.category_name
                            //         FROM stock_transaction st
                            //         JOIN product p ON st.product_id = p.product_id
                            //         JOIN category c ON p.category_id = c.category_id
                            //         $whereClause
                            //         ORDER BY st.transaction_id DESC
                            //         LIMIT $limit OFFSET $offset";
                            // $result = $conn->query($sql);

                            if ($result && $result->num_rows > 0) {

                                while ($row = $result->fetch_assoc()) {
                                    $transaction_id = $row['transaction_id'];
                                    $type = $row['transaction_type'];

                                    // 1. Handle Quantity: Use absolute for math, keep original for the table display
                                    $qty_display = $row['quantity'];
                                    $qty_math = abs($qty_display);

                                    // 2. SMART TOTAL DETECTION
                                    if ($type === 'IN') {
                                        $totalAmount = $row['buy_amount'];
                                    } elseif ($type === 'OUT') {
                                        $totalAmount = $row['sell_amount'];
                                    } else {
                                        // ADJUSTMENT logic: 
                                        // We check which column actually contains the mirrored value.
                                        // If buy_amount is NOT zero, it was a Restock adjustment.
                                        // If sell_amount is NOT zero, it was a Sale adjustment.
                                        if (abs($row['buy_amount']) > 0) {
                                            $totalAmount = $row['buy_amount'];
                                        } else {
                                            $totalAmount = $row['sell_amount'];
                                        }
                                    }

                                    // 3. CALCULATE UNIT PRICE
                                    // Total divided by Absolute Quantity = Original Unit Price
                                    $unitPrice = ($qty_math > 0) ? ($totalAmount / $qty_math) : 0;

                                    // 4. SUBTOTAL is simply the total value from the DB
                                    $subtotal = $totalAmount;

                                    // 5. Badge Logic
                                    if ($type === 'ADJUSTMENT') {
                                        $typeLabel = 'Adjustment';
                                        $typeColor = '#7f8c8d';
                                    } else {
                                        $isRestock = ($type === 'IN');
                                        $typeLabel = $isRestock ? 'Restock' : 'Sale';
                                        $typeColor = $isRestock ? '#3498db' : '#2e7d32';
                                    }
                        ?>


                                    <tr>
                                        <td style="color: #666; font-size: 0.9rem;">
                                            <?php echo date('M d, Y h:i A', strtotime($row['transaction_date'])); ?>
                                        </td>

                                        <td style="color: #666; font-size: 0.9rem;">
                                            <?php echo $transaction_id; ?>
                                        </td>

                                        <td>
                                            <span
                                                style="background: <?php echo $typeColor; ?>; color: white; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase;">
                                                <?php echo $typeLabel; ?>
                                            </span>
                                        </td>

                                        <td><?php echo htmlspecialchars(str_replace('_', ' ', $row['category_name'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>

                                        <!-- Updated Price Column -->
                                        <td style="font-weight:bold; color:#2e7d32;">
                                            ₱<?php echo number_format($unitPrice, 2); ?>
                                        </td>

                                        <td><?php echo $row['quantity']; ?></td>

                                        <!-- Updated Subtotal Column -->
                                        <td style="font-weight:bold; color:#333;">
                                            ₱<?php echo number_format($subtotal, 2); ?>
                                        </td>

                                        <td>
                                            <?php if ($row['transaction_type'] !== 'ADJUSTMENT' && empty($row['related_tid'])): ?>
                                                <button class="sell-btn"
                                                    onclick="confirmReversal(<?php echo $row['transaction_id']; ?>, <?php echo $row['quantity']; ?>, '<?php echo htmlspecialchars($row['product_name']); ?>')">
                                                    <i class="fas fa-undo"></i> Reverse
                                                </button>
                                            <?php else: ?>
                                                <span style="color: #999; font-style: italic; font-size: 0.8rem;">
                                                    <?php
                                                    if ($row['transaction_type'] === 'ADJUSTMENT') {
                                                        echo "Adjustment of Transaction #" . $row['related_tid'];
                                                    } else {
                                                        echo "Fixed by Transaction #" . $row['related_tid'];
                                                    }
                                                    ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                        <?php
                                }
                            } else {
                                echo "<tr><td colspan='6' style='text-align:center;'>No transactions found.</td></tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <style>

            </style>

            <script>
                document.querySelectorAll(".delete-btn").forEach(button => {
                    button.addEventListener("click", function() {
                        const transactionId = this.getAttribute('data-id'); // Kunin ang ID mula sa button
                        const row = this.closest("tr"); // Kunin ang table row para matanggal mamaya

                        Swal.fire({
                            title: 'Are you sure?',
                            text: "You want to delete this transaction record?",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33', // Pula para sa delete
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, delete it!',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Dito mo tatawagin ang delete process (AJAX o Redirect)
                                // Halimbawa ng simpleng redirect:
                                // window.location.href = "delete_transaction.php?id=" + transactionId;

                                // O kung UI removal lang muna ang gusto mo:
                                row.remove();
                                Swal.fire(
                                    'Deleted!',
                                    'Record has been removed from the view.',
                                    'success'
                                )
                            }
                        });
                    });
                });
            </script>

        </main>
    </div>
</body>

</html>