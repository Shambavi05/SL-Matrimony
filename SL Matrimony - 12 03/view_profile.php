<?php
session_start();

if (empty($_SESSION["user_email"]) || empty($_SESSION["userGender"])) {
    http_response_code(401);
    $pageTitle = "Login required";
    $message = "Please log in to view member profiles.";
} elseif (!in_array(strtolower($_SESSION["userGender"]), ["male", "female"], true)) {
    http_response_code(403);
    $pageTitle = "Profile unavailable";
    $message = "No matching profiles are available for this account.";
} else {
    $profileId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
    if (!$profileId) {
        http_response_code(400);
        $pageTitle = "Profile unavailable";
        $message = "The requested profile could not be found.";
    } else {
        require "db_connect.php";
        $oppositeGender = strtolower($_SESSION["userGender"]) === "male" ? "female" : "male";
        $sql = "SELECT name, gender, dob, birth_time, height, religion, mother_tongue,
                       hobbies, food_preferences, zodiac_sign, nakshathira, current_city,
                       residency_state, citizenship, residency_status, higher_education,
                       employment_status, short_description, photo_url
                FROM customers
                WHERE id = ? AND LOWER(gender) = ?
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $profileId, $oppositeGender);
        $stmt->execute();
        $result = $stmt->get_result();
        $profile = $result->fetch_assoc();
        $stmt->close();
        $conn->close();

        if (!$profile) {
            http_response_code(404);
            $pageTitle = "Profile unavailable";
            $message = "This profile is not available.";
        } else {
            $pageTitle = $profile["name"];
            $message = "";
        }
    }
}

function escapeProfileValue($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escapeProfileValue($pageTitle) ?> | Astro Affinity Matrimony</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <style>
        body { background: #fff3e4; color: #34212a; }
        .profile { max-width: 900px; margin: 3rem auto; padding: 2rem; background: #fff; border-radius: 1rem; box-shadow: 0 8px 30px #4d293d20; }
        .profile-photo { width: 220px; height: 260px; object-fit: cover; border-radius: .75rem; }
        .profile-facts { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: .75rem 1.5rem; }
    </style>
</head>
<body>
    <main class="profile">
        <a href="profile.html" class="btn btn-outline-secondary mb-4">Back to profiles</a>
        <?php if ($message !== ""): ?>
            <h1><?= escapeProfileValue($pageTitle) ?></h1>
            <p><?= escapeProfileValue($message) ?></p>
            <?php if (http_response_code() === 401): ?>
                <a href="index.html">Return to the home page to log in</a>
            <?php endif; ?>
        <?php else: ?>
            <div class="row g-4 align-items-start">
                <div class="col-md-4 text-center">
                    <img class="profile-photo" src="<?= escapeProfileValue($profile["photo_url"] ?: "default.jpg") ?>" alt="<?= escapeProfileValue($profile["name"]) ?>">
                    <h1 class="h3 mt-3"><?= escapeProfileValue($profile["name"]) ?></h1>
                    <p><?= escapeProfileValue($profile["current_city"]) ?></p>
                    <button class="btn btn-danger" id="sendInterest" data-profile-id="<?= (int) $profileId ?>">Express interest</button>
                </div>
                <div class="col-md-8">
                    <h2 class="h4">About</h2>
                    <p><?= nl2br(escapeProfileValue($profile["short_description"])) ?></p>
                    <h2 class="h4 mt-4">Profile details</h2>
                    <div class="profile-facts">
                        <?php foreach ([
                            "Gender" => "gender", "Date of birth" => "dob", "Birth time" => "birth_time",
                            "Height" => "height", "Religion" => "religion", "Mother tongue" => "mother_tongue",
                            "Hobbies" => "hobbies", "Food preference" => "food_preferences",
                            "Zodiac sign" => "zodiac_sign", "Nakshathira" => "nakshathira",
                            "Current city" => "current_city", "State" => "residency_state",
                            "Citizenship" => "citizenship", "Residency status" => "residency_status",
                            "Education" => "higher_education", "Employment" => "employment_status"
                        ] as $label => $field): ?>
                            <?php if (!empty($profile[$field])): ?>
                                <div><strong><?= escapeProfileValue($label) ?>:</strong> <?= escapeProfileValue($profile[$field]) ?></div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const interestButton = document.getElementById("sendInterest");
        if (interestButton) {
            interestButton.addEventListener("click", async () => {
                interestButton.disabled = true;
                try {
                    const body = new FormData();
                    body.append("profile_id", interestButton.dataset.profileId);
                    const response = await fetch("send_interest.php", { method: "POST", body });
                    const result = await response.text();
                    if (!response.ok || result.trim() !== "success") {
                        throw new Error(result.replace(/^error:\s*/i, "") || "Could not send your interest.");
                    }
                    await Swal.fire("Interest sent", "Your interest has been recorded.", "success");
                } catch (error) {
                    await Swal.fire("Unable to send interest", error.message, "error");
                } finally {
                    interestButton.disabled = false;
                }
            });
        }
    </script>
</body>
</html>
