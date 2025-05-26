<?php 
// Include database connection
include '../../../src/db.php';

// Set page title and include CSS/JS
$title = 'Edit Product';
$cssbs = '../../css/bootstrap.css';
$jsbs  = '../../js/bootstrap.bundle.min.js';

// Define links for navigation
$dashboardlink  = '/backoffice/dashboard.php';
$productlink    = '/backoffice/product.php';
$categorylink   = '/backoffice/category.php';
$orderlink      = '/backoffice/order.php';

// Fetch categories for the select dropdown
$categories = [];
$catResult = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
if ($catResult) {
    while ($row = $catResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch product data from the id
$id         = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product    = null;
if ($id > 0) {
    $stmt = $conn->prepare("SELECT p.id, p.name, p.price, p.photo, p.stock, p.description, c.name AS category_name, c.id AS catid
                            FROM products p
                            LEFT JOIN categories c ON p.catid = c.id
                            WHERE p.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
}
include '../layout/header.php';
?>
<h2>Form Update Product</h2>
<div class="container ps-0 ms-0 pb-5">
    <div class="panel panel-scrollable">
        <?php if ($product): ?>
            <form action="../Controller/product/update_product.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= htmlspecialchars($product['id']) ?>">
                <div class="mb-3">
                    <label for="name" class="form-label">Product Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="catid" class="form-label">Category</label>
                    <select class="form-select" id="catid" name="catid" required>
                        <option value="" disabled>Select Category</option>
                        <?php if (empty($categories)) { ?>
                            <option value="">No categories available</option>
                        <?php } else { ?>
                            <?php foreach ($categories as $cat) { ?>
                                <option value="<?= $cat['id'] ?>" <?= ($product['catid'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php }
                        } ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Price</label>
                    <input type="number" class="form-control" id="price" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="stock" class="form-label">Stock</label>
                    <input type="number" class="form-control" id="stock" name="stock" value="<?= htmlspecialchars($product['stock']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($product['description']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Current Photo</label><br>
                    <?php if (!empty($product['photo'])): ?>
                        <img src="../../<?= htmlspecialchars($product['photo']) ?>" alt="Product Photo" style="max-width:120px;">
                    <?php else: ?>
                        <span>No photo</span>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="photo" class="form-label">Change Photo</label>
                    <input type="file" class="form-control" id="photo" name="photo">
                </div>
                <button type="submit" class="btn btn-primary">Update Product</button>
                <a href="../product.php" class="btn btn-secondary">Cancel</a>
            </form>
        <?php else: ?>
            <div class="alert alert-warning">Product not found.</div>
        <?php endif; ?>
    </div>
</div>
<?php
include '../layout/footer.php';
?>