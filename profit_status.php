<?php
// ... session and db_connect lines ...
session_start();

if (!isset($_SESSION['isLoggedIn'])) {
    header("Location: index.php");
    exit();
}

require 'db_connect.php';

// Get all unique years from the database to populate the dropdown
$year_list_sql = "SELECT DISTINCT YEAR(transaction_date) as year_val 
                  FROM stock_transaction 
                  ORDER BY year_val DESC";
$year_list_res = mysqli_query($conn, $year_list_sql);



// 1. Capture Year and Month from the URL (Syncs with your HTML form)
$currentY = (int) date('Y');
$currentM = (int) date('n');

$selectedY = isset($_GET['year']) ? intval($_GET['year']) : $currentY;
$selectedM = isset($_GET['month']) ? intval($_GET['month']) : $currentM;

$today_sql = "SELECT SUM(t.sell_amount) as rev, SUM(t.sell_amount - (p.cost * t.quantity)) as prof 
              FROM stock_transaction t JOIN product p ON t.product_id = p.product_id
              WHERE t.transaction_type = 'OUT' AND DATE(t.transaction_date) = CURDATE()";
$today = mysqli_fetch_assoc(mysqli_query($conn, $today_sql));

$week_sql = "SELECT SUM(t.sell_amount) as rev, SUM(t.sell_amount - (p.cost * t.quantity)) as prof 
             FROM stock_transaction t JOIN product p ON t.product_id = p.product_id
             WHERE t.transaction_type = 'OUT' 
             AND YEAR(t.transaction_date) = $selectedY
             AND MONTH(t.transaction_date) = $selectedM
             AND t.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";

$week_res = mysqli_query($conn, $week_sql);
$week = mysqli_fetch_assoc($week_res);

$month_sql = "SELECT 
                SUM(t.sell_amount) as rev, 
                SUM(t.sell_amount - (p.cost * t.quantity)) as prof 
              FROM stock_transaction t 
              JOIN product p ON t.product_id = p.product_id
              WHERE t.transaction_type = 'OUT' 
              AND MONTH(t.transaction_date) = $selectedM 
              AND YEAR(t.transaction_date) = $selectedY";

$month_res = mysqli_query($conn, $month_sql);
$month = mysqli_fetch_assoc($month_res);

$year_sql = "SELECT SUM(t.sell_amount) as rev, SUM(t.sell_amount - (p.cost * t.quantity)) as prof 
             FROM stock_transaction t JOIN product p ON t.product_id = p.product_id
             WHERE t.transaction_type = 'OUT' 
             AND YEAR(t.transaction_date) = $selectedY";

