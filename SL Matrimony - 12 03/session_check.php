<?php
session_start();
header('Content-Type: application/json');

$response = [
    'loggedIn' => isset($_SESSION['user_email']),
    'gender' => $_SESSION['userGender'] ?? null,
    'name' => $_SESSION['name'] ?? null
];

echo json_encode($response);
?>
