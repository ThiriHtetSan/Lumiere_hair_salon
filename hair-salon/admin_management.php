
<?php
session_start();

// Only logged-in owners can access this page
if (
    !isset($_SESSION["admin_id"]) ||
    ($_SESSION["admin_role"] ?? "") !== "owner"
) {
    http_response_code(403);
    exit("Access denied. Owner only.");
}

// Generate CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Connect to MySQL database
require_once __DIR__ . "/config/database.php";

// Get all admin accounts
try {
    $stmt = $pdo->query(
       "SELECT id, username, role, is_active, created_at
 FROM admins
 ORDER BY id ASC"
    );

    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log($e->getMessage());
    $admins = [];
    $adminError = "Unable to load admin accounts.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Management | Lumière Hair Salon</title>
</head>
<body>
    <h1>Admin Management</h1>
    <p>Welcome, Owner!</p>
    <p>Manage salon administrator accounts here.</p>
    
<hr>

<h2>Add New Admin</h2>

<form action="create_new_admin.php" method="POST">

    <label for="username">Username</label>
    <input
        type="text"
        id="username"
        name="username"
        minlength="3"
        maxlength="100"
        required
    >

    <br><br>

    <label for="password">Password</label>
    <input
        type="password"
        id="password"
        name="password"
        minlength="12"
        autocomplete="new-password"
        required
    >

    <br><br>

    <label for="confirm_password">Confirm Password</label>
    <input
        type="password"
        id="confirm_password"
        name="confirm_password"
        minlength="12"
        autocomplete="new-password"
        required
    >

    <br><br>

    <input
        type="hidden"
        name="csrf_token"
        value="<?php echo htmlspecialchars(
            $_SESSION['csrf_token'],
            ENT_QUOTES,
            'UTF-8'
        ); ?>"
    >

    <button type="submit">Create Admin</button>

</form>

<hr>

<h2>Admin Accounts</h2>

<?php if (isset($adminError)): ?>

    <p><?php echo htmlspecialchars($adminError, ENT_QUOTES, "UTF-8"); ?></p>

<?php elseif (empty($admins)): ?>

    <p>No admin accounts found.</p>

<?php else: ?>

    <div style="overflow-x: auto;">
        <table border="1"
               cellpadding="12"
               cellspacing="0"
               style="width: 100%; border-collapse: collapse;">

            <thead>
                <tr style="background-color: #f0e9e3;">
                    <th>ID</th>
                    <th>Username</th>
                   <th>Role</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Action</th>
                </tr>
            </thead>

            

<tbody>
    <?php foreach ($admins as $admin): ?>
        <tr>
            <td>
                <?php echo (int)$admin["id"]; ?>
            </td>

            <td>
                <?php echo htmlspecialchars(
                    $admin["username"],
                    ENT_QUOTES,
                    "UTF-8"
                ); ?>
            </td>

            <td>
                <?php echo htmlspecialchars(
                    $admin["role"],
                    ENT_QUOTES,
                    "UTF-8"
                ); ?>
            </td>

            <!-- Account Status -->
            <td>
                <?php if ((int)$admin["is_active"] === 1): ?>
                    <span style="color: green; font-weight: bold;">
                        Active
                    </span>
                <?php else: ?>
                    <span style="color: red; font-weight: bold;">
                        Disabled
                    </span>
                <?php endif; ?>
            </td>

            <td>
                <?php echo htmlspecialchars(
                    $admin["created_at"],
                    ENT_QUOTES,
                    "UTF-8"
                ); ?>
            </td>

            <!-- NEW: Disable / Enable Button -->
            <td>
                <?php if ($admin["role"] !== "owner"): ?>

                    <form action="toggle_admin_status.php"
                          method="POST">

                        <input type="hidden"
                               name="csrf_token"
                               value="<?php echo htmlspecialchars(
                                   $_SESSION["csrf_token"],
                                   ENT_QUOTES,
                                   "UTF-8"
                               ); ?>">

                        <input type="hidden"
                               name="admin_id"
                               value="<?php echo (int)$admin["id"]; ?>">

                        <button type="submit">
                            <?php echo (int)$admin["is_active"] === 1
                                ? "Disable"
                                : "Enable"; ?>
                        </button>
                    </form>

                <?php else: ?>
                    Protected
                <?php endif; ?>
            </td>

        </tr>
    <?php endforeach; ?>
</tbody>



        </table>
    </div>

<?php endif; ?>

</body>
</html>
