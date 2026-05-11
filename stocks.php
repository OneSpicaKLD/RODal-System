<?php
session_start();

if (!isset($_SESSION['isLoggedIn'])) {
    header("Location: index.php");
    exit();
}

require 'db_connect.php';

// 1. Capture All Filters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$currentCat = (isset($_GET['category']) && $_GET['category'] !== 'all') ? mysqli_real_escape_string($conn, $_GET['category']) : 'all';
$sort = $_GET['sort'] ?? 'default'; // Capture Sort

// --- Define Dynamic ORDER BY ---
switch ($sort) {
    case 'most_sold':
        $orderBy = "total_out DESC";
        break;
    case 'least_sold':
        $orderBy = "total_out ASC";
        break;
    case 'alpha_asc':
        $orderBy = "p.product_name ASC";
        break;
    case 'alpha_desc':
        $orderBy = "p.product_name DESC";
        break;
    case 'price_asc':
        $orderBy = "p.price ASC";
        break;
    case 'price_desc':
        $orderBy = "p.price DESC";
        break;
    case 'stock_low':
        $orderBy = "(SUM(CASE WHEN t.transaction_type = 'IN' THEN t.quantity ELSE 0 END) - SUM(CASE WHEN t.transaction_type = 'OUT' THEN t.quantity ELSE 0 END)) ASC";
        break;
    case 'stock_high':
        $orderBy = "(SUM(CASE WHEN t.transaction_type = 'IN' THEN t.quantity ELSE 0 END) - SUM(CASE WHEN t.transaction_type = 'OUT' THEN t.quantity ELSE 0 END)) DESC";
        break;
    default:
        $orderBy = "p.product_id ASC";
}

// 2. Setup Pagination Variables
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// 3. Build Dynamic WHERE Clause
$conditions = [];
$conditions[] = "p.is_active = 1";

// Handle Search
if (!empty($search)) {
    $conditions[] = "p.product_name LIKE '%$search%'";
}

// FIX: Use $currentCat (matching your Section 1)
if ($currentCat !== 'all') {
    $conditions[] = "c.category_name = '$currentCat'";
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
$sql = "SELECT p.product_id, p.product_name, p.product_sku, p.cost, p.category_id, c.category_name, p.price,
        SUM(CASE WHEN t.transaction_type = 'IN' THEN t.quantity ELSE 0 END) as total_in,
        SUM(CASE WHEN t.transaction_type = 'OUT' THEN t.quantity ELSE 0 END) as total_out
        FROM product p
        JOIN category c ON p.category_id = c.category_id
        LEFT JOIN stock_transaction t ON p.product_id = t.product_id
        $whereClause
        GROUP BY p.product_id
        ORDER BY $orderBy 
        LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $sql);

