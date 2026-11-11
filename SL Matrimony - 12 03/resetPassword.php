<?php
session_start();
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $resetPassword = trim($_POST["resetPassword"]);
    $confirmPassword = trim($_POST["confirmPassword"]);

    if (empty($email) || empty($resetPassword) || empty($confirmPassword)) {
        exit("error: All fields are required!");
    }

    if ($resetPassword !== $confirmPassword) {
        exit("error: Passwords do not match!");
    }

    // Hash the new password
    $hashedPassword = password_hash($resetPassword, PASSWORD_DEFAULT);

    // Check if email exists
    $sql = "SELECT id FROM customers WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        exit("error: Email not found!");
    }

    // Update password in the database
    $updateSql = "UPDATE customers SET password = ? WHERE email = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("ss", $hashedPassword, $email);

    if ($updateStmt->execute()) {
        exit("success");
    } else {
        exit("error: Could not update password.");
    }

    $stmt->close();
    $updateStmt->close();
    $conn->close();
}
?>
