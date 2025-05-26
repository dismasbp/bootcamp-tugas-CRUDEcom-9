<?php
// Include your database connection file here
include '../../../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Populating variables from GET data
    $input  = $_POST;

    // Validate and sanitize the category ID
    $id         = $input['id'];
    $query      = "SELECT * FROM `categories` WHERE `id` = $id";
    $result     = $conn->query($query);
    $data       = $result->fetch_assoc();
    if (!$data) {
        die("Category not found.");
    }

    // Validate name
    $name = isset($input['name']) ? trim($input['name']) : '';
    if ($name === '') {
        $errors = "Category name is required.";
    }

    // Only proceed if there are no validation errors
    if (empty($errors)) {
        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("UPDATE `categories` SET `name` = ?  WHERE `id` = ?");
        if ($stmt) {
            $stmt->bind_param("si", $name, $id);
            if ($stmt->execute()) {
                header("Location: /backoffice/category.php?success=1");
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
        echo "<p style='color:red;'>$errors</p>";
    }
}
?>
