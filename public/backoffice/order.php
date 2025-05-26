<?php
// Pagination setup
$limit  = 10;
$page   = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Include database connection
include '../../src/db.php';

// Set page title and include CSS/JS
$title = 'Order List';
$cssbs = '../css/bootstrap.css';
$jsbs  = '../js/bootstrap.bundle.min.js';

// Define links for navigation
$dashboardlink  = '/backoffice/dashboard.php';
$productlink    = '/backoffice/product.php';
$categorylink   = '/backoffice/category.php';
$orderlink      = '/backoffice/order.php';

// Get total orders count
$countSql        = "SELECT COUNT(*) as total FROM orders";
$countResult     = $conn->query($countSql);
$totalOrders     = $countResult ? (int) $countResult->fetch_assoc()['total'] : 0;
$totalPages      = ceil($totalOrders / $limit);

// Fetch orders with limit and offset
$sql    = "SELECT o.id, u.name AS user_name, o.total
            FROM orders o
            LEFT JOIN users u ON o.userid = u.id
            ORDER BY o.id DESC
            LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Fetch order details
$detailsdata    = "SELECT od.qty, od.orderid, od.productid, od.qty
                    FROM order_details od";
$datadetails    = $conn->query($detailsdata);

// Fetch product names for details
$productdata    = "SELECT p.id, p.name
                    FROM products p";
$dataproduct    = $conn->query($productdata);
include 'layout/header.php';
?>
<div class="container ps-0 ms-0 pb-5">
    <div class="row row-col-2">
        <div class="col">
            <h2>Order List</h2>
        </div>
    </div>
    <div class="panel panel-scrollable">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">User</th>
                    <th scope="col">Total</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php $no = 1 + $offset; while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <th scope="row"><?php echo $no++; ?></th>
                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                            <td><?php echo number_format($row['total'], 0, ',', '.'); ?></td>
                            <td>
                                <!-- Button trigger modal detail -->
                                <button type="button" class="btn btn-sm btn-success" title="Detail"
                                    data-bs-toggle="modal" data-bs-target="#detailModal-<?= $row['id']; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                        <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                        <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                                    </svg>
                                </button>

                                <!-- Bootstrap Detail Modal -->
                                <div class="modal fade" id="detailModal-<?= $row['id']; ?>" tabindex="-1" aria-labelledby="detailModalLabel-<?= $row['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="detailModalLabel-<?= $row['id']; ?>">Order Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <div class="row">
                                                    <div class="col">
                                                        <strong>Product:</strong>
                                                    </div>
                                                    <div class="col">
                                                        <strong>Quantity:</strong>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <?php
                                                    foreach ($datadetails as $detail) {
                                                        if ($detail['orderid'] == $row['id']) {
                                                            foreach ($dataproduct as $product) {
                                                                if ($product['id'] == $detail['productid']) { ?>
                                                                    <div class="row mb-2 mt-2">
                                                                        <div class="col">
                                                                            <?= htmlspecialchars($product['name']); ?>
                                                                        </div>
                                                                        <div class="col">
                                                                            <?= htmlspecialchars($detail['qty']); ?>
                                                                        </div>
                                                                    </div>
                                                                <?php }
                                                            }
                                                        }
                                                    } ?>
                                                </div>
                                            </div>
                                            <div class="modal-footer justify-content-center">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Button trigger modal delete -->
                                <button type="button" class="btn btn-sm btn-danger" title="Delete"
                                    data-bs-toggle="modal" data-bs-target="#deleteModal-<?= $row['id']; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5.5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6zm2 .5a.5.5 0 0 1 .5-.5.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6z"/>
                                        <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9.5A1.5 1.5 0 0 1 11.5 15h-7A1.5 1.5 0 0 1 3 13.5V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1H14a1 1 0 0 1 1 1v1zM4.118 4 4 13.5a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                    </svg>
                                </button>

                                <!-- Bootstrap Delete Modal -->
                                <div class="modal fade" id="deleteModal-<?= $row['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel-<?= $row['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel-<?= $row['id']; ?>">Delete Order</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                Are you sure you want to delete order<br>
                                                <strong>#<?= $row['id']; ?> (<?= htmlspecialchars($row['user_name']); ?> - <?= htmlspecialchars($row['product_name']); ?>)</strong>?
                                            </div>
                                            <div class="modal-footer justify-content-center">
                                                <form action="delete-order.php" method="get" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                                </form>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <tfoot>
                <tr>
                    <td colspan="6">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <!-- Previous button -->
                                <li class="page-item<?php if ($page <= 1) echo ' disabled'; ?>">
                                    <a class="page-link" href="?page=<?= max(1, $page - 1); ?>" tabindex="-1">Previous</a>
                                </li>
                                <?php
                                    // Show max 5 page links, centered around current page
                                    $start = max(1, $page - 2);
                                    $end = min($totalPages, $page + 2);
                                    if ($start > 1) {
                                        echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                                        if ($start > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                    for ($i = $start; $i <= $end; $i++):
                                ?>
                                    <li class="page-item<?php if ($i == $page) echo ' active'; ?>">
                                        <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                                    </li>
                                <?php endfor;
                                    if ($end < $totalPages) {
                                        if ($end < $totalPages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                        echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . '">' . $totalPages . '</a></li>';
                                    }
                                ?>
                                <!-- Next button -->
                                <li class="page-item<?php if ($page >= $totalPages) echo ' disabled'; ?>">
                                    <a class="page-link" href="?page=<?= min($totalPages, $page + 1); ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
<?php
include 'layout/footer.php';
?>