// 6. Fetch categories for the edit modal dropdown
$cat_query_modal = "SELECT category_id, category_name FROM category ORDER BY category_name ASC";
$cat_result_modal = mysqli_query($conn, $cat_query_modal);
$categories_for_modal = [];
while ($cat = mysqli_fetch_assoc($cat_result_modal)) {
    $categories_for_modal[] = $cat;
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rodal Store - Products</title>

    <link rel="icon" type="image/png" href="rodal-icon.png">

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="script.js"></script>
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
                <li class="active"><a href="stocks.php"><i class="fas fa-boxes"></i> <span>Products</span></a></li>
                <li><a href="profit_status.php"><i class="fas fa-coins"></i> <span>Profit Status</span></a></li>
                <li><a href="purchase-history.php"><i class="fas fa-history"></i><span>Transactions History</span></a>
                </li>
                <li><a href="archived_products.php"><i class="fas fa-archive"></i> <span>Archived Products</span></a>
                </li>
            </ul>
        </aside>

        <main class="main-content">
            <header>
                <div class="header-title">
                    <h1>Product Inventory</h1>
                </div>

                <div class="header-right">
                    <div class="search-bar">
                        <form method="GET" action="stocks.php">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Search..."
                                value="<?php echo htmlspecialchars($search); ?>">
                            <?php if (!empty($search)): ?>
                                <a href="stocks.php" class="clear-search" title="Clear Search">
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
                                <ul class="notif-list" id="notifList"></ul>
                                <div class="notif-footer">
                                    <a href="all_notifications.php">View all alerts</a>
                                </div>
                            </div>
                        </div>

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

                <!-- HEADER + FILTER -->
                <div class="top-bar">
                    <button class="add-product-btn" onclick="openAddProductModal()">
                        <i class="fas fa-plus"></i> Add Product
                    </button>

                    <div class="pagination-container">
                        <?php
                        $url_params = '';
                        if (!empty($search))
                            $url_params .= '&search=' . urlencode($search);
                        if (!empty($category))
                            $url_params .= '&category=' . urlencode($category);
                        if ($sort !== 'default')
                            $url_params .= '&sort=' . urlencode($sort); // <--- ADD THIS

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

                    <?php
                    // Top of stocks.php
                    $search = $_GET['search'] ?? '';
                    $currentCat = $_GET['category'] ?? 'all';
                    ?>

                    <form method="GET" id="filterForm" action="stocks.php" style="display: flex; gap: 15px;">
                        <!-- Keep the search value -->
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">

                        <!-- 2. SORT DROPDOWN (New) -->
                        <div class="modern-dropdown" id="sortDropdown">
                            <div class="dropdown-trigger">
                                <span>
                                    <?php
                                    $labels = [
                                        'default' => 'Default Sort',
                                        'most_sold' => 'Sales: Most Sold',
                                        'least_sold' => 'Sales: Least Sold',
                                        'alpha_asc' => 'Name: A to Z',
                                        'alpha_desc' => 'Name: Z to A',
                                        'price_asc' => 'Price: Low-High',
                                        'price_desc' => 'Price: High-Low',
                                        'stock_low' => 'Stock: Low-High',
                                        'stock_high' => 'Stock: High-Low'
                                    ];
                                    echo $labels[$sort] ?? 'Default Sort';
                                    ?>
                                </span>
                                <i class="fas fa-sort-amount-down"></i>
                            </div>
                            <ul class="dropdown-menu">
                                <li data-value="default" class="<?php echo ($sort == 'default') ? 'active' : ''; ?>">
                                    Default Sort</li>
                                <li data-value="most_sold"
                                    class="<?php echo ($sort == 'most_sold') ? 'active' : ''; ?>">Sales: Most Sold</li>
                                <li data-value="least_sold"
                                    class="<?php echo ($sort == 'least_sold') ? 'active' : ''; ?>">Sales: Least Sold
                                </li>
                                <li data-value="alpha_asc"
                                    class="<?php echo ($sort == 'alpha_asc') ? 'active' : ''; ?>">Name: A to Z</li>
                                <li data-value="alpha_desc"
                                    class="<?php echo ($sort == 'alpha_desc') ? 'active' : ''; ?>">Name: Z to A</li>
                                <li data-value="price_asc"
                                    class="<?php echo ($sort == 'price_asc') ? 'active' : ''; ?>">Price: Low to High
                                </li>
                                <li data-value="price_desc"
                                    class="<?php echo ($sort == 'price_desc') ? 'active' : ''; ?>">Price: High to Low
                                </li>
                                <li data-value="stock_low"
                                    class="<?php echo ($sort == 'stock_low') ? 'active' : ''; ?>">Stock: Low to High
                                </li>
                                <li data-value="stock_high"
                                    class="<?php echo ($sort == 'stock_high') ? 'active' : ''; ?>">Stock: High to Low
                                </li>
                            </ul>
                            <input type="hidden" name="sort" id="realSortInput"
                                value="<?php echo htmlspecialchars($sort); ?>">
                        </div>

                        <!-- 1. CATEGORY DROPDOWN (Existing) -->
                        <div class="modern-dropdown" id="categoryDropdown">
                            <div class="dropdown-trigger">
                                <span id="selectedDisplay">
                                    <?php echo ($currentCat === 'all') ? 'All Categories' : str_replace('_', ' ', $currentCat); ?>
                                </span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <ul class="dropdown-menu">
                                <li data-value="all" class="<?php echo ($currentCat == 'all') ? 'active' : ''; ?>">All
                                    Categories</li>
                                <?php
                                $cats = ["TOILETRIES", "BEVERAGE", "DRINK_POWDERED", "FOOD_CANNED", "FOOD_INSTANT", "FOOD_SNACK", "FOOD_INGREDIENT", "FOOD_RICE", "CLEANING_AGENTS"];
                                foreach ($cats as $cat) {
                                    $isActive = ($currentCat == $cat) ? 'active' : '';
                                    echo "<li data-value='$cat' class='$isActive'>" . str_replace('_', ' ', $cat) . "</li>";
                                }
                                ?>
                            </ul>
                            <input type="hidden" name="category" id="realCategoryInput"
                                value="<?php echo htmlspecialchars($currentCat); ?>">
                        </div>


                    </form>


                </div>

                <table class="grid-table">
                    <thead class="grid-thead">
                        <tr>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Sold</th>
                            <th>Price</th>
                            <th style="text-align: center;">Quantity</th>
                            <th>Expiry Date</th>
                            <th style="text-align: center;">Action</th>
                        </tr>
                    </thead>

                    <tbody id="productBody">
                        <?php while ($row = mysqli_fetch_assoc($result)) {
                            $stock = $row['total_in'] - $row['total_out'];
                            $sold = $row['total_out'];
                            $catAttr = strtoupper(str_replace(' ', '_', $row['category_name']));
                        ?>
                            <tr data-category="<?php echo $catAttr; ?>">
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><?php echo ucwords(strtolower(str_replace('_', ' ', $row['category_name']))); ?></td>
                                <td><?php echo $stock; ?></td>
                                <td><?php echo $sold; ?></td>
                                <td style="font-weight: bold; color: #2e7d32;">
                                    ₱<?php echo number_format($row['price'], 2); ?></td>
                                <td>
                                    <div class="quantity-box">
                                        <button onclick="changeQty(this,-1)">-</button>
                                        <input type="number" value="1" min="1" id="qty-<?php echo $row['product_id']; ?>">
                                        <button onclick="changeQty(this,1)">+</button>
                                    </div>
                                </td>
                                <td>
                                    <input type="date" id="expiry-<?php echo $row['product_id']; ?>"
                                        style="margin-top: 5px; font-size: 12px; padding: 2px;">
                                </td>
                                <td>
                                    <div class="action-group" style="display: flex; align-items: center; gap: 8px;">
                                        <button class="add-btn"
                                            onclick="updateStock(<?php echo $row['product_id']; ?>, 'IN')">Add</button>

                                        <button class="sell-btn"
                                            onclick="updateStock(<?php echo $row['product_id']; ?>, 'OUT', <?php echo $stock; ?>)">Sell</button>

                                        <div class="action-menu-container">
                                            <button class="action-trigger-btn"
                                                onclick="toggleMenu(event, <?php echo $row['product_id']; ?>)">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>

                                            <div id="menu-<?php echo $row['product_id']; ?>" class="dropdown-menu">
                                                <a href="javascript:void(0)" title="Edit" onclick="openEditModal(
            <?php echo $row['product_id']; ?>,
            '<?php echo addslashes(htmlspecialchars($row['product_name'])); ?>',
            '<?php echo addslashes(htmlspecialchars($row['product_sku'])); ?>',
            <?php echo $row['category_id']; ?>,
            <?php echo $row['cost']; ?>,
            <?php echo $row['price']; ?>
        )">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </a>

                                                <a href="javascript:void(0)" class="archive-link" title="Archive" onclick="archiveProduct(
            <?php echo $row['product_id']; ?>, 
            '<?php echo addslashes(htmlspecialchars($row['product_name'])); ?>'
        )">
                                                    <i class="fas fa-archive"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            </section>
        </main>
    </div>


    <!-- ══════════════════════════════════════════ -->
    <!--              ADD PRODUCT MODAL             -->
    <!-- ══════════════════════════════════════════ -->
    <div class="modal-overlay" id="addProductOverlay" onclick="closeAddProductModal(event)">
        <div class="modal-card">
            <div class="modal-header">
                <h2><i class="fas fa-box-open"></i> Add New Product</h2>
                <button class="modal-close" onclick="closeAddProductModal()"><i class="fas fa-times"></i></button>
            </div>

            <form id="addProductForm" onsubmit="submitAddProduct(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="new_product_name">Product Name <span class="required">*</span></label>
                        <input type="text" id="new_product_name" name="product_name"
                            placeholder="e.g. Lucky Me Pancit Canton" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_sku">SKU <span class="required">*</span></label>
                            <input type="text" id="new_sku" name="product_sku" placeholder="e.g. SKU-00123" required>
                        </div>
                        <div class="form-group">
                            <label for="new_category">Category <span class="required">*</span></label>
                            <select id="new_category" name="category_id" required>
                                <option value="">-- Select Category --</option>
                                <?php
                                foreach ($categories_for_modal as $cat) {
                                    $readable = ucwords(strtolower(str_replace('_', ' ', $cat['category_name'])));
                                    echo "<option value='{$cat['category_id']}'>$readable</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_cost">Cost Price (₱) <span class="required">*</span></label>
                            <input type="number" id="new_cost" name="cost" placeholder="0.00" step="0.01" min="0"
                                required>
                        </div>
                        <div class="form-group">
                            <label for="new_price">Selling Price (₱) <span class="required">*</span></label>
                            <input type="number" id="new_price" name="price" placeholder="0.00" step="0.01" min="0"
                                required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeAddProductModal()">Cancel</button>
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save Product</button>
                </div>
            </form>
        </div>
    </div>


    <!-- ══════════════════════════════════════════ -->
    <!--             EDIT PRODUCT MODAL             -->
    <!-- ══════════════════════════════════════════ -->
    <div class="modal-overlay" id="editProductOverlay" onclick="closeEditModal(event)">
        <div class="modal-card">
            <div class="modal-header">
                <h2><i class="fas fa-pen"></i> Edit Product</h2>
                <button class="modal-close" onclick="closeEditModal()"><i class="fas fa-times"></i></button>
            </div>

            <form id="editProductForm" onsubmit="submitEditProduct(event)">
                <input type="hidden" id="edit_product_id" name="product_id">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_product_name">Product Name <span class="required">*</span></label>
                        <input type="text" id="edit_product_name" name="product_name" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_sku">SKU <span class="required">*</span></label>
                            <input type="text" id="edit_sku" name="product_sku" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_category">Category <span class="required">*</span></label>
                            <select id="edit_category" name="category_id" required>
                                <option value="">-- Select Category --</option>
                                <?php
                                foreach ($categories_for_modal as $cat) {
                                    $readable = ucwords(strtolower(str_replace('_', ' ', $cat['category_name'])));
                                    echo "<option value='{$cat['category_id']}'>$readable</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_cost">Cost Price (₱) <span class="required">*</span></label>
                            <input type="number" id="edit_cost" name="cost" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_price">Selling Price (₱) <span class="required">*</span></label>
                            <input type="number" id="edit_price" name="price" step="0.01" min="0" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>

</body>

</html>