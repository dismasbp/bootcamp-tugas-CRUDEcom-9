<?php
// Set page title and include CSS/JS
$title = 'Create Category';
$cssbs = '../../css/bootstrap.css';
$jsbs  = '../../js/bootstrap.bundle.min.js';

// Define links for navigation
$dashboardlink  = '/backoffice/dashboard.php';
$productlink    = '/backoffice/product.php';
$categorylink   = '/backoffice/category.php';
$orderlink      = '/backoffice/order.php';
include '../layout/header.php';
?>
<h2>Form Create Category</h2>
<div class="container ps-0 ms-0 pb-5">
    <div class="panel panel-scrollable">
        <form action="../Controller/category/store_category.php" method="POST" enctype="multipart/form-data" class="mt-4">
            <div class="mb-3">
                <label for="name" class="form-label">Category Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Category</button>
        </form>
    </div>
</div>
<?php
include '../layout/footer.php';
?>