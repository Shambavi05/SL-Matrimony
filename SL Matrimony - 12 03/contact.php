<?php
session_start();
include 'contact_connect.php'; // Include database connection

header("Content-Type: text/plain"); // Response will be plain text

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    if (empty($name) || empty($email) || empty($message)) {
        echo "error: All fields are required!";
        exit;
    }

    $sql = "INSERT INTO submissions (name, email, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("sss", $name, $email, $message);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error: There was an error saving your message.";
        }

        $stmt->close();
    } else {
        echo "error: Failed to prepare the database query.";
    }

    $conn->close();
}
?>
