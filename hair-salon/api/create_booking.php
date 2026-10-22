
<?php

header("Content-Type: application/json; charset=UTF-8");

// ========================================
// 1. Allow POST Requests Only
// ========================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Method not allowed."
    ]);

    exit;
}

// ========================================
// 2. Database Connection
// ========================================

require_once __DIR__ . "/../config/database.php";

// ========================================
// 3. Receive Booking Data
// ========================================

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$service = trim($_POST["service"] ?? "");
$date = trim($_POST["date"] ?? "");
$time = trim($_POST["time"] ?? "");
$message = trim($_POST["message"] ?? "");

// ========================================
// 4. Required Fields Validation
// ========================================

if (
    $name === "" ||
    $email === "" ||
    $phone === "" ||
    $service === "" ||
    $date === "" ||
    $time === ""
) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Please fill in all required fields."
    ]);

    exit;
}

// ========================================
// 5. Email Validation
// ========================================

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid email address."
    ]);

    exit;
}

// ========================================
// 6. Input Length Validation
// ========================================

if (
    mb_strlen($name) > 100 ||
    strlen($email) > 254 ||
    mb_strlen($phone) > 30 ||
    mb_strlen($service) > 100 ||
    mb_strlen($message) > 1000
) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "One or more fields exceed the allowed length."
    ]);

    exit;
}

// ========================================
// 7. Phone Number Validation
// ========================================

if (!preg_match('/^[0-9+\-\s()]{7,30}$/', $phone)) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid phone number."
    ]);

    exit;
}

// ========================================
// 8. Date and Time Validation
// ========================================

$dateObject = DateTimeImmutable::createFromFormat(
    "!Y-m-d",
    $date
);

$timeObject = DateTimeImmutable::createFromFormat(
    "!H:i",
    $time
);

if (
    !$dateObject ||
    $dateObject->format("Y-m-d") !== $date ||
    !$timeObject ||
    $timeObject->format("H:i") !== $time
) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid booking date or time."
    ]);

    exit;
}

// ========================================
// 9. Past Date Prevention
// ========================================

$timezone = new DateTimeZone("Asia/Bangkok");

$bookingDateTime = DateTimeImmutable::createFromFormat(
    "!Y-m-d H:i",
    $date . " " . $time,
    $timezone
);

$now = new DateTimeImmutable("now", $timezone);

if (
    !$bookingDateTime ||
    $bookingDateTime < $now
) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Please select a future booking date and time."
    ]);

    exit;
}

// ========================================
// 10. Save Booking to Database
// ========================================

try {

    // Basic duplicate booking prevention:
    // Reject the same email, service, date and
    // time if an active booking already exists.

    $checkStmt = $pdo->prepare(
        "SELECT id
         FROM bookings
         WHERE email = ?
           AND service = ?
           AND booking_date = ?
           AND booking_time = ?
           AND status IN ('pending', 'confirmed')
         LIMIT 1"
    );

    $checkStmt->execute([
        $email,
        $service,
        $date,
        $time
    ]);

    if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
        http_response_code(409);

        echo json_encode([
            "success" => false,
            "message" => "You already have a booking for this service at the selected date and time."
        ]);

        exit;
    }

    // Insert new booking
    $sql = "INSERT INTO bookings
            (
                name,
                email,
                phone,
                service,
                booking_date,
                booking_time,
                message
            )
            VALUES
            (
                :name,
                :email,
                :phone,
                :service,
                :booking_date,
                :booking_time,
                :message
            )";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":name" => $name,
        ":email" => $email,
        ":phone" => $phone,
        ":service" => $service,
        ":booking_date" => $date,
        ":booking_time" => $time,
        ":message" => $message === "" ? null : $message
    ]);

    http_response_code(201);

    echo json_encode([
        "success" => true,
        "message" => "Booking created successfully!",
        "booking_id" => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to save booking."
    ]);
}
