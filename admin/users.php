
<?php
session_start();
require_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? "") !== "admin") {
    header("Location: ../login.php");
    exit;
}

function h($value): string {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}

$search = trim($_GET["search"] ?? "");

$sql = "SELECT u.id, u.name, u.email, u.phone, u.created_at,
               COUNT(b.id) AS total_bookings
        FROM users u
        LEFT JOIN bookings b ON b.user_id = u.id
        WHERE u.role = 'user'";

$params = [];

if ($search !== "") {
    $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $term = "%" . $search . "%";
    $params = [$term, $term, $term];
}

$sql .= " GROUP BY u.id, u.name, u.email, u.phone, u.created_at
          ORDER BY u.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Users | SGTS Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box}
body{margin:0;background:#f3f6fb;color:#17243a;font-family:Inter,Arial,sans-serif}
.topbar{background:#0b203b;color:white;padding:18px 5%;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
.brand{font-size:21px;font-weight:800}.brand i{color:#ff8a24;margin-right:8px}
.topbar a{color:white;text-decoration:none;font-size:13px}
.container{max-width:1250px;margin:30px auto;padding:0 20px}
.heading{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:22px}
h1{font-size:26px;margin:0 0 7px}.muted{color:#718096;font-size:12px}
.back{background:#e87516;color:white;padding:11px 15px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700}
.panel{background:white;padding:22px;border-radius:13px;border:1px solid #e5ebf3;box-shadow:0 4px 18px #17243a08}
.toolbar{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;margin-bottom:18px}
.search{display:flex;gap:8px;flex:1;max-width:520px}
input{width:100%;min-width:0;padding:12px;border:1px solid #d9e0e8;border-radius:8px;font-size:13px}
button{border:0;border-radius:8px;padding:12px 16px;background:#ff8a24;color:white;font-weight:700;cursor:pointer}
.table-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse;min-width:750px}
th,td{text-align:left;padding:15px 13px;border-bottom:1px solid #edf0f5;font-size:12px}
th{background:#f8fafc;color:#59677b;font-size:10px;text-transform:uppercase;letter-spacing:.4px}
tr:last-child td{border-bottom:0}
.avatar{display:inline-flex;width:34px;height:34px;align-items:center;justify-content:center;border-radius:50%;background:#fff0e5;color:#d96d1c;font-weight:800;margin-right:9px}
.count{background:#e9f2ff;color:#175cd3;padding:5px 9px;border-radius:18px;font-weight:700}
.empty{text-align:center;color:#718096;padding:28px}
</style>
</head>
<body>
<header class="topbar">
    <div class="brand"><i class="fa-solid fa-truck-fast"></i> SGTS <span style="font-size:12px;font-weight:500;color:#cbd5e1">ADMIN</span></div>
    <a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
</header>

<main class="container">
    <div class="heading">
        <div>
            <h1>Manage Users</h1>
            <div class="muted">View registered customers and their booking activity.</div>
        </div>
        <a class="back" href="dashboard.php"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
    </div>

    <section class="panel">
        <div class="toolbar">
            <strong><i class="fa-solid fa-users" style="color:#e87516"></i> Registered Users (<?= count($users) ?>)</strong>
            <form class="search" method="GET" action="users.php">
                <input type="search" name="search" value="<?= h($search) ?>" placeholder="Search name, email or phone...">
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
            </form>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>User</th><th>Email</th><th>Phone</th><th>Total Bookings</th><th>Registered On</th></tr>
                </thead>
                <tbody>
                <?php if (!$users): ?>
                    <tr><td colspan="5" class="empty">No registered users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <span class="avatar"><?= h(strtoupper(substr($user["name"] ?: "U", 0, 1))) ?></span>
                            <strong><?= h($user["name"]) ?></strong>
                        </td>
                        <td><?= h($user["email"]) ?></td>
                        <td><?= h($user["phone"] ?: "Not provided") ?></td>
                        <td><span class="count"><?= (int)$user["total_bookings"] ?></span></td>
                        <td><?= $user["created_at"] ? h(date("d M Y", strtotime($user["created_at"]))) : "—" ?></td>
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
