<?php
// Include your database connection file here
include '../../../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize the product ID
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        echo "<p style='color:red;'>Invalid product ID.</p>";
        exit;
    }

    // Check if product exists
    $query  = "SELECT * FROM `products` WHERE `id` = ?";
    $stmt   = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data   = $result->fetch_assoc();
    $stmt->close();

    if (!$data) {
        echo "<p style='color:red;'>Product not found.</p>";
        exit;
    }

    // Delete the product
    $stmt = $conn->prepare("DELETE FROM `products` WHERE `id` = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            header("Location: /backoffice/product.php?deleted=1");
            exit;
        } else {
            echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
    }
}
?>
