<?php
session_start();
header("Content-Type: text/plain; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST" || empty($_SESSION["user_email"])) {
    http_response_code(401);
    exit("error: Please log in to express interest.");
}

$profileId = filter_input(INPUT_POST, "profile_id", FILTER_VALIDATE_INT);
if (!$profileId) {
    http_response_code(400);
    exit("error: Invalid profile.");
}

require "db_connect.php";
$userSql = "SELECT id, gender FROM customers WHERE email = ? LIMIT 1";
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

$userGender = strtolower($user["gender"]);
if (!in_array($userGender, ["male", "female"], true)) {
    http_response_code(403);
    $conn->close();
    exit("error: No matching profiles are available for this account.");
}

$targetSql = "SELECT id FROM customers WHERE id = ? AND LOWER(gender) = ? LIMIT 1";
$oppositeGender = $userGender === "male" ? "female" : "male";
$targetStmt = $conn->prepare($targetSql);
$targetStmt->bind_param("is", $profileId, $oppositeGender);
$targetStmt->execute();
$targetStmt->store_result();
if ($targetStmt->num_rows !== 1 || (int) $user["id"] === $profileId) {
    http_response_code(404);
    $targetStmt->close();
    $conn->close();
    exit("error: This profile is not available.");
}
$targetStmt->close();

$insertSql = "INSERT INTO interests (sender_id, recipient_id) VALUES (?, ?)";
$stmt = $conn->prepare($insertSql);
$stmt->bind_param("ii", $user["id"], $profileId);
if ($stmt->execute()) {
    echo "success";
} elseif ($stmt->errno === 1062) {
    http_response_code(409);
    echo "error: You have already expressed interest in this profile.";
} else {
    http_response_code(500);
    echo "error: Could not record your interest.";
}

$stmt->close();
$conn->close();
?>
