<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['isLoggedIn'])) {
    header("Location: index.php");
    exit();
}

// 1. Capture the category from the dropdown
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

// 2. Build the WHERE clause using the "title" column
$where_clause = "";
if ($category == 'low-stock') {
    $where_clause = " WHERE title LIKE '%Low Stock%' ";
} elseif ($category == 'expiry-warning') {
    $where_clause = " WHERE title LIKE '%Expiry%' OR title LIKE '%Expired%' ";
}

// 3. Pagination Setup
$limit = 20;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// 4. Fetch total count for pagination (with the filter)
$total_query = "SELECT COUNT(*) as total FROM notification" . $where_clause;
$total_res = mysqli_query($conn, $total_query);
$total_count = mysqli_fetch_assoc($total_res)['total'];
$total_pages = ceil($total_count / $limit);

// 5. Fetch notifications with newest first
$notif_sql = "SELECT * FROM notification 
              $where_clause 
              ORDER BY created_at DESC 
              LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $notif_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>All Alerts - Rodal Store</title>

    <link rel="icon" type="image/png" href="http://localhost:8000/RODALSystem/rodal-icon.png">

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cloudflare.com">
    <script src="script.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>

<body>
    <div class="container">
        <!-- Re-use your existing sidebar here -->

        <main class="main-content">
            <header>
                <h1>Notification History</h1>
                <a href="dashboard.php" class="rodal-back-btn">
                    <span class="logo-letter">R</span>
                    <span class="full-text">Back to Dashboard</span>
                </a>
            </header>

            <!-- FILTER BAR -->

            <div class="top-bar">

                <div class="pagination-container">
                    <?php
                    $search_param = isset($_GET['search']) ? "&search=" . urlencode($_GET['search']) : "";
                    $cat_param = isset($_GET['category']) ? "&category=" . urlencode($_GET['category']) : "";
                    $url_params = $search_param . $cat_param;
                    ?>

                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo ($page - 1) . $url_params; ?>">&laquo; Prev</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i . $url_params; ?>"
                                class="<?php echo ($page == $i) ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo ($page + 1) . $url_params; ?>">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="filter-bar">

                    <form method="GET" id="notifFilterForm">
                        <div class="modern-dropdown" id="notifDropdown">
                            <div class="dropdown-trigger">
                                <span id="notifDisplay">
                                    <?php
                                    $currentNotif = $_GET['category'] ?? '';
                                    if ($currentNotif == 'low-stock') echo 'Low Stock';
                                    elseif ($currentNotif == 'expiry-warning') echo 'Expiry Warning';
                                    else echo 'All Notifications';
                                    ?>
                                </span>
                                <i class="fas fa-chevron-down"></i>
                            </div>

                            <ul class="dropdown-menu">
                                <li data-value="" class="<?php echo ($currentNotif == '') ? 'active' : ''; ?>">All Notifications</li>
                                <li data-value="low-stock" class="<?php echo ($currentNotif == 'low-stock') ? 'active' : ''; ?>">Low Stock</li>
                                <li data-value="expiry-warning" class="<?php echo ($currentNotif == 'expiry-warning') ? 'active' : ''; ?>">Expiry Warning</li>
                            </ul>

                            <input type="hidden" name="category" id="realNotifInput" value="<?php echo htmlspecialchars($currentNotif); ?>">

                            <?php if (isset($_GET['search'])): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                            <?php endif; ?>
                        </div>
                    </form>

                </div>

            </div>

            <section class="notification-history">
                <table class="grid-table">
                    <thead class="grid-thead">
                        <tr>
                            <th>Type</th>
                            <th>Notification</th>
                            <th>Message</th>
                            <th style="text-align: right;">Timestamp</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        // Reset the pointer if you've already used $result once above
                        mysqli_data_seek($result, 0);

                        while ($row = mysqli_fetch_assoc($result)):
                            $statusClass = ($row['status'] == 'unread') ? 'status-unread' : 'status-read';
                            $titleClass = '';
                            $icon = '🔔';
                            $lowerTitle = strtolower($row['title']);

                            if (strpos($lowerTitle, 'low stock') !== false) {
                                $titleClass = 'text-low-stock';
                                $icon = '⚠️';
                            } elseif (strpos($lowerTitle, 'expiry') !== false || strpos($lowerTitle, 'expired') !== false) {
                                $titleClass = 'text-expiry';
                                $icon = '🚫';
                            }
                        ?>
                            <tr class="<?php echo $statusClass; ?> <?php echo $titleClass; ?>">
                                <td class="icon-cell">
                                    <span class="status-icon"><?php echo $icon; ?></span>
                                </td>
                                <td>
                                    <div class="title-text"><?php echo $row['title']; ?></div>
                                    <span class="badge"><?php echo strtoupper($row['status']); ?></span>
                                </td>
                                <td class="message-cell"><?php echo $row['message']; ?></td>
                                <td class="time-cell">
                                    <div class="date"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></div>
                                    <div class="time"><?php echo date('h:i A', strtotime($row['created_at'])); ?></div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>


    <style>
        /* Optional: Make them pop more if the background is read/unread */
        .status-unread .text-expiry strong {
            text-shadow: 0 0 1px rgba(239, 68, 68, 0.2);
        }


        .status-unread {
            background-color: #fff9e6;
            border-left: 4px solid var(--card-yellow);
        }

        .status-read {
            opacity: 0.7;
        }


        /* NOTIFICAITON */

        .notification-history {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        /* FILTER BAR */
        .filter-bar {
            margin: 18px 0;
            display: flex;
            justify-content: flex-end;
        }

        .filter-bar select {
            padding: 10px 16px;
            border-radius: 12px;
            border: 1px solid #dbe2ea;
            background: linear-gradient(135deg, #ffffff, #f8fafc);
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            cursor: pointer;
            outline: none;
            transition: 0.2s ease;
        }

        .filter-bar select:hover,
        .filter-bar select:focus {
            border-color: #fff9c4;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
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

            /* Typography improvements */
            font-size: 13px;
            font-weight: 600;
            color: #2d3748;
            letter-spacing: 0.3px;

            transition: all 0.3s ease;
        }

        .dropdown-trigger:hover {
            border-color: #FAF089;
            /* Light yellow border */
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            /* Optional: Adds a subtle lift effect */
        }

        /* logo */
        .rodal-back-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: var(--card-yellow);
            border: 2px solid var(--accent);
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 800;

            /* Starting State: A Circle */
            width: 45px;
            height: 45px;
            border-radius: 50px;
            padding: 0;

            overflow: hidden;
            position: relative;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* The "R" Logo */
        .logo-letter {
            font-size: 1.5rem;
            position: absolute;
            transition: transform 0.4s ease, opacity 0.3s ease;
        }

        /* The Dashboard Text */
        .full-text {
            font-size: 0.9rem;
            white-space: nowrap;
            opacity: 0;
            transform: translateX(20px);
            /* Pushed to the right initially */
            transition: all 0.4s ease;
        }

        /* Hover State */
        .rodal-back-btn:hover {
            width: 200px;
            /* Expands to fit the text */
            background-color: var(--accent);
            padding: 0 20px;
        }

        .rodal-back-btn:hover .logo-letter {
            transform: translateX(-30px);
            /* R slides out to the left */
            opacity: 0;
        }

        .rodal-back-btn:hover .full-text {
            opacity: 1;
            transform: translateX(0);
            /* Text slides into center */
        }

        .rodal-back-btn:hover .logo-letter {
            transform: translateX(-30px) rotate(-90deg);
            /* Adds a little spin to the exit */
            opacity: 0;
        }

        /* Container styling */

        .grid-thead th {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            color: #000000;
            padding: 10px 20px;
            font-family: 'Poppins', sans-serif;
        }

        .grid-table td {
            padding: 15px 20px;
            border: none;
        }

        /* Status Badge Modernization */
        .badge {
            padding: 5px 12px;
            font-size: 0.7rem;
            font-weight: bold;
            color: #666;
            border: 1px solid #ddd;
            background: var(--accent);
            color: white;
            border-radius: 4px;
            text-transform: uppercase;
        }

        /* Low Stock: Light Yellow Base & Hover */
        .text-low-stock {
            background-color: #fffdf5;
            /* Very faint yellow */
            border-left: 5px solid #fcc419;
        }

        .text-low-stock:hover {
            background-color: #fdf2cc !important;
            /* Slightly deeper light yellow on hover */
            transform: translateY(-2px);
        }

        /* Expiry Warning: Light Red Base & Hover */
        .text-expiry {
            background-color: #fffafa;
            /* Very faint red */
            border-left: 5px solid #ff5050;
        }

        .text-expiry:hover {
            background-color: #ffc4c4 !important;
            /* Slightly deeper light red on hover */
            transform: translateY(-2px);
        }

        /* --- Text Color Indicators (The "Notification" Column) --- */

        /* This targets the <strong> or <div> inside the title cell */
        .text-low-stock .title-text,
        .text-low-stock strong {
            color: #d99100;
            /* Darker Yellow/Amber for readability */
        }

        .text-expiry .title-text,
        .text-expiry strong {
            color: #e03131;
            /* Clear Red */
        }

        /* Message Styling */
        .grid-table td:nth-child(3) {
            color: #555;
            font-size: 0.9rem;
            max-width: 300px;
        }

        /* Icon styling */
        .icon-cell {
            width: 60px;
            text-align: center;
        }

        .status-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: #f0f0f0;
            /* Default background */
            border-radius: 12px;
            font-size: 1.2rem;
        }

        /* Specific colors for specific types */
        .text-low-stock .status-icon {
            background: #fff4e5;
            /* Soft Orange */
        }

        .text-expiry .status-icon {
            background: #ffebee;
            /* Soft Red */
        }

        /* Title and Badge layout */
        .title-text {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 4px;
        }


        /* Time Cell styling */
        .time-cell {
            text-align: right;
            line-height: 1.2;
        }

        .date {
            font-weight: 600;
            color: var(--text-dark);
        }

        .time {
            font-size: 0.8rem;
            color: #999;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Select all modern dropdowns on the page
            const allDropdowns = document.querySelectorAll('.modern-dropdown');

            allDropdowns.forEach(dropdown => {
                const trigger = dropdown.querySelector('.dropdown-trigger');
                const menuItems = dropdown.querySelectorAll('.dropdown-menu li');
                const hiddenInput = dropdown.querySelector('input[type="hidden"]');
                const displaySpan = trigger.querySelector('span');
                const parentForm = dropdown.closest('form');

                // Toggle Open/Close
                trigger.addEventListener('click', function(e) {
                    e.stopPropagation();

                    // Close any other open dropdowns first for a clean UI
                    allDropdowns.forEach(other => {
                        if (other !== dropdown) other.classList.remove('is-open');
                    });

                    dropdown.classList.toggle('is-open');
                });

                // Handle Item Selection
                menuItems.forEach(item => {
                    item.addEventListener('click', function() {
                        const val = this.getAttribute('data-value');
                        const text = this.innerText;

                        // Update the hidden input and the visible label
                        if (hiddenInput) hiddenInput.value = val;
                        if (displaySpan) displaySpan.innerText = text;

                        // Update Active Styling
                        menuItems.forEach(li => li.classList.remove('active'));
                        this.classList.add('active');

                        // Close the dropdown
                        dropdown.classList.remove('is-open');

                        /**
                         * LOGIC ROUTING:
                         * 1. If it's the Category Filter (Instant JS Filter)
                         */
                        if (dropdown.id === "categoryDropdown" && typeof filterCategory === "function") {
                            filterCategory();
                        }

                        /**
                         * 2. If it's a Form Filter (Year or Notifications - Page Refresh)
                         */
                        if (parentForm && dropdown.id !== "categoryDropdown") {
                            parentForm.submit();
                        }
                    });
                });
            });

            // Close dropdowns if user clicks anywhere else on the screen
            window.addEventListener('click', function() {
                allDropdowns.forEach(d => d.classList.remove('is-open'));
            });
        });

        /**
         * INSTANT TABLE FILTER (For Stocks Page)
         * This works by reading the hidden input we just updated above.
         */
        function filterCategory() {
            const hiddenInput = document.getElementById("realCategoryInput");
            if (!hiddenInput) return;

            const selected = hiddenInput.value;
            const rows = document.querySelectorAll("#productBody tr");

            rows.forEach(row => {
                const category = row.getAttribute("data-category");
                if (selected === "all" || selected === "" || category === selected) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }
    </script>
</body>

<style>

</style>


</html>