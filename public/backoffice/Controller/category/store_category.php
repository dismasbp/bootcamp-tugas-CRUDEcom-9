<?php

// Include your database connection file here
include '../../../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Popilating variables from POST data
    $input = $_POST;

    // Validate name
    $name = isset($input['name']) ? trim($input['name']) : '';
    if ($name === '') {
        $errors = "Category name is required.";
    }

    // Only proceed if there are no validation errors
    if (empty($errors)) {
        // Use prepared statements to prevent SQL injection and let MySQL handle auto-increment id
        $stmt = $conn->prepare("INSERT INTO `categories` (`name`) VALUES (?)");
        if ($stmt) {
            $stmt->bind_param("s", $name);
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
