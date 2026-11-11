<?php
session_start();
file_put_contents("debug.txt", "Received JSON: " . file_get_contents('php://input') . PHP_EOL, FILE_APPEND);

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['zodiac_sign']) || !isset($_SESSION['nakshathira'])) {
    echo json_encode(["error" => "❌ Session data missing."]);
    exit;
}

$loggedRasi = $_SESSION['zodiac_sign'];
$loggedNakshathira = $_SESSION['nakshathira'];

$targetRasi = $data['zodiac'] ?? "";
$targetNakshathira = $data['nakshathira'] ?? "";

file_put_contents("debug.txt", "Session Zodiac: $loggedRasi, Target Zodiac: $targetRasi" . PHP_EOL, FILE_APPEND);

if (empty($targetRasi) || empty($targetNakshathira)) {
    echo json_encode(["error" => "❌ Invalid Input"]);
    exit;
}

function calculatePorutham($boyNak, $girlNak, $boyRasi, $girlRasi) {
    $poruthams = [
        "dina" => 0, 
        "gana" => 0, 
        "yoni" => 0, 
        "rasi" => 0, 
        "rajju" => 0,
        "vedha" => 0, 
        "mahendra" => 0, 
        "sthree_dheerga" => 0,
        "vasiya" => 0, 
        "nadi" => 0
    ];
    
// Initializing $nakshathira array
$nakshathira = [
    "Ashvini - Aswini", 
    "Bharani - Bharani", 
    "Krithika - Karthigai", 
    "Rohini - Rohini", 
    "Mrigashira - Mrigasheersham", 
    "Ardra - Thiruadhirai", 
    "Punarvasu - Punarpoosam", 
    "Pushya - Poosam", 
    "Ashlesha - Aayilyam", 
    "Magha - Magam", 
    "Purva Palguni - Pooram", 
    "Uttara Palguni - Uthiram", 
    "Hasta - Hastam", 
    "Chithra - Chithirai", 
    "Swathi - Swathi", 
    "Vishakha - Vishakam", 
    "Annuradha - Anusham", 
    "Jyeshtha - Kettai", 
    "Mula - Moolam", 
    "Purva Ashadha - Pooradam", 
    "Uttara Ashadha - Uthiradam", 
    "Shravana - Thiruvonam", 
    "Dhanishtha - Avittam", 
    "Shatabisha - Chadhayam", 
    "Purvva Bhadrapada - Pooratadhi", 
    "Uttara Bhadrapada - Uthirattathi", 
    "Revati - Revathi"
];

// Ensure $nakshathira is an array before using array_search
if (is_array($nakshathira)) {
    $boyNakIndex = array_search($boyNak, $nakshathira);
    $girlNakIndex = array_search($girlNak, $nakshathira);
} else {
    echo json_encode(["error" => "❌ Nakshathira data is missing or incorrect."]);
    exit;
}

// 1. Dina Porutham
if (isset($dinaPorutham[$boyNak]) && in_array($girlNak, $dinaPorutham[$boyNak])) {
    $poruthams['dina'] = 1;
}

// 2. Gana Porutham
if (isset($gana[$boyNak]) && isset($gana[$girlNak])) {
    if ($gana[$boyNak] === $gana[$girlNak]) {
        $poruthams['gana'] = 1;
    } elseif (($gana[$boyNak] === "Deva" && $gana[$girlNak] === "Manushya") ||
              ($gana[$boyNak] === "Manushya" && $gana[$girlNak] === "Deva")) {
        $poruthams['gana'] = 1; // Acceptable match
    }
}

// 3. Yoni Porutham
if (isset($yoni[$boyNak]) && isset($yoni[$girlNak])) {
    if ($yoni[$boyNak] === $yoni[$girlNak]) {
        $poruthams['yoni'] = 1;
    }
}

// 4. Rasi Porutham
if (isset($rasiPorutham[$boyRasi]) && in_array($girlRasi, $rasiPorutham[$boyRasi])) {
    $poruthams['rasi'] = 1;
}

// 5. Rajju Porutham
if (isset($rajju[$boyNak]) && isset($rajju[$girlNak]) &&
    $rajju[$boyNak] !== $rajju[$girlNak]) {
    $poruthams['rajju'] = 1;
}

// 6. Vedha Porutham
if (isset($vedhaDosham[$boyNak]) && !in_array($girlNak, $vedhaDosham[$boyNak])) {
    $poruthams['vedha'] = 1;
}

// 7. Vasiya Porutham
if (isset($vasiya[$boyRasi]) && in_array($girlRasi, $vasiya[$boyRasi])) {
    $poruthams['vasiya'] = 1;
}

// 8. Mahendra Porutham
$boyNakIndex = array_search($boyNak, $nakshathira);
$girlNakIndex = array_search($girlNak, $nakshathira);
if ($boyNakIndex !== false && $girlNakIndex !== false) {
    $distance = ($girlNakIndex - $boyNakIndex + 27) % 27;
    if (in_array($distance, [4, 7, 10, 13, 16, 19, 22, 25])) {
        $poruthams['mahendra'] = 1;
    }
}

// 9. Sthree Deerga Porutham
if ($girlNakIndex > $boyNakIndex) {
    $poruthams["sthree_dheerga"] = 1;
}

// 10. Nadi Porutham
if (isset($nadi[$boyNak]) && isset($nadi[$girlNak]) &&
    $nadi[$boyNak] !== $nadi[$girlNak]) {
    $poruthams['nadi'] = 1;
}

    $labels = [
        "dina" => "Dina Porutham",
        "gana" => "Gana Porutham",
        "yoni" => "Yoni Porutham",
        "rasi" => "Rasi Porutham",
        "rajju" => "Rajju Porutham",
        "vedha" => "Vedha Porutham",
        "mahendra" => "Mahendra Porutham",
        "sthree_dheerga" => "Sthree Dheerga Porutham ",
        "vasiya" => "Vasiya Porutham",
        "nadi" => "Nadi Porutham"
    ];

    // Definitions of each Porutham
    $poruthamMeanings = [
        "Dina Porutham" => "Represents health and longevity of the couple.",
        "Gana Porutham" => "Indicates temperament compatibility between the couple.",
        "Mahendra Porutham" => "Signifies children and lineage compatibility.",
        "Sthree Dheerga Porutham" => "Represents long life and happiness of the wife.",
        "Yoni Porutham" => "Physical and sexual compatibility between the couple.",
        "Rasi Porutham" => "Rasi compatibility and mental harmony.",
        "Rajju Porutham" => "Longevity and danger to the husband's life.",
        "Vedha Porutham" => "Avoids clashing Nakshatras that can lead to disharmony.",
        "Vasya Porutham" => "Attraction and mutual understanding.",
        "Nadi Porutham" => "Very crucial for progeny (having children)."
    ];
    

    $icons = [1 => "✅", 0 => "❌"];

    $dinaPorutham = [
        "Ashvini - Aswini" => ["Bharani - Bharani", "Krithika - Karthigai", "Rohini - Rohini", "Mrigashira - Mrigasheersham", "Punarvasu - Punarpoosam", "Uttara Bhadrapada - Uthirattathi", "Shravana - Thiruvonam"],
        "Bharani - Bharani" => ["Ashvini - Aswini", "Rohini - Rohini", "Ardra - Thiruadhirai"],
        "Krithika - Karthigai" => ["Ashwini - Aswini", "Mrigashira - Mrigasheersham", "Pushya - Poosam"],
        "Rohini - Rohini"=> ["Swathi - Swathi", "Dhanishtha - Avittam"],
        "Mrigashira - Mrigasheersham" => ["Shravana - Thiruvonam"],
        "Ardra - Thiruadhirai" => ["Purvva Bhadrapada - Pooratadhi"],
        "Punarvasu - Punarpoosam" => ["Krithika - Karthigai"],
        "Pushya - Poosam" => ["Bharani - Bharani"],
        "Ashlesha - Aayilyam" => ["Magha - Magam"],
        "Magha - Magam" => ["Ashlesha - Aayilyam"],
        "Purva Palguni - Pooram" => ["Uttara Bhadrapada - Uthirattathi", "Hasta - Hastam"],
        "Uttara Palguni - Uthiram" => ["Purva Ashadha - Pooradam", "Shravana - Thiruvonam"],
        "Hasta - Hastam" => ["Shatabisha - Chadhayam", "Purva Palguni - Pooram"],
        "Chitra - Chithirai" => ["Revati - Revathi"],
        "Swathi - Swathi" => ["Rohini - Rohini"],
        "Vishakha - Vishakam" => ["Uttara Ashadha - Uthiradam"],
        "Annuradha - Anusham" => ["Krithika - Karthigai"],
        "Jyeshtha - Kettai" => ["Ashwini - Aswini"],
        "Moola - Moolam" => ["Ashvini - Aswini", "Mula - Moolam"],
        "Purva Ashadha - Pooradam" => ["Uttara Palguni - Uthiram"],
        "Uttara Ashadha - Uthiradam" => ["Vishakha - Vishakam"],
        "Shravana - Thiruvonam" => ["Mrigashira - Mrigasheersham", "Uttara Phalguni - Uthiram"],
        "Dhanishtha - Avittam" => ["Rohini - Rohini"],
        "Shatabisha - Chadhayam" => ["Hasta - Hastam"],
        "Purvva Bhadrapada - Pooratadhi" => ["Ardra - Thiruadhirai"],
        "Uttara Bhadrapada - Uthirattathi" => ["Purva Palguni - Pooram", "Ardra - Thiruadhirai"],
        "Revati - Revathi" => ["Chithra - Chithirai"]
    ];

        $gana = [
            "Ashwini - Aswini" => "Deva",
            "Bharani - Bharani" => "Manushya",
            "Krithika - Karthigai" => "Rakshasa",
            "Rohini - Rohini" => "Manushya",
            "Mrigashira - Mrigasheersham" => "Deva",
            "Ardra - Thiruadhirai" => "Rakshasa",
            "Punarvasu - Punarpoosam" => "Deva",
            "Pushya - Poosam" => "Deva",
            "Ashlesha - Aayilyam" => "Rakshasa",
            "Magha - Magam" => "Rakshasa",
            "Purva Palguni - Pooram" => "Manushya",
            "Uttara Palguni - Uthiram" => "Manushya",
            "Hasta - Hastam" => "Deva",
            "Chithra - Chithirai" => "Rakshasa",
            "Swathi - Swathi" => "Deva",
            "Vishakha - Vishakam" => "Rakshasa",
            "Annuradha - Anusham" => "Deva",
            "Jyeshtha - Kettai" => "Rakshasa",
            "Mula - Moolam" => "Rakshasa",
            "Purva Ashadha - Pooradam" => "Manushya",
            "Uttara Ashadha - Uthiradam" => "Manushya",
            "Shravana - Thiruvonam" => "Deva",
            "Dhanishta - Avittam" => "Rakshasa",
            "Shatabisha - Chadhayam" => "Rakshasa",
            "Purvva Bhadrapada - Pooratadhi" => "Manushya",
            "Uttara Bhadrapada - Uthirattathi" => "Manushya",
            "Revati - Revathi" => "Deva"
    ];

    $yoni = [
        "Ashvini - Aswini" => "Horse", 
        "Bharani - Bharani" => "Elephant", 
        "Krithika - Karthigai" => "Sheep",
        "Rohini - Rohini"=> "Serpent", 
        "Mrigashira - Mrigasheersham" => "Snake", 
        "Ardra - Thiruadhirai" => "Dog",
        "Punarvasu - Punarpoosam" => "Cat", 
        "Pushya - Poosam" => "Sheep", 
        "Ashlesha - Aayilyam" => "Cat",
        "Magha - Magam" => "Rat", 
        "Purva Palguni - Pooram" => "Rat", 
        "Uttara Palguni - Uthiram" => "Cow",
        "Hasta - Hastam" => "Buffalo", 
        "Chithra - Chithirai" => "Tiger", 
        "Swathi - Swathi" => "Buffalo",
        "Vishakha - Vishakam" => "Tiger", 
        "Annuradha - Anusham" => "Deer", 
        "Jyeshtha - Kettai" => "Deer",
        "Mula - Moolam" => "Dog", 
        "Purva Ashadha - Pooradam" => "Monkey", 
        "Uttara Ashadha - Uthiradam" => "Mongoose",
        "Shravana - Thiruvonam" => "Monkey", 
        "Dhanishtha - Avittam" => "Lion", 
        "Shatabisha - Chadhayam" => "Horse",
        "Purvva Bhadrapada - Pooratadhi" => "Lion", 
        "Uttara Bhadrapada - Uthirattathi" => "Cow", 
        "Revati - Revathi" => "Elephant"
    ];

    $rasiCompatibility = [
        "Aries - Mesham" => ["Leo - Simmam", "Sagittarius - Dhanusu"],
        "Taurus - Rishabam" => ["Virgo - Kanni", "Capicorn - Magaram"],
        "Gemini - Midhunam" => ["Libra - Thulam", "Aquarius - Kumbam"],
        "Cancer - Kadagam" => ["Scorpio - Virushigam", "Pisces - Meenam"],
        "Leo - Simmam" => ["Aries - Mesham", "Sagittarius - Dhanusu" ,"Virgo - Kanni"],
        "Libra - Thulam" => ["Taurus - Rishabam", "Capicorn - Magaram"],
        "Virgo - Kanni" => ["Gemini - Midhunam", "Aquarius - Kumbam"],
        "Scorpio - Virushigam" => ["Cancer - Kadagam", "Pisces - Meenam"],
        "Sagittarius - Dhanusu" => ["Aries - Mesham", "Leo - Simmam"],
        "Capicorn - Magaram" => ["Taurus - Rishabam", "Virgo - Kanni"],
        "Aquarius - Kumbam" => ["Gemini - Midhunam", "Libra - Thulam"],
        "Pisces - Meenam" => ["Cancer - Kadagam", "Scorpio - Virushigam"]
    ];

    $rajjuGroups = [
        "Ashvini - Aswini" => "Pada", 
        "Bharani - Bharani" => "Kanta", 
        "Krithika - Karthigai" => "Siro",
        "Rohini - Rohini" => "Pada", 
        "Mrigashira - Mrigasheersham" => "Kanta", 
        "Ardra - Thiruadhirai" => "Siro",
        "Punarvasu - Punarpoosam" => "Pada",
        "Pushya - Poosam" => "Kanta", 
        "Ashlesha - Aayilyam" => "Siro",
        "Magha - Magam" => "Pada", 
        "Purva Palguni - Pooram" => "Kanta", 
        "Uttara Palguni - Uthiram" => "Siro",
        "Hasta - Hastam" => "Pada",
        "Chithra - Chithirai" => "Kanta", 
        "Swathi - Swathi" => "Siro",
        "Vishakha - Vishakam" => "Pada", 
        "Annuradha - Anusham" => "Kanta", 
        "Jyeshtha - Kettai" => "Siro",
        "Mula - Moolam" => "Pada", 
        "Purva Ashadha - Pooradam" => "Kanta", 
        "Uttara Ashadha - Uthiradam" => "Siro",
        "Shravana - Thiruvonam" => "Pada", 
        "Dhanishtha - Avittam" => "Kanta", 
        "Shatabisha - Chadhayam" => "Siro",
        "Purvva Bhadrapada - Pooratadhi" => "Pada", 
        "Uttara Bhadrapada - Uthirattathi" => "Kanta", 
        "Revati - Revathi" => "Siro"
    ];

    $vedhaPairs = [
        "Ashvini - Aswini" => ["Jyeshtha - Kettai", "Mula - Moolam"], 
        "Bharani - Bharani" => ["Revati - Revathi", "Pushya - Poosam"],
        "Krithika - Karthigai" => ["Annuradha - Anusham", "Punarvasu - Punarpoosam"], 
        "Rohini - Rohini" => ["Swathi - Swathi", "Dhanishtha - Avittam"],
        "Mrigashira - Mrigasheersham" => ["Shravana - Thiruvonam"], 
        "Ardra - Thiruadhirai" => ["Purvva Bhadrapada - Pooratadhi"],
        "Punarvasu - Punarpoosam" => ["Krithika - Karthigai"], 
        "Pushya - Poosam" => ["Bharani - Bharani"],
        "Ashlesha - Aayilyam" => ["Magha - Magam"], 
        "Magha - Magam" => ["Ashlesha - Aayilyam"],
        "Purva Palguni - Pooram" => ["Uttara Bhadrapada - Uthirattathi"],
        "Uttara Palguni - Uthiram" => ["Purva Ashadha - Pooradam"],
        "Hasta - Hastam" => ["Shatabisha - Chadhayam", "Purva Palguni - Pooram"], 
        "Chithra - Chithirai" => ["Revati - Revathi"],
        "Swathi - Swathi" => ["Revati - Revathi"], 
        "Vishakha - Vishakam" => ["Uttara Ashadha - Uthiradam"],
        "Annuradha - Anusham" => ["Krithika - Karthigai"], 
        "Jyeshtha - Kettai" => ["Ashvini - Aswini"],
        "Mula - Moolam" => ["Ashvini - Aswini"], 
        "Purva Ashadha - Pooradam" => ["Uttara Palguni - Uthiram"],
        "Uttara Ashadha - Uthiradam" => ["Vishakha - Vishakam"], 
        "Shravana - Thiruvonam" => ["Mrigashira - Mrigasheersham"],
        "Dhanishtha - Avittam" => ["Rohini - Rohini"], 
        "Shatabisha - Chadhayam" => ["Hasta - Hastam"],
        "Purvva Bhadrapada - Pooratadhi" => ["Ardra - Thiruadhirai"], 
        "Uttara Bhadrapada - Uthirattathi" => ["Purva Palguni - Pooram"],
        "Revati - Revathi" => ["Chithra - Chithirai"]
    ];

    $girlIndex = array_search($girlNak, $nakshathira);
    $boyIndex = array_search($boyNak, $nakshathira);

    // Match calculation
    if (in_array($girlNak, $dinaPorutham[$boyNak] ?? [])) $poruthams['dina'] = 1;
    if (($gana[$boyNak] ?? '') === ($gana[$girlNak] ?? '')) $poruthams['gana'] = 1;
    if (($yoni[$boyNak] ?? '') === ($yoni[$girlNak] ?? '')) $poruthams['yoni'] = 1;
    if (in_array($girlRasi, $rasiCompatibility[$boyRasi] ?? [])) $poruthams['rasi'] = 1;
    if (($rajjuGroups[$boyNak] ?? '') !== ($rajjuGroups[$girlNak] ?? '')) $poruthams['rajju'] = 1;
    if (!in_array($girlNak, $vedhaPairs[$boyNak] ?? [])) $poruthams['vedha'] = 1;
    if ($girlIndex !== false && $boyIndex !== false) {
        $distance = ($boyIndex - $girlIndex + 27) % 27;
        $mahendraDistances = [4, 7, 10, 13, 16, 19, 22, 25];
        if (in_array($distance, $mahendraDistances)) {
            $poruthams['mahendra'] = 1;
        }
               
        // Sthree Dheerga Porutham - Boy's Nakshatra should be at least 13 stars away from girl's
        if ($distance >= 13) {
            $poruthams['sthree_dheerga'] = 1;
        }
    }
     // Vasiya Porutham - Based on Rasi attraction
     $vasiyaRules = [
        "Aries - Mesham" => ["Leo - Simmam", "Scorpio - Virushigam"],
        "Taurus - Rishabam" => ["Cancer - Kadagam", "Libra - Thulam"],
        "Gemini - Midhunam" => ["Virgo - Kanni", "Scorpio - Virushigam"],
        "Cancer - Kadagam" => ["Taurus - Rishabam", "Pisces - Meenam"],
        "Leo - Simmam" => ["Aries - Mesham", "Virgo - Kanni"],
        "Libra - Thulam" => ["Gemini - Midhunam", "Leo - Simmam"],
        "Virgo - Kanni" => ["Taurus - Rishabam", "Capicorn - Magaram"],
        "Scorpio - Virushigam" => ["Aries - Mesham", "Cancer - Kadagam"],
        "Sagittarius - Dhanusu" => ["Gemini - Midhunam", "Pisces - Meenam"],
        "Capicorn - Magaram" => ["Libra - Thulam", "Aquarius - Kumbam"],
        "Aquarius - Kumbam" => ["Capicorn - Magaram", "Pisces - Meenam"],
        "Pisces - Meenam" => ["Cancer - Kadagam", "Sagittarius - Dhanusu"]
    ];

    if (in_array($girlRasi, $vasiyaRules[$boyRasi] ?? [])) {
        $poruthams['vasiya'] = 1;
    }

    // Nadi Porutham - Should NOT be same nadi
    $nadiGroups = [
        "Aadi" => ["Ashvini - Aswini", "Ardra - Thiruadhirai", "Punarvasu - Punarpoosam", "Hasta - Hastam", "Jyeshtha - Kettai", "Mula - Moolam", "Shatabisha - Chadhayam", "Purvva Bhadrapada - Pooratadhi", "Revati - Revathi"],
        "Madhya" => ["Bharani - Bharani", "Mrigashira - Mrigasheersham", "Pushya - Poosam", "Chithra - Chithirai", "Annuradha - Anusham", "Purva Ashadha - Pooradam", "Dhanishtha - Avittam", "Uttara Bhadrapada - Uthirattathi"],
        "Antya" => ["Krithika - Karthigai", "Rohini - Rohini", "Ashlesha - Aayilyam", "Magha - Magam", "Purva Palguni - Pooram", "Uttara Palguni - Uthiram", "Swathi - Swathi", "Vishakha - Vishakam", "Uttara Ashadha - Uthiradam", "Shravana - Thiruvonam"]
    ];

    $boyNadi = "";
    $girlNadi = "";

    foreach ($nadiGroups as $nadi => $nakshatrasList) {
        if (in_array($boyNak, $nakshatrasList)) {
            $boyNadi = $nadi;
        }
        if (in_array($girlNak, $nakshatrasList)) {
            $girlNadi = $nadi;
        }
    }
    
    if ($boyNadi !== "" && $girlNadi !== "" && $boyNadi !== $girlNadi) {
        $poruthams['nadi'] = 1;
    }
    
    

// Initialize detailed Poruthams array
$detailedPoruthams = [];
foreach ($poruthams as $key => $val) {
    $detailedPoruthams[$key] = [
        "match" => $val,
        "icon" => $icons[$val],
        "label" => $labels[$key]
    ];
}

// Calculate the total score
$score = array_sum($poruthams);

// Determine the verdict based on the score
$verdict = $score >= 6 ? "💖 Excellent Match ($score/10)"
         : ($score >= 5 ? "😊 Good Match ($score/10)"
         : "😐 Low Match ($score/10)");

// Repeat the star symbol based on the score
$scoreIcon = str_repeat("⭐", $score);  

// Prepare final result with explanation
$explanation = "Output Explanation:\n";

foreach ($detailedPoruthams as $key => $porutham) {
    $meaning = isset($poruthamMeanings[$porutham['label']]) ? $poruthamMeanings[$porutham['label']] : "No explanation available.";
    $explanation .= $porutham['label'] . ": " . $porutham['icon'] . " (" . ($porutham['match'] ? "Match" : "No match") . ")\n";
    $explanation .= "Explanation: " . $meaning . "\n"; 
}

// Add the total score and verdict to the explanation
$explanation .= "\nThe total score is $score out of 10. $scoreIcon\n";
$explanation .= "\nVerdict: $verdict";
return $explanation;
}

$result = calculatePorutham($loggedNakshathira, $targetNakshathira, $loggedRasi, $targetRasi);
echo $result;
