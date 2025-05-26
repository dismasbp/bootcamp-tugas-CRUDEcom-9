<?php
// Include database connection
include '../src/db.php';

// Pagination configuration
$itemsPerPage   = 9;
$page           = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
$search         = isset($_GET['search']) ? trim($_GET['search']) : '';
$category       = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort           = isset($_GET['sort']) ? $_GET['sort'] : '';

// Fetch all categories for filter dropdown
$categories     = [];
$catStmt        = $conn->prepare("SELECT id, name FROM categories ORDER BY name");
$catStmt->execute();
$catResult  = $catStmt->get_result();
while ($row = $catResult->fetch_assoc()) {
    $categories[] = $row;
}
$catStmt->close();

// Build base SQL with filters
$whereClauses   = ["1=1"];
$params         = [];
$paramTypes     = "";

// Search filter
if ($search !== '') {
    $whereClauses[] = "p.name LIKE ?";
    $params[]       = '%' . $search . '%';
    $paramTypes .= "s";
}

// Category filter
if ($category > 0) {
    $whereClauses[] = "p.catid = ?";
    $params[]       = $category;
    $paramTypes .= "i";
}

$whereSQL = implode(" AND ", $whereClauses);

// Get total count for pagination
$countSql   = "SELECT COUNT(*) as total FROM products p WHERE $whereSQL";
$countStmt  = $conn->prepare($countSql);
if ($paramTypes !== "") {
    $countStmt->bind_param($paramTypes, ...$params);
}
$countStmt->execute();
$countResult    = $countStmt->get_result();
$totalItems     = 0;
if ($countRow = $countResult->fetch_assoc()) {
    $totalItems = (int)$countRow['total'];
}
$countStmt->close();

// Calculate total pages and offset
$totalPages = ceil($totalItems / $itemsPerPage);
$page       = min($page, $totalPages > 0 ? $totalPages : 1);
$offset     = ($page - 1) * $itemsPerPage;

// Build main SQL query with sorting and limit
$sql = "SELECT p.id, p.name, p.price, p.photo, p.stock, p.description, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.catid = c.id
        WHERE $whereSQL";

if ($sort === 'price_asc') {
    $sql .= " ORDER BY p.price ASC";
} elseif ($sort === 'price_desc') {
    $sql .= " ORDER BY p.price DESC";
} else {
    $sql .= " ORDER BY p.name ASC";
}

$sql .= " LIMIT ? OFFSET ?";


// Prepare statement and bind params + pagination
$stmt = $conn->prepare($sql);
if ($paramTypes === "") {
    // Only pagination params
    $stmt->bind_param("ii", $itemsPerPage, $offset);
} else {
    // Filters + pagination
    $paramTypesPagination = $paramTypes . "ii";
    $bindParams = array_merge($params, [$itemsPerPage, $offset]);
    // bind_param requires references
    $refs = [];
    foreach ($bindParams as $key => $value) {
        $refs[$key] = &$bindParams[$key];
    }
    // Call bind_param with references
    call_user_func_array([$stmt, 'bind_param'], array_merge([$paramTypesPagination], $refs));
}
$stmt->execute();
$result = $stmt->get_result();

