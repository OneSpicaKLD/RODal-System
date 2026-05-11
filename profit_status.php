<?php
session_start();

if (!isset($_SESSION['isLoggedIn'])) {
    header("Location: index.php");
    exit();
}

require 'db_connect.php';

// 1. Capture All Date Filters
$currentY = (int) date('Y');
$currentM = (int) date('n');
$currentD = (int) date('j');

$selectedY = isset($_GET['year']) ? intval($_GET['year']) : $currentY;
$selectedM = isset($_GET['month']) ? intval($_GET['month']) : $currentM;
$selectedD = isset($_GET['day']) ? intval($_GET['day']) : $currentD;

// 2. SELECTED DAY (Matches your Day Dropdown)
$day_sql = "SELECT SUM(t.sell_amount) as rev, SUM(t.sell_amount - (p.cost * t.quantity)) as prof 
            FROM stock_transaction t JOIN product p ON t.product_id = p.product_id
            WHERE t.transaction_type = 'OUT' 
            AND DAY(t.transaction_date) = $selectedD
            AND MONTH(t.transaction_date) = $selectedM 
            AND YEAR(t.transaction_date) = $selectedY";
$day_data = mysqli_fetch_assoc(mysqli_query($conn, $day_sql));

// 3. THIS WEEK (Actual last 7 days from today)
$week_sql = "SELECT SUM(t.sell_amount) as rev, SUM(t.sell_amount - (p.cost * t.quantity)) as prof 
             FROM stock_transaction t JOIN product p ON t.product_id = p.product_id
             WHERE t.transaction_type = 'OUT' 
             AND t.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$week = mysqli_fetch_assoc(mysqli_query($conn, $week_sql));

// 4. SELECTED MONTH (Matches your Month Dropdown)
$month_sql = "SELECT SUM(t.sell_amount) as rev, SUM(t.sell_amount - (p.cost * t.quantity)) as prof 
              FROM stock_transaction t JOIN product p ON t.product_id = p.product_id
              WHERE t.transaction_type = 'OUT' 
              AND MONTH(t.transaction_date) = $selectedM 
              AND YEAR(t.transaction_date) = $selectedY";
$month = mysqli_fetch_assoc(mysqli_query($conn, $month_sql));

// 5. SELECTED YEAR (Matches your Year Dropdown)
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

                                <!-- Day Selector -->
                                <div class="modern-dropdown" id="dayDropdown">
                                    <div class="dropdown-trigger">
                                        <span>
                                            <?php
                                            $selectedD = isset($_GET['day']) ? (int) $_GET['day'] : (int) date('j');
                                            echo $selectedD;
                                            ?>
                                        </span>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                    <ul class="dropdown-menu">
                                        <?php
                                        // Get number of days in the selected month/year
                                        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedM, $selectedY);
                                        for ($d = 1; $d <= $daysInMonth; $d++):
                                            $activeClass = ($d == $selectedD) ? 'active' : '';
                                            echo "<li data-value='$d' class='$activeClass'>$d</li>";
                                        endfor;
                                        ?>
                                    </ul>
                                    <input type="hidden" name="day" value="<?php echo $selectedD; ?>">
                                </div>


                            </form>
                        </div>

                    </div>

                    <!-- REVENUE CARDS -->
                    <div class="card-row">
                        <div class="yellow-box">
                            <span>SELECTED DAY</span>
                            <strong>₱<?php echo number_format($day_data['rev'] ?? 0, 2); ?></strong>
                        </div>

                        <div class="yellow-box">
                            <span>THIS WEEK</span>
                            <strong>₱<?php echo number_format($week['rev'] ?? 0, 2); ?></strong>
                        </div>

                        <div class="yellow-box">
                            <span>SELECTED MONTH</span>
                            <!-- Use $month instead of $month_data -->
                            <strong>₱<?php echo number_format($month['rev'] ?? 0, 2); ?></strong>
                        </div>
                    </div>




                    <!-- SECOND TITLE -->
                    <div class="section-head">
                        <h2>TOTAL PROFIT</h2>
                    </div>

                    <!-- PROFIT CARDS -->
                    <div class="card-row">
                        <div class="green-box">
                            <span>SELECTED DAY</span>
                            <strong>₱<?php echo number_format($day_data['prof'] ?? 0, 2); ?></strong>
                        </div>

                        <div class="green-box">
                            <span>THIS WEEK</span>
                            <strong>₱<?php echo number_format($week['prof'] ?? 0, 2); ?></strong>
                        </div>

                        <div class="green-box">
                            <span>SELECTED MONTH</span>
                            <!-- Use $month instead of $month_data -->
                            <strong>₱<?php echo number_format($month['prof'] ?? 0, 2); ?></strong>
                        </div>
                    </div>



                </section>

            </div>
        </main>
    </div>

   

</body>

</html>