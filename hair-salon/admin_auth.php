
<?php
session_start();

require_once __DIR__ . "/config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: admin_login.php");
    exit;
}

$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";

if ($username === "" || $password === "") {
    exit("Username and password are required.");
}

try {
    $stmt = $pdo->prepare(
        "SELECT id, username, password_hash, role, is_active
        FROM admins
        WHERE username = ?
         LIMIT 1"
    );

    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
    !$admin ||
    (int)$admin["is_active"] !== 1 ||
    !password_verify($password, $admin["password_hash"])
) {
    exit("Invalid username or password.");
}

    session_regenerate_id(true);

    $_SESSION["admin_id"] = $admin["id"];
    $_SESSION["admin_username"] = $admin["username"];
    $_SESSION["admin_role"] = $admin["role"];

    header("Location: admin_dashboard.php");
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit("Login failed. Please try again.");
}
