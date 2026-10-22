
<?php
session_start();

// 1. Check admin login
if (!isset($_SESSION["admin_id"])) {
    http_response_code(401);
    exit("Unauthorized access.");
}

// 2. Allow POST requests only
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed.");
}

// 3. Validate CSRF token
$submittedToken = $_POST["csrf_token"] ?? "";
$sessionToken = $_SESSION["csrf_token"] ?? "";

if (
    !is_string($submittedToken) ||
    !is_string($sessionToken) ||
    $sessionToken === "" ||
    !hash_equals($sessionToken, $submittedToken)
) {
    http_response_code(403);
    exit("Invalid security token.");
}

// 4. Database connection
require_once __DIR__ . "/../config/database.php";

// 5. Get booking information
$bookingId = filter_input(
    INPUT_POST,
    "booking_id",
    FILTER_VALIDATE_INT
);

$status = $_POST["status"] ?? "";

$allowedStatuses = ["confirmed", "cancelled"];

if (
    !$bookingId ||
    !is_string($status) ||
    !in_array($status, $allowedStatuses, true)
) {
    http_response_code(400);
    exit("Invalid booking information.");
}

try {

    // 6. Check if admin account is active
    $stmt = $pdo->prepare(
        "SELECT is_active
         FROM admins
         WHERE id = ?
         LIMIT 1"
    );

    $stmt->execute([$_SESSION["admin_id"]]);

    $currentAdmin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        !$currentAdmin ||
        (int)$currentAdmin["is_active"] !== 1
    ) {
        $_SESSION = [];
        session_destroy();

        http_response_code(403);
        exit("Account disabled or access revoked.");
    }

    // 7. Update pending booking only
    $stmt = $pdo->prepare(
        "UPDATE bookings
         SET status = ?
         WHERE id = ?
           AND status = 'pending'"
    );

    $stmt->execute([$status, $bookingId]);

    // 8. Redirect to admin dashboard
    header("Location: ../admin_dashboard.php");
    exit;

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);
    exit("Unable to update booking status.");
}
