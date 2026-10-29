
<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (
    !isset($_SESSION['user_id']) ||
    ($_SESSION['user_role'] ?? '') !== 'admin'
) {
    header("Location: ../login.php");
    exit;
}

$allowedStatuses = [
    'pending',
    'confirmed',
    'packed',
    'in_transit',
    'delivered',
    'cancelled'
];

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    $status = $_POST['booking_status'] ?? '';

    if (!$bookingId || !in_array($status, $allowedStatuses, true)) {
        $error = "Invalid booking details.";
    } else {
        try {
            $stmt = $pdo->prepare(
                "UPDATE bookings SET booking_status = ? WHERE id = ?"
            );
            $stmt->execute([$status, $bookingId]);

            $message = $stmt->rowCount() > 0
                ? "Booking status updated successfully."
                : "No change made, or booking not found.";
        } catch (PDOException $e) {
            error_log("Booking update failed: " . $e->getMessage());
            $error = "Unable to update booking status.";
        }
    }
}

$search = trim($_GET['search'] ?? '');

$sql = "SELECT
            b.id,
            b.booking_code,
            b.shifting_type,
            b.pickup_address,
            b.delivery_address,
            b.shifting_date,
            b.distance_km,
            b.estimated_price,
            b.booking_status,
            b.created_at,
            u.name AS customer_name,
            u.phone AS customer_phone,
            v.vehicle_name
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN vehicles v ON b.vehicle_id = v.id";

$params = [];

if ($search !== '') {
    $sql .= " WHERE b.booking_code LIKE ?
              OR u.name LIKE ?
              OR u.phone LIKE ?";
    $term = "%{$search}%";
    $params = [$term, $term, $term];
}

$sql .= " ORDER BY b.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

function h($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Bookings | SGTS Admin</title>
<style>
*{box-sizing:border-box}
body{margin:0;font-family:Arial,sans-serif;background:#f3f6fb;color:#17243b}
header{background:#101d35;color:white;padding:18px 5%;display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap}
.brand{font-size:23px;font-weight:bold}
.brand span{color:#ff8a32}
header a{color:white;text-decoration:none;margin-left:16px}
main{max-width:1450px;margin:30px auto;padding:0 20px}
h1{margin-bottom:8px}
.sub{color:#68758a;margin-bottom:24px}
.panel{background:white;padding:20px;border-radius:12px;box-shadow:0 4px 18px #15284b0c;margin-bottom:20px}
.search{display:flex;gap:10px;flex-wrap:wrap}
input,select{padding:10px;border:1px solid #d6dce6;border-radius:7px;font-size:14px}
input{min-width:220px;flex:1}
button,.btn{border:0;border-radius:7px;background:#ff8a32;color:white;padding:10px 14px;font-weight:bold;cursor:pointer;text-decoration:none;display:inline-block}
.btn.secondary{background:#263956}
.table-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse;min-width:1050px}
th,td{text-align:left;padding:13px 11px;border-bottom:1px solid #edf0f5;vertical-align:top;font-size:13px}
th{background:#f7f9fc;color:#3c4960}
small{color:#68758a}
.address{max-width:190px;overflow-wrap:anywhere}
.status-form{display:flex;gap:7px;align-items:center;min-width:180px}
.status-form select{min-width:115px}
.notice{padding:12px 15px;border-radius:8px;margin-bottom:15px;background:#e7f8ed;color:#176537}
.error{background:#ffeded;color:#9b2222}
.price{font-weight:bold;color:#17243b}
@media(max-width:600px){header{align-items:flex-start}header a{margin-left:0;margin-right:12px;display:inline-block;margin-top:10px}main{padding:0 12px}}
</style>
</head>
<body>
<header>
    <div class="brand">SGTS <span>Admin</span></div>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="vehicles.php">Vehicles</a>
        <a href="users.php">Users</a>
        <a href="payments.php">Payments</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>
    <h1>Manage Bookings</h1>
    <p class="sub">View customer bookings and update their delivery status.</p>

    <?php if ($message !== ''): ?>
        <div class="notice"><?= h($message) ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="notice error"><?= h($error) ?></div>
    <?php endif; ?>

    <section class="panel">
        <form class="search" method="GET">
            <input type="text" name="search"
                   placeholder="Search booking code, customer or phone"
                   value="<?= h($search) ?>">
            <button type="submit">Search</button>
            <a class="btn secondary" href="bookings.php">Reset</a>
        </form>
    </section>

    <section class="panel">
        <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Booking / Customer</th>
                    <th>Vehicle</th>
                    <th>Pickup</th>
                    <th>Delivery</th>
                    <th>Date / Distance</th>
                    <th>Estimated Price</th>
                    <th>Booking Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$bookings): ?>
                <tr><td colspan="7">No bookings found.</td></tr>
            <?php else: ?>
                <?php foreach ($bookings as $b): ?>
                <tr>
                    <td>
                        <strong><?= h($b['booking_code']) ?></strong><br>
                        <?= h($b['customer_name']) ?><br>
                        <small><?= h($b['customer_phone']) ?></small>
                    </td>
                    <td><?= h($b['vehicle_name'] ?: 'Vehicle unavailable') ?><br>
                        <small><?= h($b['shifting_type']) ?></small>
                    </td>
                    <td class="address"><?= h($b['pickup_address']) ?></td>
                    <td class="address"><?= h($b['delivery_address']) ?></td>
                    <td>
                        <?= h($b['shifting_date']) ?><br>
                        <small><?= h($b['distance_km']) ?> km</small>
                    </td>
                    <td class="price">₹<?= number_format((float)$b['estimated_price'], 2) ?></td>
                    <td>
                        <form method="POST" class="status-form">
                            <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                            <select name="booking_status" aria-label="Booking status">
                                <?php foreach ($allowedStatuses as $status): ?>
                                    <option value="<?= h($status) ?>"
                                        <?= $b['booking_status'] === $status ? 'selected' : '' ?>>
                                        <?= h(ucwords(str_replace('_', ' ', $status))) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Save</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </section>
</main>
</body>
</html>
