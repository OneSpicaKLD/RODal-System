<?php

session_start();

require 'db_connect.php';

if (!isset($_SESSION['isLoggedIn'])) {
    header("Location: index.php");
    exit();
}
if ($_SESSION['role'] !== 'owner') {
    header("Location: admin_dashboard.php");
    exit();
}

$sql = "SELECT COUNT(*) AS TotalProducts FROM product";
$result = mysqli_query($conn, $sql);

// Fetch the result
$row = mysqli_fetch_assoc($result);
$totalProducts = $row['TotalProducts'];

// Calculate Live Inventory: Total IN minus Total OUT
$sqlcount = "SELECT 
    SUM(CASE WHEN transaction_type = 'IN' THEN quantity ELSE 0 END) - 
    SUM(CASE WHEN transaction_type = 'OUT' THEN quantity ELSE 0 END) AS live_total 
    FROM stock_transaction";

$resultcount = mysqli_query($conn, $sqlcount);
$rowcount = mysqli_fetch_assoc($resultcount);

// This now represents what is actually in the store
$totalIncount = $rowcount['live_total'] ?? 0;


// Query for Today's Revenue (Money earned)
$revenue_query = "SELECT SUM(sell_amount) AS total_money 
                  FROM stock_transaction 
                  WHERE transaction_type = 'OUT' 
                  AND DATE(transaction_date) = CURDATE()";

$revenue_result = mysqli_query($conn, $revenue_query);
$revenue_row = mysqli_fetch_assoc($revenue_result);

// Use the correct alias 'total_money' and default to 0.00
$totalRevenue = $revenue_row['total_money'] ?? 0.00;

// Query for Today's Sales Count (Number of items)
$sales_count_query = "SELECT SUM(quantity) AS items_sold 
                      FROM stock_transaction 
                      WHERE transaction_type = 'OUT' 
                      AND DATE(transaction_date) = CURDATE()";

$sales_result = mysqli_query($conn, $sales_count_query);
$sales_row = mysqli_fetch_assoc($sales_result);
$totalSalesCount = $sales_row['items_sold'] ?? 0;

// Query to count how many unique products are currently low on stock
$low_stock_count_query = "SELECT COUNT(*) as low_count FROM (
    SELECT p.product_id
    FROM product p
    JOIN stock_transaction t ON p.product_id = t.product_id
    GROUP BY p.product_id
    HAVING SUM(CASE WHEN t.transaction_type = 'IN' THEN t.quantity ELSE -t.quantity END) <= 10
) as subquery";

$low_stock_result = mysqli_query($conn, $low_stock_count_query);
$low_stock_row = mysqli_fetch_assoc($low_stock_result);
$totalLowStock = $low_stock_row['low_count'] ?? 0;

?>

<?php
// --- 1. WEEKLY LOGIC (KEEP THIS) ---
$revenue_data = [];
$labels = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $displayDate = date('M d', strtotime($date));
    $sql = "SELECT SUM(sell_amount) as daily_revenue FROM stock_transaction 
            WHERE transaction_type = 'OUT' AND DATE(transaction_date) = '$date'";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    $labels[] = $displayDate;
    $revenue_data[] = $row['daily_revenue'] ?? 0;
}
$js_labels = json_encode($labels);
$js_revenue = json_encode($revenue_data);

// --- 2. MONTHLY LOGIC (ADD THIS NOW) ---
$monthly_revenue = [];
$month_labels = [];

for ($i = 5; $i >= 0; $i--) {
    // Gets the YYYY-MM for the last 6 months
    $monthDate = date('Y-m', strtotime("-$i months"));
    $displayMonth = date('M', strtotime($monthDate)); // e.g. "Jan"

    $sqlMonth = "SELECT SUM(sell_amount) as month_total FROM stock_transaction 
                 WHERE transaction_type = 'OUT' 
                 AND DATE_FORMAT(transaction_date, '%Y-%m') = '$monthDate'";

    $resMonth = mysqli_query($conn, $sqlMonth);
    $rowMonth = mysqli_fetch_assoc($resMonth);

    $month_labels[] = $displayMonth;
    $monthly_revenue[] = $rowMonth['month_total'] ?? 0;
}

$js_month_labels = json_encode($month_labels);
$js_month_revenue = json_encode($monthly_revenue);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rodal Store Inventory System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="script.js"></script>


</head>

