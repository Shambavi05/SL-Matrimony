<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "db_connect.php";

// Example dynamic values (received from JS via POST)
$boyNak = $_POST['boyNak'] ?? ''; 
$girlNak = $_POST['girlNak'] ?? ''; 
$poruthams = $_POST['poruthams'] ?? [];  

// Example of Porutham calculation result (dynamically calculated)
$matchedCount = array_sum($poruthams);

// Convert Porutham array to JSON format for storage
$poruthamJson = json_encode($poruthams);

// Prepare the SQL insert statement (excluding rasi fields)
$stmt = $conn->prepare("INSERT INTO porutham_results (boy_nakshathira, girl_nakshathira, porutham_json, matched_count) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssi", $boyNak, $girlNak, $poruthamJson, $matchedCount);

// Execute the insert query
if ($stmt->execute()) {
    echo json_encode([
        "message" => "✅ Porutham Calculated and Saved Successfully",
        "poruthams" => $poruthams,
        "matched_count" => $matchedCount
    ]);
} else {
    echo json_encode(["error" => "❌ Failed to Save Porutham Result."]);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