$year_data = mysqli_fetch_assoc(mysqli_query($conn, $year_sql));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rodal Store - Profit Status</title>

    <link rel="icon" type="image/png" href="rodal-icon.png">

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="script.js"></script>


    <!-- <script>
        document.addEventListener('DOMContentLoaded', function() {

            const allDropdowns = document.querySelectorAll('.modern-dropdown');

            allDropdowns.forEach(dropdown => {
                const trigger = dropdown.querySelector('.dropdown-trigger');
                const menuItems = dropdown.querySelectorAll('.dropdown-menu li');
                const hiddenInput = dropdown.querySelector('input[type="hidden"]');
                const displaySpan = trigger.querySelector('span');
                const parentForm = dropdown.closest('form');

                trigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    allDropdowns.forEach(other => {
                        if (other !== dropdown) other.classList.remove('is-open');
                    });
                    dropdown.classList.toggle('is-open');
                });

                menuItems.forEach(item => {
                    item.addEventListener('click', function() {
                        const val = this.getAttribute('data-value');
                        const text = this.innerText;

                        hiddenInput.value = val;
                        displaySpan.innerText = text;

                        dropdown.classList.remove('is-open');

                        if (parentForm) {
                            parentForm.submit();
                        }

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

        document.addEventListener('DOMContentLoaded', function() {
            const allDropdowns = document.querySelectorAll('.modern-dropdown');

            allDropdowns.forEach(dropdown => {
                const trigger = dropdown.querySelector('.dropdown-trigger');
                const menuItems = dropdown.querySelectorAll('.dropdown-menu li');
                const displaySpan = trigger.querySelector('span');

                // Find the hidden input WITHIN this specific dropdown div
                const hiddenInput = dropdown.querySelector('input[type="hidden"]');

                // Find the shared form
                const parentForm = dropdown.closest('form');

                // Toggle dropdown open/close
                trigger.addEventListener('click', function(e) {
                    e.stopPropagation();

                    // Close other dropdowns first
                    allDropdowns.forEach(other => {
                        if (other !== dropdown) other.classList.remove('is-open');
                    });

                    dropdown.classList.toggle('is-open');
                });

                // Handle item selection
                menuItems.forEach(item => {
                    item.addEventListener('click', function() {
                        const val = this.getAttribute('data-value');
                        const text = this.innerText;

                        // 1. Update the hidden input value
                        if (hiddenInput) {
                            hiddenInput.value = val;
                        }

                        // 2. Update the visible text
                        displaySpan.innerText = text;

                        // 3. Close the menu
                        dropdown.classList.remove('is-open');

                        // 4. Submit the shared form (this sends year, month, and day)
                        if (parentForm) {
                            parentForm.submit();
                        }
                    });
                });
            });

            // Close all dropdowns if clicking anywhere else on the screen
            window.addEventListener('click', () => {
                allDropdowns.forEach(d => d.classList.remove('is-open'));
            });
        });
    </script> -->

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
                <li class="active"><a href="profit_status.php"><i class="fas fa-coins"></i> <span>Profit
                            Status</span></a></li>
                <li><a href="purchase-history.php"><i class="fas fa-history"></i><span>Transactions
                            History</span></a></li>
                <li><a href="archived_products.php"><i class="fas fa-archive"></i> <span>Archived Products</span></a>
                </li>
            </ul>
        </aside>

        <main class="main-content">
            <header>
                <div class="header-title">
                    <h1>Profit Status</h1>
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

            <div class="profit-layout">

                <!-- SINGLE SECTION -->
                <section class="stat-group">

                    <!-- TOP HEADER -->
                    <div class="section-head">
                        <h2>TOTAL REVENUE</h2>
                        <div class="filter-container">
                            <form method="GET" id="filterForm" action="profit_status.php" class="filter-group">

                                <!-- Year Selector -->
                                <div class="modern-dropdown" id="yearDropdown">
                                    <div class="dropdown-trigger">
                                        <span>
                                            <?php
                                            $currentY = (int) date('Y');
                                            $selectedY = isset($_GET['year']) ? intval($_GET['year']) : $currentY;
                                            echo $selectedY;
                                            ?>
                                        </span>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                    <ul class="dropdown-menu">
                                        <?php
                                        // Generates current year and the 2 previous years
                                        for ($i = $currentY; $i >= ($currentY - 2); $i--) {
                                            $activeClass = ($i == $selectedY) ? 'active' : '';
                                            echo "<li data-value='$i' class='$activeClass'>$i</li>";
                                        }
                                        ?>
                                    </ul>
                                    <input type="hidden" name="year" value="<?php echo $selectedY; ?>">
                                </div>

                                <!-- Month Selector -->
                                <div class="modern-dropdown" id="monthDropdown">
                                    <div class="dropdown-trigger">
                                        <span>
                                            <?php
                                            $months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                                            $selectedM = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
                                            echo $months[$selectedM - 1];
                                            ?>
                                        </span>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                    <ul class="dropdown-menu">
                                        <?php foreach ($months as $index => $name): ?>
                                            <?php $mVal = $index + 1; ?>
                                            <li data-value="<?php echo $mVal; ?>"
                                                class="<?php echo ($mVal == $selectedM) ? 'active' : ''; ?>">
                                                <?php echo $name; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <input type="hidden" name="month" value="<?php echo $selectedM; ?>">
                                </div>

                            </form>
                        </div>

                    </div>

                    <!-- REVENUE CARDS -->
                    <div class="card-row">
                        <div class="yellow-box">
                            <span>TODAY</span>
                            <strong>₱
                                <?php echo number_format($today['rev'] ?? 0, 2); ?>
                            </strong>
                        </div>

                        <div class="yellow-box">
                            <span>THIS WEEK</span>
                            <strong>₱
                                <?php echo number_format($week['rev'] ?? 0, 2); ?>
                            </strong>
                        </div>

                        <div class="yellow-box">
                            <span>THIS MONTH</span>
                            <strong>₱
                                <?php echo number_format($month['rev'] ?? 0, 2); ?>
                            </strong>
                        </div>
                    </div>


                    <!-- SECOND TITLE -->
                    <div class="section-head">
                        <h2>TOTAL PROFIT</h2>
                    </div>

                    <!-- PROFIT CARDS -->
                    <div class="card-row">
                        <div class="green-box">
                            <span>TODAY</span>
                            <strong>₱
                                <?php echo number_format($today['prof'] ?? 0, 2); ?>
                            </strong>
                        </div>

                        <div class="green-box">
                            <span>THIS WEEK</span>
                            <strong>₱
                                <?php echo number_format($week['prof'] ?? 0, 2); ?>
                            </strong>
                        </div>

                        <div class="green-box">
                            <span>THIS MONTH</span>
                            <strong>₱
                                <?php echo number_format($month['prof'] ?? 0, 2); ?>
                            </strong>
                        </div>
                    </div>

                </section>

            </div>
        </main>
    </div>

    <style>
        /* REUSABLE DROPDOWN STYLES */
        .modern-dropdown {
            position: relative;
            width: 230px;
            /* Default width */
            font-family: 'Poppins', sans-serif;
            user-select: none;
        }

        /* Specific sizing for the Year dropdown so it doesn't look too bulky */
        #yearDropdown {
            width: 150px;
        }

        /* 1. Ensure the container has a stacking context */
        .year-selector {
            position: relative;
            z-index: 10;
            /* Higher than the yellow card */
        }

        /* 2. Force the dropdown menu to the very front */
        .modern-dropdown .dropdown-menu {
            position: absolute;
            z-index: 9999 !important;
            /* Force to top */

            /* Optional: If the card is still peaking through, 
       ensure the menu has a solid white background */
            background-color: #ffffff !important;
        }

        /* 3. Check the Yellow Card */
        /* If your yellow card has a z-index, make sure it is lower than 10 */
        .profit-card,
        .card-class-name {
            position: relative;
            z-index: 1;
        }

        .dropdown-trigger {
            background: #fff;
            border: 2px solid #edf2f7;
            padding: 10px 18px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            color: #2d3748;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            /* Sits flush as requested */
            left: 0;
            width: 100%;
            background: #fff;
            border-radius: 15px;
            /* Curvy top and bottom */
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            list-style: none;
            padding: 8px 0;
            margin: 0;
            display: none;
            z-index: 1000;
            border: 1px solid #edf2f7;
            overflow: hidden;
        }

        .dropdown-menu li {
            padding: 12px 18px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            color: #4a5568;
            letter-spacing: 0.2px;
            transition: all 0.2s ease;
        }

        /* Hover State: Light Yellow as requested */
        .dropdown-menu li:hover {
            background: #fff9c4;
            color: #856404;
            padding-left: 22px;
        }

        .dropdown-menu li.active {
            background: #fef9c3;
            color: #856404;
            font-weight: 700;
        }

        .modern-dropdown.is-open .dropdown-menu {
            display: block;
        }
    </style>

</body>

</html>