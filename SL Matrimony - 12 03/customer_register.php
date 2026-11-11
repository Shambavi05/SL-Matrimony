<?php  
session_start();
include "db_connect.php";

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $name = trim($_POST['name']);
    $gender = trim($_POST['gender']);
    $marital_status = trim($_POST['marital_status']);
    $dob = trim($_POST['dob']);
    $birth_time = trim($_POST['birth_time']);
    $height = trim($_POST['height']);
    $religion = trim($_POST['religion']);
    $mother_tongue = trim($_POST['mother_tongue']);
    $hobbies = trim($_POST['hobbies']);
    $food_preferences = trim($_POST['food_preferences']);
    $zodiac_sign = trim($_POST['zodiac_sign']);
    $nakshathira = trim($_POST['nakshathira']);
    $current_city = trim($_POST['current_city']);
    $residency_state = trim($_POST['residency_state']);
    $citizenship = trim($_POST['citizenship']);
    $residency_status = trim($_POST['residency_status']);
    $higher_education = trim($_POST['higher_education']);
    $employment_status = trim($_POST['employment_status']);
    $short_description = trim($_POST['short_description']);

    // Check if an image was uploaded
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        exit("error: Please upload a valid photo.");
    }

    // Validate required fields
    if (
        empty($email) || empty($password) || empty($name) || empty($gender) || empty($dob) || empty($birth_time) || empty($height) ||
        empty($religion) || empty($mother_tongue) || empty($hobbies) || empty($food_preferences) || empty($zodiac_sign) ||
        empty($nakshathira) || empty($current_city) || empty($residency_state) || empty($citizenship) ||
        empty($residency_status) || empty($higher_education) || empty($employment_status) || empty($short_description)
    ) {
        exit("error: All required fields must be filled out!");
    }

    // Check if email already exists
    $checkUser = "SELECT id FROM customers WHERE email = ?";
    $stmt = $conn->prepare($checkUser);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        exit("error: Email already registered. Please log in.");
    }
    $stmt->close();

    // Hash the password before storing it
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Handle Image Upload
    $uploadDir = "uploads/";
    if (!file_exists($uploadDir)) {
        // If upload not exist create that
        mkdir($uploadDir, 0777, true);
    }

    $imageFileType = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
    $uniqueFilename = uniqid("IMG_", true) . "." . $imageFileType;
    $targetFilePath = $uploadDir . $uniqueFilename;

    // Allow only image files
    $allowedFormats = ["jpg", "jpeg", "png", "gif", "avif"];
    if (!in_array($imageFileType, $allowedFormats)) {
        exit("error: Only JPG, JPEG, PNG, GIF & AVIF files are allowed.");
    }

    // Validate image size
    $maxFileSize = 5000000; // 5MB
    if ($_FILES['photo']['size'] > $maxFileSize) {
        exit("error: File size exceeds the maximum limit of 5MB.");
    }

    // Move uploaded file to the target directory
    if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFilePath)) {
        exit("error: Failed to upload image.");
    }

    // Prepare SQL statement
    $stmt = $conn->prepare("INSERT INTO customers 
    (email, password, name, gender, marital_status, dob, birth_time, height, religion, mother_tongue, hobbies, food_preferences, zodiac_sign, nakshathira, current_city, residency_state, citizenship, residency_status, higher_education, employment_status, short_description, photo_url) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");


    if (!$stmt) {
        exit("error: SQL Prepare failed - " . $conn->error);
    }

    // Bind parameters to the SQL query
    $stmt->bind_param(
        "ssssssssssssssssssssss", 
        $email, $hashedPassword, $name, $gender, $marital_status, $dob, $birth_time, $height, 
        $religion, $mother_tongue, $hobbies, $food_preferences, $zodiac_sign, $nakshathira, 
        $current_city, $residency_state, $citizenship, $residency_status, $higher_education, 
        $employment_status, $short_description, $targetFilePath
    );
    

    // Execute the SQL query
    if ($stmt->execute()) {
        $_SESSION["userRegistered"] = true;
        $_SESSION["userEmail"] = $email; 
        $_SESSION["userGender"] = $gender;
        exit("success");
    } else {
        exit("error: Registration failed. Please try again. " . $stmt->error);
    }
    
    // Close statement and database connection
    $stmt->close();
    $conn->close();
}
?>