<body>

    <div class="container">
        <!-- SIDEBAR -->
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
                <li class="active"><a href="dashboard.php"><i class="fas fa-th-large"></i> <span>Dashboard</span></a>
                </li>
                <li><a href="stocks.php"><i class="fas fa-boxes"></i> <span>Products</span></a></li>
                <li><a href="profit_status.php"><i class="fas fa-coins"></i> <span>Profit Status</span></a></li>
                <li><a href="purchase-history.php"><i class="fas fa-history"></i><span>Transactions
                            History
                        </span></a></li>
                <li><a href="archived_products.php"><i class="fas fa-archive"></i> <span>Archived Products</span></a></li>
            </ul>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <header>
                <div class="header-title">
                    <h1>Dashboard</h1>
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

                                <a href="change_password.php"
                                    style="display: flex !important; 
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
                  transition: background 0.2s;"
                                    onmouseover="this.style.backgroundColor='#fffdf0'"
                                    onmouseout="this.style.backgroundColor='#ffffff'">
                                    <i class="fas fa-key" style="color: #f1c40f;"></i> Change Password
                                </a>

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

                    </div>
                </div>
            </header>

            <!-- STATS SECTION -->
            <!-- REPLACE YOUR CURRENT STATS SECTION WITH THIS -->

            <section class="stats-container">

                <!-- TOTAL AVAILABLE PRODUCTS -->
                <div class="stat-card">
                    <div class="card-icon"><i class="fas fa-boxes"></i></div>
                    <div class="card-info">
                        <p>Total Products</p>
                        <strong><?php echo $totalProducts ?> Items</strong>
                    </div>
                </div>

                <!-- TOTAL QUANTITY -->
                <div class="stat-card">
                    <div class="card-icon"><i class="fas fa-layer-group"></i></div>
                    <div class="card-info">
                        <p>Total Quantity</p>
                        <div class="stat-value" style>
                            <strong>
                                <?php echo number_format($totalIncount, 0, '.', ','); ?>
                            </strong>
                        </div>
                    </div>
                </div>

                <!-- TODAY SALES -->
                <div class="stat-card">
                    <div class="card-icon"><i class="fas fa-coins"></i></div>
                    <div class="card-info">
                        <p>Today's Revenue</p>
                        <div class="stat-value">
                            <strong>
                                ₱<?php echo number_format((float) $totalRevenue, 2, '.', ','); ?>
                            </strong>
                        </div>
                    </div>
                </div>

                <!-- LOW STOCK ALERT -->
                <div class="stat-card warning-card">
                    <div class="card-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="card-info">
                        <p>Low Stock Alerts</p>
                        <strong>
                            <?php echo number_format($totalLowStock); ?> Items
                        </strong>
                    </div>
                </div>

            </section>


            <!-- REAL TIME STOCK OVERVIEW -->
            <section class="dashboard-grid">

                <!-- CHART -->
                <div class="graph-section">
                    <!-- Header with Title and Buttons -->
                    <div class="graph-header">
                        <h3>Sales Overview</h3>
                        <div class="chart-toggle">
                            <button onclick="updateChart('weekly')" id="btn-weekly"
                                class="chart-btn active">Weekly</button>
                            <button onclick="updateChart('monthly')" id="btn-monthly" class="chart-btn">Monthly</button>
                        </div>
                    </div>

                    <!-- The Actual Graph -->
                    <div class="chart-container">
                        <canvas id="revenueChart" data-weekly-labels='<?php echo $js_labels; ?>'
                            data-weekly-values='<?php echo $js_revenue; ?>'
                            data-monthly-labels='<?php echo $js_month_labels; ?>'
                            data-monthly-values='<?php echo $js_month_revenue; ?>'>
                        </canvas>
                    </div>
                </div>


                <!-- STOCK OVERVIEW -->
                <div class="stock-panel">
                    <div class="stock-header">
                        <h3>Real-Time Stock Overview</h3>
                        <span class="stock-badge">Live</span>
                    </div>

                    <div class="stock-list">

                        <?php
                        $stock_overview_query = "SELECT p.product_name, 
                IFNULL(SUM(CASE WHEN t.transaction_type = 'IN' THEN t.quantity ELSE -t.quantity END), 0) as current_qty
                FROM product p
                LEFT JOIN stock_transaction t ON p.product_id = t.product_id
                GROUP BY p.product_id
                ORDER BY current_qty ASC
                LIMIT 6";

                        $stock_overview_result = mysqli_query($conn, $stock_overview_query);

                        while ($item = mysqli_fetch_assoc($stock_overview_result)) {
                            $qty = $item['current_qty'];

                            if ($qty <= 10) {
                                $status = "Critical";
                                $badge = "critical";
                            } elseif ($qty <= 25) {
                                $status = "Low";
                                $badge = "low";
                            } else {
                                $status = "Healthy";
                                $badge = "good";
                            }
                        ?>

                            <div class="stock-item-card">
                                <div class="stock-info">
                                    <span class="stock-name"><?php echo $item['product_name']; ?></span>
                                    <small><?php echo $qty; ?> pcs available</small>
                                </div>

                                <span class="stock-status <?php echo $badge; ?>">
                                    <?php echo $status; ?>
                                </span>
                            </div>

                        <?php } ?>

                    </div>

                    <div class="stock-footer">
                        <a href="stocks.php">View Full Inventory →</a>
                    </div>
                </div>

            </section>

        </main>
    </div>
</body>

</html>