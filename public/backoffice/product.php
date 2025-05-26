<?php
// Pagination setup
$limit  = 10;
$page   = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Include database connection
include '../../src/db.php';

// Set the title and include necessary CSS and JS files
$title = 'Product List';
$cssbs = '../css/bootstrap.css';
$jsbs  = '../js/bootstrap.bundle.min.js';

// Define links for navigation
$dashboardlink  = '/backoffice/dashboard.php';
$productlink    = '/backoffice/product.php';
$categorylink   = '/backoffice/category.php';
$orderlink      = '/backoffice/order.php';

// Get total products count
$countSql       = "SELECT COUNT(*) as total FROM products";
$countResult    = $conn->query($countSql);
$totalProducts  = $countResult ? (int) $countResult->fetch_assoc()['total'] : 0;
$totalPages     = ceil($totalProducts / $limit);

// Fetch products with limit and offset
$sql    = "SELECT p.id, p.name, p.price, p.photo, p.stock, p.description, c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON p.catid = c.id
            LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
include 'layout/header.php';
?>
<div class="container ps-0 ms-0 pb-5">
    <div class="row row-col-2">
        <div class="col">
            <h2>Product List</h2>
        </div>
        <div class="col text-end">
            <a href="create/product.php" class="btn btn-primary">Add Product</a>
        </div>
    </div>
    <div class="panel panel-scrollable">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Category</th>
                    <th scope="col">Price</th>
                    <th scope="col">Stock</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php $no = 1 + $offset; while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <th scope="row"><?php echo $no++; ?></th>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                            <td><?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($row['stock']); ?></td>
                            <td>
                                <div class="row">
                                    <div class="col-auto mb-1">
                                        <a href="edit/product.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                                <path d="M15.502 1.94a.5.5 0 0 1 0 .706l-1 1a.5.5 0 0 1-.708 0l-1-1a.5.5 0 0 1 0-.707l1-1a.5.5 0 0 1 .708 0l1 1zm-1.75 2.456-1-1L4 11.293V12.5a.5.5 0 0 0 .5.5h1.207l8.045-8.045z"/>
                                                <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-7a.5.5 0 0 0-1 0v7a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5h7a.5.5 0 0 0 0-1h-7A1.5 1.5 0 0 0 1 2.5v11z"/>
                                            </svg>
                                        </a>
                                    </div>
                                    <div class="col-auto mb-1">
                                        <!-- Button trigger modal -->
                                        <button type="button" class="btn btn-sm btn-danger" title="Delete"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal-<?= $row['id']; ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5.5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6zm2 .5a.5.5 0 0 1 .5-.5.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6z"/>
                                                <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9.5A1.5 1.5 0 0 1 11.5 15h-7A1.5 1.5 0 0 1 3 13.5V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1H14a1 1 0 0 1 1 1v1zM4.118 4 4 13.5a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Bootstrap Delete Modal -->
                                    <div class="modal fade" id="deleteModal-<?= $row['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel-<?= $row['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel-<?= $row['id']; ?>">Delete Product</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-center">
                                                    Are you sure you want to delete<br>
                                                    <strong><?= htmlspecialchars($row['name']); ?></strong>?
                                                </div>
                                                <div class="modal-footer justify-content-center">
                                                    <form action="Controller/product/delete_product.php" method="POST" class="d-inline">
                                                        <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                                    </form>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No products found.</td>
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