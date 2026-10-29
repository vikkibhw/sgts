
<?php
session_start();
require_once __DIR__ . "/../config/db.php";

// Only admin can access this page
if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? "") !== "admin") {
    header("Location: ../login.php");
    exit;
}

function getCount(PDO $pdo, string $sql): int {
    return (int) $pdo->query($sql)->fetchColumn();
}

$totalUsers = getCount($pdo, "SELECT COUNT(*) FROM users WHERE role = 'user'");
$totalVehicles = getCount($pdo, "SELECT COUNT(*) FROM vehicles");
$activeVehicles = getCount($pdo, "SELECT COUNT(*) FROM vehicles WHERE status = 'active'");
$totalBookings = getCount($pdo, "SELECT COUNT(*) FROM bookings");
$pendingBookings = getCount($pdo, "SELECT COUNT(*) FROM bookings WHERE booking_status = 'pending'");
$completedBookings = getCount($pdo, "SELECT COUNT(*) FROM bookings WHERE booking_status = 'delivered'");
$unpaidBookings = getCount($pdo, "SELECT COUNT(*) FROM bookings WHERE payment_status = 'unpaid'");

$recentBookings = $pdo->query(
    "SELECT b.booking_code, u.name AS customer_name,
            v.vehicle_name, b.estimated_price,
            b.booking_status, b.payment_status, b.created_at
     FROM bookings b
     LEFT JOIN users u ON b.user_id = u.id
     LEFT JOIN vehicles v ON b.vehicle_id = v.id
     ORDER BY b.id DESC LIMIT 8"
)->fetchAll();

