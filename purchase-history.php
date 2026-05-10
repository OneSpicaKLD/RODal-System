<?php
session_start();

if (!isset($_SESSION['isLoggedIn'])) {
    header("Location: index.php");
    exit();
}

require 'db_connect.php';

// 1. Capture All Filters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category = (isset($_GET['category']) && $_GET['category'] !== 'all') ? mysqli_real_escape_string($conn, $_GET['category']) : '';

// 2. Setup Pagination Variables
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// 3. Build Dynamic WHERE Clause
$conditions = [];
$conditions[] = "p.product_name LIKE '%$search%'";

if (!empty($category)) {
    $conditions[] = "c.category_name = '$category'";
}

$whereClause = "WHERE " . implode(" AND ", $conditions);

// 4. Get Total Count (For Pagination)
$total_query = "SELECT COUNT(*) as total 
                FROM product p 
                JOIN category c ON p.category_id = c.category_id 
                $whereClause";
$total_results_res = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_results_res);
$total_pages = ceil($total_row['total'] / $limit);

// 5. Main Data Query
$sql = "SELECT p.product_id, p.product_name, c.category_name, p.price,
        SUM(CASE WHEN t.transaction_type = 'IN' THEN t.quantity ELSE 0 END) as total_in,
        SUM(CASE WHEN t.transaction_type = 'OUT' THEN t.quantity ELSE 0 END) as total_out
        FROM product p
        JOIN category c ON p.category_id = c.category_id
        LEFT JOIN stock_transaction t ON p.product_id = t.product_id
        $whereClause
        GROUP BY p.product_id
        ORDER BY p.product_id ASC
        LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rodal Store - Purchase History</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="script.js"></script>

    <style>
        .history-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
    </style>

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
                <li><a href="dashboard.php"><i class="fas fa-th-large"></i> <span>Dashboard</span></a></li>
                <li><a href="stocks.php"><i class="fas fa-boxes"></i> <span>Products</span></a></li>
                <li><a href="profit_status.php"><i class="fas fa-coins"></i> <span>Profit Status</span></a></li>
                <li class="active"><a href="purchase-history.php"><i class="fas fa-history"></i><span>Transactions
                            History
                        </span></a></li>
                <li><a href="archived_products.php"><i class="fas fa-archive"></i> <span>Archived Products</span></a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header>
                <div class="header-title">
                    <h1>Purchase History</h1>
                </div>

                <div class="header-right">

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
                        <div class="user-profile-wrapper" style="position: relative !important; display: inline-block !important; vertical-align: middle;">

                            <div id="profileBtn" onclick="toggleProfileMenu(event)"
                                style="cursor: pointer; 
                padding: 2px; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
                transition: opacity 0.2s;"
                                onmouseover="this.style.opacity='0.8'"
                                onmouseout="this.style.opacity='1'">
                                <i class="fas fa-user-circle" style="font-size: 24px !important; color: #333 !important;"></i>
                            </div>

                            <div id="profileDropdown"
                                style="display: none; 
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

                                <div style="padding: 12px 18px; border-bottom: 1px solid #f0f0f0; background: #fff; text-align: left !important;">
                                    <strong style="display: block !important; color: #333 !important; font-size: 14px !important; line-height: 1.2 !important; margin: 0 !important;">
                                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                                    </strong>
                                    <span style="color: #888 !important; font-size: 12px !important; font-weight: normal !important;">
                                        <?php echo htmlspecialchars(ucfirst($_SESSION['role'])); ?>
                                    </span>
                                </div>

                                <a href="logout.php"
                                    onclick="confirmLogout(event)"
                                    style="display: flex !important; 
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
                  transition: background 0.2s;"
                                    onmouseover="this.style.backgroundColor='#fff5f5'"
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

                <form method="GET" id="filterForm" action="purchase-history.php">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">

                    <div class="modern-dropdown" id="categoryDropdown">
                        <div class="dropdown-trigger">
                            <span id="selectedDisplay">
                                <?php
                                echo ($currentCat === 'all') ? 'All Categories' : str_replace('_', ' ', $currentCat);
                                ?>
                            </span>
                            <i class="fas fa-chevron-down"></i>
                        </div>

                        <ul class="dropdown-menu">
                            <li data-value="all" class="<?php echo ($currentCat == 'all') ? 'active' : ''; ?>">All Categories</li>
                            <?php
                            $cats = ["TOILETRIES", "BEVERAGE", "DRINK_POWDERED", "FOOD_CANNED", "FOOD_INSTANT", "FOOD_SNACK", "FOOD_INGREDIENT", "FOOD_RICE", "CLEANING_AGENTS"];
                            foreach ($cats as $cat) {
                                $isActive = ($currentCat == $cat) ? 'active' : '';
                                $readableName = str_replace('_', ' ', $cat);
                                echo "<li data-value='$cat' class='$isActive'>$readableName</li>";
                            }
                            ?>
                        </ul>

                        <input type="hidden" name="category" id="realCategoryInput" value="<?php echo htmlspecialchars($currentCat); ?>">
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
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php

                        /* keep existing connection if already connected */
                        $conn = new mysqli("localhost", "root", "", "rodal", "3306");

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
                            $sql = "SELECT 
                            c.category_name,
                            p.product_name,
                            p.price,
                            st.quantity,
                            st.related_tid,
                            st.transaction_id,
                            st.transaction_type,  
                            st.transaction_date 
                            FROM stock_transaction st
                            JOIN product p ON st.product_id = p.product_id
                            JOIN category c ON p.category_id = c.category_id
                            $whereSQL
                            ORDER BY st.transaction_id DESC
                            LIMIT $limit OFFSET $offset";
                            $result = $conn->query($sql);

                            if ($result && $result->num_rows > 0) {

                                while ($row = $result->fetch_assoc()) {

                                    $transaction_id = $row['transaction_id'];

                                    $subtotal = $row['price'] * $row['quantity'];
                        ?>
                                    <?php
                                    // 1. Logic for Type Badge (PLACE THIS RIGHT BEFORE YOUR <tr>)
                                    $type = $row['transaction_type'];

                                    if ($type === 'ADJUSTMENT') {
                                        $typeLabel = 'Adjustment';
                                        $typeColor = '#7f8c8d'; // Gray for corrections
                                    } else {
                                        $isRestock = ($type === 'IN');
                                        $typeLabel = $isRestock ? 'Restock' : 'Sale';
                                        $typeColor = $isRestock ? '#3498db' : '#2e7d32'; // Blue for IN, Green for OUT
                                    }

                                    $subtotal = $row['price'] * $row['quantity'];
                                    ?>
                                    <tr>
                                        <!-- 1. Date & Time -->
                                        <td style="color: #666; font-size: 0.9rem;">
                                            <?php echo date('M d, Y h:i A', strtotime($row['transaction_date'])); ?>
                                        </td>

                                        <td style="color: #666; font-size: 0.9rem;">
                                            <?php echo $transaction_id; ?>
                                        </td>

                                        <!-- 2. Transaction Type -->
                                        <td>
                                            <!-- The Main Badge -->
                                            <span
                                                style="background: <?php echo $typeColor; ?>; color: white; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase;">
                                                <?php echo $typeLabel; ?>
                                            </span>

                                            <!-- The "Paper Trail" (Only shows IF there is a link) -->
                                            <?php if (!empty($row['related_tid'])): ?>
                                                <div
                                                    style="font-size: 0.65rem; color: #7f8c8d; margin-top: 4px; font-style: italic; line-height: 1;">
                                                    <?php
                                                    echo ($row['transaction_type'] === 'ADJUSTMENT')
                                                        ? "Adjustment of Transaction #" . $row['related_tid']
                                                        : "Fixed by Transaction #" . $row['related_tid'];
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- 3. Category -->
                                        <td><?php echo htmlspecialchars(str_replace('_', ' ', $row['category_name'])); ?></td>

                                        <!-- 4. Products -->
                                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>

                                        <!-- 5. Price -->
                                        <td style="font-weight:bold; color:#2e7d32;">
                                            ₱<?php echo number_format($row['price'], 2); ?>
                                        </td>

                                        <!-- 6. Quantity -->
                                        <td><?php echo $row['quantity']; ?></td>

                                        <!-- 7. Subtotal -->
                                        <td style="font-weight:bold; color:#333;">
                                            ₱<?php echo number_format($subtotal, 2); ?>
                                        </td>
                                    </tr>

                        <?php
                                }
                            } else {
                                echo "<tr><td colspan='8' style='text-align:center;'>No transactions found.</td></tr>";
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