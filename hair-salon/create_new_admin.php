
<?php
session_start();

// Only accept POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed.");
}

// Only owners can create admins
if (
    !isset($_SESSION["admin_id"]) ||
    ($_SESSION["admin_role"] ?? "") !== "owner"
) {
    http_response_code(403);
    exit("Access denied. Owner only.");
}

// Validate CSRF token
$token = $_POST["csrf_token"] ?? "";
$sessionToken = $_SESSION["csrf_token"] ?? "";

if (
    !is_string($token) ||
    !is_string($sessionToken) ||
    $sessionToken === "" ||
    !hash_equals($sessionToken, $token)
) {
    http_response_code(403);
    exit("Invalid security token.");
}

require_once __DIR__ . "/config/database.php";

// Get form values
$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirm_password"] ?? "";

// Validate username
if (
    strlen($username) < 3 ||
    strlen($username) > 100 ||
    !preg_match('/^[a-zA-Z0-9_]+$/', $username)
) {
    exit("Username must be 3-100 characters and contain only letters, numbers, or underscores.");
}

// Validate password
if (strlen($password) < 12) {
    exit("Password must contain at least 12 characters.");
}

// Confirm password
if ($password !== $confirmPassword) {
    exit("Passwords do not match.");
}

try {
    // Check duplicate username
    $check = $pdo->prepare(
        "SELECT id FROM admins WHERE username = ?"
    );

    $check->execute([$username]);

    if ($check->fetch()) {
        exit("Username already exists.");
    }

    // Hash password
    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    // Create admin account
    $stmt = $pdo->prepare(
        "INSERT INTO admins (username, password_hash, role)
         VALUES (?, ?, 'admin')"
    );

    $stmt->execute([$username, $passwordHash]);

    header("Location: admin_management.php?created=1");
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit("Unable to create admin account.");
}
