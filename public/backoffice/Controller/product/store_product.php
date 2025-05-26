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

    // Handle photo upload
    $photo = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($_FILES['image']['tmp_name']);
            if (!in_array($fileType, $allowedTypes)) {
                $errors[] = "Invalid photo file type.";
            } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) { // 2MB limit
                $errors[] = "Photo file is too large (max 2MB).";
            } else {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $newFileName = uniqid('product_', true) . '.' . $ext;
                $uploadDir = __DIR__ . '/../../../img/product/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Remove previous photo if exists (expects previous photo path in $_POST['old_photo'])
                if (!empty($_POST['image'])) {
                    $oldPhotoPath = realpath($uploadDir . basename($_POST['image']));
                    // Ensure the old photo is inside the upload directory for safety
                    if ($oldPhotoPath && strpos($oldPhotoPath, realpath($uploadDir)) === 0 && file_exists($oldPhotoPath)) {
                        unlink($oldPhotoPath);
                    }
                }

                $uploadPath = $uploadDir . $newFileName;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    // Store relative path for DB
                    $photo = '/img/product/' . $newFileName;
                } else {
                    $errors[] = "Failed to upload photo.";
                }
            }
        } else {
            $errors[] = "Error uploading photo.";
        }
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
