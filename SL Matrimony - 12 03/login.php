<?php
session_start();
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"] ?? "";

    // Query to fetch the user details
    $sql = "SELECT email, password, gender, name, photo_url, zodiac_sign, nakshathira FROM customers WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Email exists
        if (password_verify($password, $row["password"])) {
            session_regenerate_id(true);
            $_SESSION["user_email"]   = $row["email"];
            $_SESSION["name"]         = $row["name"];
            $_SESSION["userGender"]   = $row["gender"];
            $_SESSION["photo_url"]    = $row["photo_url"];
            $_SESSION["zodiac_sign"]  = $row["zodiac_sign"] ?? '';
            $_SESSION["nakshathira"]  = $row["nakshathira"] ?? '';

            echo "redirect_profiles";
        } else {
            echo "error: Incorrect password!";
        }
    } else {
        echo "error: User not found!";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "error: Invalid request!";
}
?>