$userStmt = $conn->prepare("SELECT id, name, email FROM users ORDER BY name");
$userStmt->execute();
$userResult = $userStmt->get_result();
$users = [];
while ($user = $userResult->fetch_assoc()) {
    $users[] = $user;
}
$userStmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Transaction</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <link href="css/bootstrap.css" rel="stylesheet" />
</head>
<body>
    <header class="bg-light p-3">
        <div class="container">
            <nav class="navbar navbar-light navbar-expand-lg bg-body-tertiary">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#">GadgetStore</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                                <a class="nav-link" href="backoffice/dashboard.php">Settings</a>
                            </li>
                        </ul>
                    </div>
                    <div class="d-flex">
                        <a class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#TrxDetail">
                            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-cart-fill" viewBox="0 0 16 16">
                                <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <main class="container mt-4">
        <div class="container mt-4">
            <form method="get" action="" class="row g-3 mb-4 align-items-center">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by name" value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select">
                        <option value="0">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $category) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="sort" class="form-select">
                        <option value="">Sort by Name (A-Z)</option>
                        <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>

            <?php if ($result->num_rows > 0): ?>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
                    <?php while ($product = $result->fetch_assoc()): ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm">
                                <img src="<?= htmlspecialchars($product['photo']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>" style="height:200px; object-fit:cover;">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                                    <h6 class="text-success card-text fw-bold">Rp <?= number_format($product['price'], 0, ',', '.') ?></h6>
                                    <p class="card-text text-truncate"><?= htmlspecialchars($product['description']) ?></p>
                                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-primary mt-auto" onclick="createNewOrder(<?= $product['id'] ?>, <?= $product['stock'] ?>, <?= $product['price'] ?>, '<?= addslashes(htmlspecialchars($product['name'])) ?>')">Add To Cart</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <script>
                    // Store cart products
                    const cartProducts = new Map();

                    function createNewOrder(productId, stock, price, name) {
                        if (cartProducts.has(productId)) {
                            alert('Product Already Added');
                            return;
                        }

                        if (stock === 0) {
                            alert('Insufficient Stock');
                            return;
                        }

                        const productsContainer = document.getElementById('products');

                        // Create row for product item
                        const productRow        = document.createElement('div');
                        productRow.id           = 'product' + productId;
                        productRow.className    = 'row align-items-center mb-2 g-2';

                        // Hidden input column for product id
                        const hiddenCol         = document.createElement('div');
                        hiddenCol.className     = 'd-none';
                        const inputProductId    = document.createElement('input');
                        inputProductId.type     = 'hidden';
                        inputProductId.name     = `product_id[${productId}]`;
                        inputProductId.value    = productId;
                        hiddenCol.appendChild(inputProductId);

                        // Delete button column
                        const delCol            = document.createElement('div');
                        delCol.className        = 'col-1 d-flex justify-content-center';
                        const btnDel            = document.createElement('a');
                        btnDel.className        = 'btn btn-danger btn-sm';
                        btnDel.innerText        = '-';
                        btnDel.onclick          = () => handleCount(productId, 0);
                        delCol.appendChild(btnDel);

                        // Quantity input column
                        const qtyCol            = document.createElement('div');
                        qtyCol.className        = 'col-3 d-flex justify-content-center';
                        const inputQty          = document.createElement('input');
                        inputQty.type           = 'number';
                        inputQty.id             = `qty[${productId}]`;
                        inputQty.name           = `qty[${productId}]`;
                        inputQty.className      = 'form-control form-control-sm';
                        inputQty.min            = 1;
                        inputQty.max            = stock;
                        inputQty.value          = 1;
                        inputQty.onchange       = () => handleChangeCount(productId);
                        qtyCol.appendChild(inputQty);

                        // Add button column
                        const addCol            = document.createElement('div');
                        addCol.className        = 'col-1 d-flex justify-content-center';
                        const btnAdd            = document.createElement('a');
                        btnAdd.className        = 'btn btn-primary btn-sm';
                        btnAdd.innerText        = '+';
                        btnAdd.onclick          = () => handleCount(productId, 1);
                        addCol.appendChild(btnAdd);

                        // Name column
                        const nameCol           = document.createElement('div');
                        nameCol.className       = 'col-4 d-flex justify-content-center';
                        nameCol.innerText       = name;
                        nameCol.classList.add('fw-semibold');

                        // Price column
                        const priceCol          = document.createElement('div');
                        priceCol.className      = 'col-3 d-flex justify-content-center';
                        priceCol.id             = 'price' + productId;
                        priceCol.innerText      = price;

                        // Function to update price display
                        function updatePrice() {
                            const qty                   = parseInt(inputQty.value);
                            const totalPrice            = qty * price;
                            priceCol.innerText    = 'Rp ' + totalPrice.toLocaleString();
                            updateSubtotal();
                        }

                        // Initialize price
                        updatePrice();

                        // Append all columns to the row
                        productRow.appendChild(hiddenCol);
                        productRow.appendChild(delCol);
                        productRow.appendChild(qtyCol);
                        productRow.appendChild(addCol);
                        productRow.appendChild(nameCol);
                        productRow.appendChild(priceCol);

                        // Append the row to the container
                        productsContainer.appendChild(productRow);

                        // Add product to cartProducts map
                        cartProducts.set(productId, { stock: stock, price: price, quantity: 1, element: productRow });
                    }

                    function handleCount(productId, type) {
                        if (!cartProducts.has(productId)) return;

                        const product = cartProducts.get(productId);
                        const inputQty = document.getElementById(`qty[${productId}]`);
                        let currentQty = parseInt(inputQty.value);

                        if (type === 1) { // increment
                            if (currentQty < product.stock) {
                                currentQty++;
                            } else {
                                alert("Insufficient Stock");
                            }
                        } else if (type === 0) { // decrement
                            currentQty--;
                            if (currentQty < 1) {
                                // Remove product from cart
                                product.element.remove();
                                cartProducts.delete(productId);
                                updateSubtotal();
                                return;
                            }
                        }

                        inputQty.value = currentQty;
                        updatePriceDisplay(productId, currentQty);
                    }

                    function handleChangeCount(productId) {
                        if (!cartProducts.has(productId)) return;

                        const product = cartProducts.get(productId);
                        const inputQty = document.getElementById(`qty[${productId}]`);
                        let currentQty = parseInt(inputQty.value);

                        if (currentQty > product.stock) {
                            alert('Insufficient Stock');
                            currentQty = product.stock;
                        } else if (currentQty < 1 || isNaN(currentQty)) {
                            // Remove product from cart when quantity less than 1 or invalid
                            product.element.remove();
                            cartProducts.delete(productId);
                            updateSubtotal();
                            return;
                        }

                        inputQty.value = currentQty;
                        updatePriceDisplay(productId, currentQty);
                    }

                    function updatePriceDisplay(productId, qty) {
                        const product = cartProducts.get(productId);
                        const priceContainer = document.getElementById('price' + productId);
                        const totalPrice = qty * product.price;
                        priceContainer.innerText = 'Rp ' + totalPrice.toLocaleString();

                        // Update stored quantity
                        product.quantity = qty;

                        updateSubtotal();
                    }

                    function updateSubtotal() {
                        let subtotal = 0;
                        cartProducts.forEach(p => {
                            subtotal += p.quantity * p.price;
                        });

                        document.getElementById('subtotal').innerText = 'Rp ' + subtotal.toLocaleString();

                        updatePayButton(subtotal);
                    }

                    function updatePayButton(subtotal) {
                        const payBtn = document.getElementById('pay');
                        const amountPaidInput = document.getElementById('value');
                        const finalPriceElem = document.getElementById('finalprice');

                        let amountPaid = parseFloat(amountPaidInput.value);
                        if (isNaN(amountPaid)) amountPaid = 0;

                        finalPriceElem.innerText = 'Rp ' + subtotal.toLocaleString();

                        if (amountPaid >= subtotal && subtotal > 0) {
                            payBtn.removeAttribute('disabled');
                        } else {
                            payBtn.setAttribute('disabled', '');
                        }
                    }

                    // Listen to amount paid input changes
                    // document.getElementById('value').addEventListener('input', () => {
                    //     const subtotalText = document.getElementById('subtotal').innerText;
                    //     const subtotalNumber = parseFloat(subtotalText.replace(/[^0-9.-]+/g,"")) || 0;
                    //     updatePayButton(subtotalNumber);
                    // });
                    // document.addEventListener('DOMContentLoaded', () => {
                    //     const valueInput = document.getElementById('value');
                    //     if (valueInput) {
                    //         valueInput.addEventListener('input', () => {
                    //         const subtotalText = document.getElementById('subtotal').innerText;
                    //         const subtotalNumber = parseFloat(subtotalText.replace(/[^0-9.-]+/g,"")) || 0;
                    //         updatePayButton(subtotalNumber);
                    //         });
                    //     }
                    // });
                </script>

                <!-- Modal Detail Transaction -->
                <div class="modal fade" id="TrxDetail" tabindex="-1" aria-labelledby="TrxDetailLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="TrxDetailLabel">Order Detail</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form name="order" action="/backoffice/Controller/order/store_order.php" id="order" role="form" method="POST">
                                <div class="modal-body">
                                    <div id="products" class="mb-3"></div>

                                    <div class="mb-3">
                                        <label for="userid" class="form-label">Customer</label>
                                        <select id="userid" name="userid" class="form-select" required>
                                            <option value="">Select Customer</option>
                                            <?php foreach ($users as $user): ?>
                                                <option value="<?= $user['id'] ?>">
                                                    <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <h4>Subtotal</h4>
                                        <div class="p-2" id="subtotal">Rp 0</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="value" class="form-label">Amount Paid</label>
                                        <input type="number" id="value" name="value" class="form-control" min="0" placeholder="Amount Paid" />
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <div class="w-100 text-center">
                                        <h5 class="mb-1">Total</h5>
                                        <div class="h4 fw-bold" id="finalprice">Rp 0</div>
                                        <button type="submit" id="pay" class="btn btn-primary" style="border-radius: 8px; width: 260px;" disabled>Pay</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <nav aria-label="Page navigation example" class="mt-4">
                    <ul class="pagination justify-content-center">

                        <!-- Previous Button -->
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= buildPageUrl($page - 1) ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>

                        <?php
                        // Show page numbers (show max 5 pages around current for simplicity)
                        $startPage  = max(1, $page - 2);
                        $endPage    = min($totalPages, $page + 2);
                        for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link" href="<?= buildPageUrl($i) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <!-- Next Button -->
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= buildPageUrl($page + 1) ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>

            <?php else: ?>
                <div class="alert alert-warning" role="alert">
                    No products found matching your criteria.
                </div>
            <?php endif; ?>
        </div>

        <?php
        $stmt->close();
        $conn->close();

        /**
         * Helper function to build pagination URLs while preserving filters
         */
        function buildPageUrl($pageNum) {
            // Merge current GET params, but replace page number
            $params         = $_GET;
            $params['page'] = $pageNum;

            // Build query string
            return '?' . http_build_query($params);
        }
        ?>
    </main>

    <footer class="bg-light text-center py-3 mt-5">
        <div class="container">
            <p>© <?= date("Y") ?> dismasbp</p>
        </div>
    </footer>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script>
        // Listen to amount paid input changes
        document.getElementById('value').addEventListener('input', () => {
            const subtotalText = document.getElementById('subtotal').innerText;
            const subtotalNumber = parseFloat(subtotalText.replace(/[^0-9.-]+/g,"")) || 0;
            updatePayButton(subtotalNumber);
        });
    </script>
</body>
</html>
