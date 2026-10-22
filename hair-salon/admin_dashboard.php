


<?php
session_start();

// Check admin login
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit;
}

require_once __DIR__ . "/config/database.php";

// Check whether the account is still active
$stmt = $pdo->prepare(
    "SELECT id, username, role, is_active
     FROM admins
     WHERE id = ?
     LIMIT 1"
);

$stmt->execute([$_SESSION["admin_id"]]);
$currentAdmin = $stmt->fetch(PDO::FETCH_ASSOC);

// Block deleted or disabled accounts
if (
    !$currentAdmin ||
    (int)$currentAdmin["is_active"] !== 1
) {
    $_SESSION = [];
    session_destroy();

    header("Location: admin_login.php");
    exit;
}

// Keep session information updated
$_SESSION["admin_username"] = $currentAdmin["username"];
$_SESSION["admin_role"] = $currentAdmin["role"];

// Generate CSRF token for booking actions
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}


// Get customer bookings
try {
    $stmt = $pdo->query(
        "SELECT id, name, email, phone, service,
                booking_date, booking_time, message,
                status, created_at
         FROM bookings
         ORDER BY booking_date DESC, booking_time DESC"
    );

    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log($e->getMessage());
    $bookings = [];
    $bookingError = "Unable to load bookings.";
}

// Safe output
$username = htmlspecialchars(
    $_SESSION["admin_username"] ?? "Admin",
    ENT_QUOTES,
    "UTF-8"
);

$role = htmlspecialchars(
    $_SESSION["admin_role"] ?? "admin",
    ENT_QUOTES,
    "UTF-8"
);
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | Lumière Hair Salon</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f7f3ef;
            color: #46362f;
        }

        header {
            background: #46362f;
            color: white;
            padding: 20px 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h2 {
            margin: 0;
        }

        main {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .logout {
            color: white;
            text-decoration: none;
        }
    </style>
</head>

<body>


<header>
    <h2>Lumière Hair Salon</h2>

    <div style="display: flex; align-items: center; gap: 20px;">
        <span>Admin Dashboard</span>
        
<?php if (($_SESSION["admin_role"] ?? "") === "owner"): ?>
    <a href="admin_management.php"
       style="
           color: white;
           text-decoration: none;
           font-weight: bold;
       ">
        Admin Management
    </a>
<?php endif; ?>


        <form action="admin_logout.php"
              method="POST"
              style="margin: 0;">

            <input
                type="hidden"
                name="csrf_token"
                value="<?php echo htmlspecialchars(
                    $_SESSION['csrf_token'],
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>"
            >

            <button type="submit"
                    style="
                        width: auto;
                        padding: 10px 18px;
                        background: #ffffff;
                        color: #46362f;
                        border: none;
                        border-radius: 6px;
                        cursor: pointer;
                    ">
                Logout
            </button>
        </form>
    </div>
</header>



<main>

    <div class="card">
        <h2>Welcome, <?php echo $username; ?>!</h2>
        <p>Your role: <?php echo $role; ?></p>
    </div>

    <div class="card" style="margin-top: 25px;">
        <h2>Customer Appointments</h2>

        <?php if (isset($bookingError)): ?>
            <p><?php echo htmlspecialchars($bookingError); ?></p>

        <?php elseif (empty($bookings)): ?>
            <p>No bookings found.</p>

        <?php else: ?>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f0e9e3;">
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo (int)$booking["id"]; ?></td>
                                <td><?php echo htmlspecialchars($booking["name"], ENT_QUOTES, "UTF-8"); ?></td>
                                <td><?php echo htmlspecialchars($booking["phone"], ENT_QUOTES, "UTF-8"); ?></td>
                                <td><?php echo htmlspecialchars($booking["service"], ENT_QUOTES, "UTF-8"); ?></td>
                                <td><?php echo htmlspecialchars($booking["booking_date"], ENT_QUOTES, "UTF-8"); ?></td>
                                <td><?php echo htmlspecialchars($booking["booking_time"], ENT_QUOTES, "UTF-8"); ?></td>
                                <td><?php echo htmlspecialchars($booking["status"], ENT_QUOTES, "UTF-8"); ?></td>
                                
<td>
    <?php if ($booking["status"] === "pending"): ?>

        <form action="api/update_booking_status.php"
              method="POST"
              style="display: inline-block;">

            <input type="hidden"
                   name="booking_id"
                   value="<?php echo (int)$booking["id"]; ?>">

            <input type="hidden"
                   name="status"
                   value="confirmed">

            <input type="hidden"
                   name="csrf_token"
                   value="<?php echo htmlspecialchars(
                       $_SESSION["csrf_token"],
                       ENT_QUOTES,
                       "UTF-8"
                   ); ?>">

            <button type="submit">Confirm</button>
        </form>

        <form action="api/update_booking_status.php"
              method="POST"
              style="display: inline-block;">

            <input type="hidden"
                   name="booking_id"
                   value="<?php echo (int)$booking["id"]; ?>">

            <input type="hidden"
                   name="status"
                   value="cancelled">

            <input type="hidden"
                   name="csrf_token"
                   value="<?php echo htmlspecialchars(
                       $_SESSION["csrf_token"],
                       ENT_QUOTES,
                       "UTF-8"
                   ); ?>">

            <button type="submit">Cancel</button>
        </form>

    <?php else: ?>
        <span>Completed</span>
    <?php endif; ?>
</td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>
    </div>

</main>


</body>
</html>
