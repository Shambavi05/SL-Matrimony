<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

if (empty($_SESSION["user_email"]) || empty($_SESSION["userGender"])) {
    http_response_code(401);
    echo json_encode(["error" => "Please log in to view member profiles."]);
    exit;
}

$gender = strtolower(trim($_SESSION["userGender"]));
$oppositeGender = $gender === "male" ? "female" : ($gender === "female" ? "male" : null);
if ($oppositeGender === null) {
    echo json_encode(["profiles" => []]);
    exit;
}

require "db_connect.php";
$sql = "SELECT id, name, gender, current_city, height, religion, higher_education,
               employment_status, short_description, photo_url, zodiac_sign, nakshathira
        FROM customers
        WHERE LOWER(gender) = ? AND email <> ?
        ORDER BY id DESC
        LIMIT 100";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Could not load profiles."]);
    $conn->close();
    exit;
}

$stmt->bind_param("ss", $oppositeGender, $_SESSION["user_email"]);
$stmt->execute();
$result = $stmt->get_result();
$profiles = [];
while ($row = $result->fetch_assoc()) {
    $profiles[] = $row;
}

echo json_encode(["profiles" => $profiles]);
$stmt->close();
$conn->close();
?>
