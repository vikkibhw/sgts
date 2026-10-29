
<?php
session_start();
require_once __DIR__ . "/config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

$stmt = $pdo->prepare(
    "SELECT b.booking_code, b.shifting_type,
            b.pickup_address, b.delivery_address,
            b.shifting_date, b.distance_km,
            b.estimated_price, b.booking_status,
            b.payment_status, b.payment_method,
            b.packing_required, b.created_at,
            v.vehicle_name
     FROM bookings b
     INNER JOIN vehicles v ON b.vehicle_id = v.id
     WHERE b.user_id = ?
     ORDER BY b.created_at DESC"
);
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function statusClass($status) {
    switch ($status) {
        case "confirmed": return "confirmed";
        case "packed": return "packed";
        case "in_transit": return "transit";
        case "delivered": return "delivered";
        case "cancelled": return "cancelled";
        default: return "pending";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Bookings | SGTS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box}
body{margin:0;background:#f3f6fa;color:#17243a;font-family:Inter,Arial,sans-serif}
nav{background:#0b1f38;color:white;padding:18px 6%;display:flex;justify-content:space-between;align-items:center;gap:15px}
.brand{font-size:22px;font-weight:800;color:white;text-decoration:none}
.brand i{color:#ff8a24}
nav a{color:white;text-decoration:none;font-size:13px;margin-left:15px}
main{max-width:1150px;margin:35px auto;padding:0 20px 40px}
.heading{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:24px}
h1{margin:0 0 8px;font-size:29px}
.muted{color:#718096;font-size:13px;line-height:1.6}
.button{display:inline-block;padding:12px 16px;background:#ff8a24;color:white;text-decoration:none;border-radius:8px;font-size:13px;font-weight:800}
.summary{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px}
.summary-card,.booking-card,.empty{background:white;border-radius:13px;padding:22px;box-shadow:0 5px 20px #142c460a}
.summary-card i{color:#f58220;font-size:23px}
.summary-card strong{display:block;font-size:25px;margin-top:12px}
.summary-card span{color:#718096;font-size:13px}
.booking-card{margin-bottom:18px}
.booking-top{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;border-bottom:1px solid #edf0f5;padding-bottom:17px;margin-bottom:18px}
.booking-top h3{margin:0 0 7px;font-size:17px}
.booking-id{color:#718096;font-size:12px;overflow-wrap:anywhere}
.badge{display:inline-block;padding:7px 10px;border-radius:20px;font-size:11px;font-weight:800;white-space:nowrap}
.pending{background:#fff4d6;color:#875b00}
.confirmed{background:#e4f0ff;color:#205a9c}
.packed{background:#f0e8ff;color:#6541a5}
.transit{background:#e3f6ff;color:#116b8b}
.delivered{background:#e3f8eb;color:#187443}
.cancelled{background:#ffebeb;color:#ad2727}
.details{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.detail small{display:block;color:#718096;font-size:12px;margin-bottom:6px}
.detail strong{font-size:14px;line-height:1.6;overflow-wrap:anywhere}
.address{margin-top:18px;padding:14px;background:#f7f9fc;border-radius:9px;font-size:13px;line-height:1.8;overflow-wrap:anywhere}
.address b{color:#30445d}
.payment-note{margin-top:15px;padding:13px;background:#fff6eb;border:1px solid #ffe0bd;border-radius:9px;font-size:13px;line-height:1.6}
.empty{text-align:center;padding:50px 20px}
.empty i{font-size:45px;color:#ff8a24}
@media(max-width:700px){
.heading{align-items:flex-start;flex-direction:column}
.summary{grid-template-columns:1fr}
.details{grid-template-columns:1fr 1fr}
.booking-card{padding:18px}
.booking-top{flex-direction:column}
h1{font-size:25px}
nav{padding:16px 4%}
nav a{margin-left:8px}
}
@media(max-width:430px){.details{grid-template-columns:1fr}}
</style>
</head>
<body>
<nav>
    <a class="brand" href="dashboard.php"><i class="fa-solid fa-truck-fast"></i> SGTS</a>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="booking.php">Book Service</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<main>
<div class="heading">
    <div>
        <h1>My Bookings</h1>
        <div class="muted">Track your shifting requests and payment preferences.</div>
    </div>
    <a class="button" href="booking.php"><i class="fa-solid fa-plus"></i> New Booking</a>
</div>

<section class="summary">
    <div class="summary-card">
        <i class="fa-solid fa-clipboard-list"></i>
        <strong><?= count($bookings) ?></strong>
        <span>Total Bookings</span>
    </div>
    <div class="summary-card">
        <i class="fa-solid fa-clock"></i>
        <strong><?= count(array_filter($bookings, fn($b) => $b["booking_status"] === "pending")) ?></strong>
        <span>Pending Requests</span>
    </div>
    <div class="summary-card">
        <i class="fa-solid fa-circle-check"></i>
        <strong><?= count(array_filter($bookings, fn($b) => $b["booking_status"] === "delivered")) ?></strong>
        <span>Delivered Bookings</span>
    </div>
</section>

<?php if (!$bookings): ?>
<section class="empty">
    <i class="fa-solid fa-truck-ramp-box"></i>
    <h2>No bookings yet</h2>
    <p class="muted">Book your first goods transport service to see it here.</p>
    <a class="button" href="booking.php">Book a Service</a>
</section>
<?php else: ?>

<?php foreach ($bookings as $b): ?>
<article class="booking-card">
    <div class="booking-top">
        <div>
            <h3><?= e($b["shifting_type"]) ?></h3>
            <div class="booking-id">
                Booking ID: <?= e($b["booking_code"]) ?>
                · Created <?= e(date("d M Y", strtotime($b["created_at"]))) ?>
            </div>
        </div>
        <span class="badge <?= e(statusClass($b["booking_status"])) ?>">
            <?= e(ucwords(str_replace("_", " ", $b["booking_status"]))) ?>
        </span>
    </div>

    <div class="details">
        <div class="detail">
            <small><i class="fa-solid fa-truck"></i> Vehicle</small>
            <strong><?= e($b["vehicle_name"]) ?></strong>
        </div>
        <div class="detail">
            <small><i class="fa-regular fa-calendar"></i> Shifting Date</small>
            <strong><?= e(date("d M Y", strtotime($b["shifting_date"]))) ?></strong>
        </div>
        <div class="detail">
            <small><i class="fa-solid fa-indian-rupee-sign"></i> Estimated Fare</small>
            <strong>₹<?= number_format((float)$b["estimated_price"], 2) ?></strong>
        </div>
        <div class="detail">
            <small><i class="fa-solid fa-route"></i> Distance</small>
            <strong><?= number_format((float)$b["distance_km"], 1) ?> km</strong>
        </div>
        <div class="detail">
            <small><i class="fa-solid fa-box"></i> Packing</small>
            <strong><?= $b["packing_required"] ? "Required" : "Not required" ?></strong>
        </div>
        <div class="detail">
            <small><i class="fa-solid fa-wallet"></i> Payment Method</small>
            <strong><?= e($b["payment_method"]) ?></strong>
        </div>
        <div class="detail">
            <small><i class="fa-solid fa-credit-card"></i> Payment Status</small>
            <strong><?= e(ucfirst($b["payment_status"])) ?></strong>
        </div>
    </div>

    <div class="address">
        <b><i class="fa-solid fa-location-dot"></i> Pickup:</b>
        <?= e($b["pickup_address"]) ?><br>
        <b><i class="fa-solid fa-flag-checkered"></i> Delivery:</b>
        <?= e($b["delivery_address"]) ?>
    </div>

    <?php if ($b["payment_method"] === "UPI" && $b["payment_status"] !== "paid"): ?>
        <div class="payment-note">
            <strong>UPI advance payment selected.</strong><br>
            UPI ID: 8210121756@ybl<br>
            Open your booking page after booking to initiate payment. This page does not verify payment automatically.
        </div>
    <?php elseif ($b["payment_method"] === "COD"): ?>
        <div class="payment-note">
            <strong>Cash on Delivery selected.</strong><br>
            Pay after delivery as agreed with the transport provider. Current payment status: <?= e(ucfirst($b["payment_status"])) ?>.
        </div>
    <?php endif; ?>
</article>
<?php endforeach; ?>

<?php endif; ?>
</main>
</body>
</html>
