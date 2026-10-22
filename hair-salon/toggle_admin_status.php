
<?php
session_start();

// Only POST requests are allowed
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed.");
}

// Only the owner can change admin status
if (
    !isset($_SESSION["admin_id"]) ||
    ($_SESSION["admin_role"] ?? "") !== "owner"
) {
    http_response_code(403);
    exit("Access denied. Owner only.");
}

// Validate CSRF token
if (
    empty($_SESSION["csrf_token"]) ||
    !hash_equals(
        $_SESSION["csrf_token"],
        $_POST["csrf_token"] ?? ""
    )
) {
    http_response_code(403);
    exit("Invalid CSRF token.");
}

// Validate admin ID
$adminId = filter_input(
    INPUT_POST,
    "admin_id",
    FILTER_VALIDATE_INT
);

if (!$adminId || $adminId <= 0) {
    http_response_code(400);
    exit("Invalid admin ID.");
}

require_once __DIR__ . "/config/database.php";

try {
    // Change status only for staff accounts.
    // Owner accounts cannot be disabled.
    $stmt = $pdo->prepare(
        "UPDATE admins
         SET is_active = CASE
             WHEN is_active = 1 THEN 0
             ELSE 1
         END
         WHERE id = ?
         AND role = 'admin'"
    );

    $stmt->execute([$adminId]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        exit("Staff account not found.");
    }

    header("Location: admin_management.php?status_updated=1");
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit("Unable to update account status.");
}
