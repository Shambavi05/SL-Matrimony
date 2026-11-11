<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_email"])) {
    exit(json_encode(["error" => "User not logged in"]));
}

$email = $_SESSION["user_email"];
$sql = "SELECT name, email, dob, height, current_city, photo_url FROM customers WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    echo json_encode($user);
} else {
    echo json_encode(["error" => "User not found"]);
}

$stmt->close();
$conn->close();
?>
