<?php
session_start();
header("Content-Type: text/plain; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST" || empty($_SESSION["user_email"])) {
    http_response_code(401);
    exit("error: Please log in before choosing a package.");
}

$packages = [
    "Free" => 0,
    "Silver" => 20000,
    "Gold" => 30000,
    "Platinum" => 60000
];
$packageName = trim($_POST["package"] ?? "");
if (!array_key_exists($packageName, $packages)) {
    http_response_code(400);
    exit("error: Invalid package.");
}

require "db_connect.php";
$userSql = "SELECT id FROM customers WHERE email = ? LIMIT 1";
$userStmt = $conn->prepare($userSql);
$userStmt->bind_param("s", $_SESSION["user_email"]);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();
$userStmt->close();

if (!$user) {
    http_response_code(401);
    $conn->close();
    exit("error: Please log in again.");
}

$status = $packages[$packageName] === 0 ? "active" : "pending_payment";
$amount = $packages[$packageName];
$sql = "INSERT INTO subscriptions (customer_id, package_name, amount, status)
        VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isds", $user["id"], $packageName, $amount, $status);
if ($stmt->execute()) {
    echo $status === "active"
        ? "success: Your Free package is active."
        : "success: Your request is saved as pending payment. Contact our team to complete payment.";
} else {
    http_response_code(500);
    echo "error: Could not save your package request.";
}

$stmt->close();
$conn->close();
?>
