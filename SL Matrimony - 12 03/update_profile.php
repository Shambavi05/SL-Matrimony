<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_email"])) {
    exit("error: User not logged in");
}

$email = $_SESSION["user_email"];
$name = trim($_POST["name"]);
$dob = trim($_POST["dob"]);
$height = trim($_POST["height"]);
$current_city = trim($_POST["current_city"]);

// Handle profile picture update
if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] === UPLOAD_ERR_OK) {
    $uploadDir = "uploads/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $imageFileType = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
    $uniqueFilename = uniqid("IMG_", true) . "." . $imageFileType;
    $targetFilePath = $uploadDir . $uniqueFilename;

    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFilePath)) {
        $updatePhoto = "UPDATE customers SET photo_url = ? WHERE email = ?";
        $photoStmt = $conn->prepare($updatePhoto);
        $photoStmt->bind_param("ss", $targetFilePath, $email);
        $photoStmt->execute();
        $photoStmt->close();
        $_SESSION["photo_url"] = $targetFilePath;
    }
}

// Update user details
$updateSql = "UPDATE customers SET name = ?, dob = ?, height = ?, current_city = ? WHERE email = ?";
$stmt = $conn->prepare($updateSql);
$stmt->bind_param("sssss", $name, $dob, $height, $current_city, $email);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error: Update failed";
}

$stmt->close();
$conn->close();
?>
