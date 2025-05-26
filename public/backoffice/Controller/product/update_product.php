<?php
// Include your database connection file here
include '../../../../src/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Populating variables from GET data
    $input  = $_POST;

    // Validate and sanitize the product ID
    $id         = $input['id'];
    $query      = "SELECT * FROM `products` WHERE `id` = $id";
    $result     = $conn->query($query);
    $data       = $result->fetch_assoc();
    if (!$data) {
        die("Product not found.");
    }

    // Validate catid
    if (!isset($input['catid']) || !is_numeric($input['catid']) || intval($input['catid']) <= 0) {
        $errors[] = "Invalid category ID.";
    }
    $catid = isset($input['catid']) ? intval($input['catid']) : 0;

    // Validate name
    $name = isset($input['name']) ? trim($input['name']) : '';
    if ($name === '') {
        $errors[] = "Product name is required.";
    }

    // Validate price
    if (!isset($input['price']) || !is_numeric($input['price']) || floatval($input['price']) < 0) {
        $errors[] = "Invalid price.";
    }
    $price = isset($input['price']) ? floatval($input['price']) : 0;

    // Validate photo (optional, but check if string)
    $photo = isset($input['photo']) ? trim($input['photo']) : '';
    if ($photo !== '' && strlen($photo) > 255) {
        $errors[] = "Photo URL is too long.";
    }

    // Validate description (optional)
    $description = isset($input['description']) ? trim($input['description']) : '';
    if (strlen($description) > 1000) {
        $errors[] = "Description is too long.";
    }

    // Validate stock
    if (!isset($input['stock']) || !is_numeric($input['stock']) || intval($input['stock']) < 0) {
        $errors[] = "Invalid stock value.";
    }
    $stock = isset($input['stock']) ? intval($input['stock']) : 0;

    // Only proceed if there are no validation errors
    if (empty($errors)) {
        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("UPDATE `products` SET `catid` = ?, `name` = ?, `price` = ?, `photo` = ?, `description` = ?, `stock` = ? WHERE `id` = ?");
        if ($stmt) {
            $stmt->bind_param("isdssii", $catid, $name, $price, $photo, $description, $stock, $id);
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