function h($value): string {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard | SGTS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box}
body{margin:0;background:#f3f6fb;font-family:Inter,Arial,sans-serif;color:#17243a}
.topbar{background:#0b203b;color:white;padding:18px 5%;display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap}
.brand{font-size:22px;font-weight:800}.brand i{color:#ff8a24;margin-right:8px}
.top-right{display:flex;align-items:center;gap:16px;font-size:13px}
.logout{color:white;text-decoration:none;background:#e87516;padding:10px 15px;border-radius:8px;font-weight:700}
.container{max-width:1250px;margin:30px auto;padding:0 22px}
.heading{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:24px}
.heading h1{margin:0 0 7px;font-size:27px}.muted{color:#718096;font-size:13px}
.stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:17px;margin-bottom:28px}
.stat{background:white;border-radius:13px;padding:22px;box-shadow:0 4px 18px #17243a0a;border:1px solid #e8edf4}
.stat-top{display:flex;justify-content:space-between;align-items:center;color:#718096;font-size:13px;gap:8px}
.stat-icon{width:39px;height:39px;border-radius:10px;background:#fff0e5;color:#e87516;display:flex;align-items:center;justify-content:center;font-size:17px}
.stat h2{font-size:29px;margin:15px 0 5px}
.section-title{font-size:19px;margin:0 0 16px}
.modules{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:17px;margin-bottom:30px}
.module{display:block;background:white;padding:22px;border-radius:13px;text-decoration:none;color:#17243a;border:1px solid #e8edf4;box-shadow:0 4px 18px #17243a08;transition:.2s}
.module:hover{transform:translateY(-3px);border-color:#ffb26f}
.module i{font-size:23px;color:#e87516;margin-bottom:15px}
.module h3{margin:0 0 7px;font-size:16px}.module p{margin:0;color:#718096;font-size:13px;line-height:1.6}
.table-wrap{background:white;border-radius:13px;border:1px solid #e8edf4;overflow-x:auto}
table{border-collapse:collapse;width:100%;min-width:780px}
th,td{text-align:left;padding:15px 17px;border-bottom:1px solid #edf0f5;font-size:12px}
th{background:#f8fafc;color:#526176;font-size:11px;text-transform:uppercase;letter-spacing:.4px}
tr:last-child td{border-bottom:none}
.pill{display:inline-block;padding:5px 8px;border-radius:20px;background:#eef2f7;font-size:11px;font-weight:700;text-transform:capitalize}
.empty{text-align:center;padding:25px;color:#718096}
@media(max-width:900px){.stats{grid-template-columns:repeat(2,minmax(0,1fr))}.modules{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:560px){.stats,.modules{grid-template-columns:1fr}.container{padding:0 14px}.heading h1{font-size:23px}}
</style>
</head>
<body>
<header class="topbar">
    <div class="brand"><i class="fa-solid fa-truck-fast"></i> SGTS <span style="font-size:12px;font-weight:500;color:#cbd5e1">ADMIN</span></div>
    <div class="top-right">
        <span><i class="fa-solid fa-user-shield"></i> <?= h($_SESSION["user_name"] ?? "Administrator") ?></span>
        <a class="logout" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
</header>

<main class="container">
    <div class="heading">
        <div>
            <h1>Admin Dashboard</h1>
            <div class="muted">Manage your transport website from one place.</div>
        </div>
        <div class="muted"><i class="fa-regular fa-calendar"></i> <?= date("d M Y") ?></div>
    </div>

    <section class="stats">
        <div class="stat"><div class="stat-top">Total Users <span class="stat-icon"><i class="fa-solid fa-users"></i></span></div><h2><?= $totalUsers ?></h2><div class="muted">Registered customers</div></div>
        <div class="stat"><div class="stat-top">Total Vehicles <span class="stat-icon"><i class="fa-solid fa-truck"></i></span></div><h2><?= $totalVehicles ?></h2><div class="muted"><?= $activeVehicles ?> active vehicles</div></div>
        <div class="stat"><div class="stat-top">Total Bookings <span class="stat-icon"><i class="fa-solid fa-box"></i></span></div><h2><?= $totalBookings ?></h2><div class="muted"><?= $pendingBookings ?> pending bookings</div></div>
        <div class="stat"><div class="stat-top">Delivered Bookings <span class="stat-icon"><i class="fa-solid fa-circle-check"></i></span></div><h2><?= $completedBookings ?></h2><div class="muted"><?= $unpaidBookings ?> unpaid bookings</div></div>
    </section>

    <h2 class="section-title">Management Modules</h2>
    <section class="modules">
        <a class="module" href="vehicles.php"><i class="fa-solid fa-truck"></i><h3>Manage Vehicles</h3><p>Add, edit, deactivate vehicles and update transport prices.</p></a>
        <a class="module" href="users.php"><i class="fa-solid fa-users"></i><h3>Manage Users</h3><p>View registered customer information.</p></a>
        <a class="module" href="bookings.php"><i class="fa-solid fa-clipboard-list"></i><h3>Manage Bookings</h3><p>Review bookings and update delivery status.</p></a>
        <a class="module" href="payments.php"><i class="fa-solid fa-money-bill-wave"></i><h3>Manage Payments</h3><p>Review payment methods and payment status.</p></a>
        <a class="module" href="feedback.php"><i class="fa-solid fa-star"></i><h3>Customer Feedback</h3><p>View customer ratings and feedback messages.</p></a>
        <a class="module" href="../index.php"><i class="fa-solid fa-globe"></i><h3>View Website</h3><p>Open the public SGTS website.</p></a>
    </section>

    <h2 class="section-title">Recent Bookings</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Booking Code</th><th>Customer</th><th>Vehicle</th><th>Estimated Price</th><th>Booking Status</th><th>Payment</th></tr></thead>
            <tbody>
            <?php if (!$recentBookings): ?>
                <tr><td colspan="6" class="empty">No bookings have been created yet.</td></tr>
            <?php else: ?>
                <?php foreach ($recentBookings as $booking): ?>
                <tr>
                    <td><?= h($booking["booking_code"]) ?></td>
                    <td><?= h($booking["customer_name"]) ?></td>
                    <td><?= h($booking["vehicle_name"]) ?></td>
                    <td>₹<?= number_format((float)$booking["estimated_price"], 2) ?></td>
                    <td><span class="pill"><?= h(str_replace("_", " ", $booking["booking_status"])) ?></span></td>
                    <td><span class="pill"><?= h($booking["payment_status"]) ?></span></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
