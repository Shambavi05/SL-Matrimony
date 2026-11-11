<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "srilanka_matrimonial_profiles";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $exception) {
    error_log("Database connection failed: " . $exception->getMessage());
    http_response_code(503);
    header("Content-Type: text/plain; charset=utf-8");
    exit(
        "The site database is not ready. Start MySQL in XAMPP, then import " .
        "dynamic_features.sql using phpMyAdmin. Confirm the database name is " .
        "'srilanka_matrimonial_profiles' and update db_connect.php if yours differs."
    );
}
?>
