<?php

// Include your database connection file here
include '../../../../src/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate catid
    if (!isset($_POST['catid']) || !is_numeric($_POST['catid']) || intval($_POST['catid']) <= 0) {
        $errors[] = "Invalid category ID.";
    }
    $catid = isset($_POST['catid']) ? intval($_POST['catid']) : 0;

    // Validate name
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    if ($name === '') {
        $errors[] = "Product name is required.";
    }

    // Validate price
    if (!isset($_POST['price']) || !is_numeric($_POST['price']) || floatval($_POST['price']) < 0) {
        $errors[] = "Invalid price.";
    }
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;

    // Validate photo (optional, but check if string)
    $photo = isset($_POST['photo']) ? trim($_POST['photo']) : '';
    if ($photo !== '' && strlen($photo) > 255) {
        $errors[] = "Photo URL is too long.";
    }

    // Validate description (optional)
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    if (strlen($description) > 1000) {
        $errors[] = "Description is too long.";
    }

    // Validate stock
    if (!isset($_POST['stock']) || !is_numeric($_POST['stock']) || intval($_POST['stock']) < 0) {
        $errors[] = "Invalid stock value.";
    }
    $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;

    // Only proceed if there are no validation errors
    if (empty($errors)) {
        // Use prepared statements to prevent SQL injection and let MySQL handle auto-increment id
        $stmt = $conn->prepare("INSERT INTO `products` (`catid`, `name`, `price`, `photo`, `description`, `stock`) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("isdssi", $catid, $name, $price, $photo, $description, $stock);
            if ($stmt->execute()) {
                header("Location: /backoffice/product.php?success=1");
                exit;
            } else {
                echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
            }
            $stmt->close();
        } else {
            echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
        }
    } else {
        // Display validation errors
        foreach ($errors as $error) {
            echo "<p style='color:red;'>$error</p>";
        }
    }
}
?>
