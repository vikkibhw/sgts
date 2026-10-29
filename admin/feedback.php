
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

$search = trim($_GET['search'] ?? '');

$sql = "SELECT
            f.id,
            f.rating,
            f.message,
            f.created_at,
            u.name AS customer_name,
            u.email,
            b.booking_code
        FROM feedback f
        LEFT JOIN users u ON f.user_id = u.id
        LEFT JOIN bookings b ON f.booking_id = b.id";

$params = [];

if ($search !== '') {
    $sql .= " WHERE u.name LIKE ?
              OR u.email LIKE ?
              OR b.booking_code LIKE ?
              OR f.message LIKE ?";
    $term = "%{$search}%";
    $params = [$term, $term, $term, $term];
}

$sql .= " ORDER BY f.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$feedbackList = $stmt->fetchAll();

function h($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customer Feedback | SGTS Admin</title>
<style>
*{box-sizing:border-box}
body{margin:0;font-family:Arial,sans-serif;background:#f3f6fb;color:#17243b}
header{background:#101d35;color:white;padding:18px 5%;display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap}
.brand{font-size:23px;font-weight:bold}
.brand span{color:#ff8a32}
header a{color:white;text-decoration:none;margin-left:16px}
main{max-width:1200px;margin:30px auto;padding:0 20px}
h1{margin-bottom:8px}
.sub{color:#68758a;margin-bottom:24px}
.panel{background:white;padding:20px;border-radius:12px;box-shadow:0 4px 18px #15284b0c;margin-bottom:20px}
.search{display:flex;gap:10px;flex-wrap:wrap}
input{padding:11px;border:1px solid #d6dce6;border-radius:7px;font-size:14px;min-width:220px;flex:1}
button,.btn{border:0;border-radius:7px;background:#ff8a32;color:white;padding:11px 15px;font-weight:bold;cursor:pointer;text-decoration:none;display:inline-block}
.btn.secondary{background:#263956}
.table-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse;min-width:750px}
th,td{text-align:left;padding:14px 12px;border-bottom:1px solid #edf0f5;vertical-align:top;font-size:14px}
th{background:#f7f9fc;color:#3c4960}
small{color:#68758a}
.stars{color:#f59e0b;letter-spacing:2px;font-size:17px;white-space:nowrap}
.message{max-width:420px;overflow-wrap:anywhere;white-space:pre-wrap}
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
        <a href="bookings.php">Bookings</a>
        <a href="payments.php">Payments</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>
    <h1>Customer Feedback</h1>
    <p class="sub">View customer ratings, comments and related booking details.</p>

    <section class="panel">
        <form class="search" method="GET">
            <input type="text" name="search"
                   placeholder="Search customer, email, booking code or comment"
                   value="<?= h($search) ?>">
            <button type="submit">Search</button>
            <a class="btn secondary" href="feedback.php">Reset</a>
        </form>
    </section>

    <section class="panel">
        <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Booking Code</th>
                    <th>Rating</th>
                    <th>Feedback</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$feedbackList): ?>
                <tr>
                    <td colspan="5">No feedback found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($feedbackList as $item): ?>
                <tr>
                    <td>
                        <strong><?= h($item['customer_name'] ?: 'Unknown customer') ?></strong><br>
                        <small><?= h($item['email']) ?></small>
                    </td>
                    <td><?= h($item['booking_code'] ?: 'General feedback') ?></td>
                    <td>
                        <span class="stars">
                            <?= str_repeat('★', max(0, min(5, (int)$item['rating']))) ?>
                        </span>
                        <br><small><?= (int)$item['rating'] ?>/5</small>
                    </td>
                    <td class="message"><?= h($item['message']) ?></td>
                    <td><?= h($item['created_at']) ?></td>
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
