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

    // Handle photo upload
    $photo = $data['photo']; // Default to existing photo

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Error uploading photo.";
        } elseif (!in_array($_FILES['photo']['type'], $allowedTypes)) {
            $errors[] = "Invalid photo type. Only JPG, PNG, and GIF allowed.";
        } elseif ($_FILES['photo']['size'] > $maxSize) {
            $errors[] = "Photo size exceeds 2MB.";
        } else {
            // Delete old photo if exists and not empty
            if (!empty($photo) && file_exists(__DIR__ . '/../../../' . $photo)) {
                unlink(__DIR__ . '/../../../' . $photo);
            }

            // Generate unique filename
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $newFileName = uniqid('product_', true) . '.' . $ext;
            $uploadDir = __DIR__ . '/../../../img/product/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $uploadPath = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
                $photo = '/img/product/' . $newFileName;
            } else {
                $errors[] = "Failed to save uploaded photo.";
            }
        }
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
