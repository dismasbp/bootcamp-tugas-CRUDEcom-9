<?php
// Set the title and include necessary CSS and JS files
$title = 'Dashboard';
$cssbs = '../css/bootstrap.css';
$jsbs  = '../js/bootstrap.bundle.min.js';

// Define links for navigation
$dashboardlink  = '/backoffice/dashboard.php';
$productlink    = '/backoffice/product.php';
$categorylink   = '/backoffice/category.php';
$orderlink      = '/backoffice/order.php';
include 'layout/header.php';
?>

<?php
include 'layout/footer.php';
?>