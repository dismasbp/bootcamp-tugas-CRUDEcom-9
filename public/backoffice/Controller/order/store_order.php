<?php

// Include your database connection file here
include '../../../../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Popilating variables from POST data
    $input = $_POST;

    // Ensure user_id and total are provided in the POST data
    $user_id    = isset($input['userid']) ? intval($input['userid']) : 0;
    $total      = isset($input['value']) ? floatval($input['value']) : 0.0;

    // Insert order into `orders` table
    $order_stmt = $conn->prepare("INSERT INTO `orders` (`userid`, `total`) VALUES (?, ?)");
    if ($order_stmt && $order_stmt->bind_param("id", $user_id, $total) && $order_stmt->execute()) {
        // Get the last inserted order ID
        $order_id = $conn->insert_id;
        $order_stmt->close();
    } else {
        echo "<p style='color:red;'>Failed to create order: " . $conn->error . "</p>";
        exit;
    }
    
    if (!empty($input['qty'])) {
        foreach ($input['qty'] as $productid => $qty) {
            // Validate product ID and quantity
            $productid  = intval($productid);
            $qty        = intval($qty);

            if ($qty <= 0) {
                echo "<p style='color:red;'>Invalid quantity for product ID $productid.</p>";
                continue;
            }

            // Use prepared statements to prevent SQL injection
            $stmt = $conn->prepare("INSERT INTO `order_details` (`orderid`, `productid`, `qty`) VALUES (?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("iii", $order_id, $productid, $qty);
                if ($stmt->execute()) {
                    header("Location: /");
                } else {
                    echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
                }
                $stmt->close();
            } else {
                echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
            }
        }
    } else {
        echo "<p style='color:red;'>No products selected for the order.</p>";
    }
    exit;
}
?>
