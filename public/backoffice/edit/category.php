<?php
// Include database connection
include '../../../src/db.php';

// Set page title and include CSS/JS
$title = 'Edit Category';
$cssbs = '../../css/bootstrap.css';
$jsbs  = '../../js/bootstrap.bundle.min.js';

// Define links for navigation
$dashboardlink  = '/backoffice/dashboard.php';
$productlink    = '/backoffice/product.php';
$categorylink   = '/backoffice/category.php';
$orderlink      = '/backoffice/order.php';

// Fetch category data from the id
$id         = isset($_GET['id']) ? intval($_GET['id']) : 0;
$category   = null;
if ($id > 0) {
    $stmt = $conn->prepare("SELECT id, name FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $category = $result->fetch_assoc();
    $stmt->close();
}
include '../layout/header.php';
?>
<h2>Form Update Category</h2>
<div class="container ps-0 ms-0 pb-5">
    <div class="panel panel-scrollable">
        <?php if ($category): ?>
            <form action="../Controller/category/update_category.php" method="post">
                <input type="hidden" name="id" value="<?= htmlspecialchars($category['id']) ?>">
                <div class="mb-3">
                    <label for="name" class="form-label">Category Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($category['name']) ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Category</button>
                <a href="../category.php" class="btn btn-secondary">Cancel</a>
            </form>
        <?php else: ?>
            <div class="alert alert-warning">Category not found.</div>
        <?php endif; ?>
    </div>
</div>
<?php
include '../layout/footer.php';
?>