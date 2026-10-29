
<?php
session_start();

require_once __DIR__ . "/config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];
$success = "";
$error = "";

// Profile update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $phone = trim($_POST["phone"] ?? "");

    if ($name === "" || $phone === "") {
        $error = "Please fill in all required fields.";
    } elseif (strlen($name) > 100) {
        $error = "Name must not exceed 100 characters.";
    } elseif (!preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
        $error = "Please enter a valid phone number.";
    } else {
        try {
            $stmt = $pdo->prepare(
                "UPDATE users SET name = ?, phone = ? WHERE id = ?"
            );
            $stmt->execute([$name, $phone, $userId]);

            $_SESSION["user_name"] = $name;
            $success = "Profile updated successfully!";
        } catch (PDOException $e) {
            error_log("SGTS profile update failed: " . $e->getMessage());
            $error = "Unable to update your profile. Please try again.";
        }
    }
}

// Get latest profile
$stmt = $pdo->prepare(
    "SELECT name, email, phone, created_at FROM users WHERE id = ?"
);
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | SGTS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: #f3f6fb;
            color: #18243a;
        }
        .navbar {
            background: #101d35;
            padding: 17px 6%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
        }
        .brand {
            color: white;
            font-size: 23px;
            font-weight: 800;
            text-decoration: none;
        }
        .brand span { color: #ff922e; }
        .navlinks { display: flex; gap: 20px; flex-wrap: wrap; }
        .navlinks a {
            color: #e6ebf5;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }
        .navlinks a:hover { color: #ff922e; }
        .container { max-width: 950px; margin: 38px auto; padding: 0 18px; }
        .heading { margin-bottom: 24px; }
        .heading h1 { margin: 0 0 8px; font-size: 30px; }
        .heading p { color: #68758b; margin: 0; line-height: 1.6; }
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 22px;
        }
        .card {
            background: white;
            padding: 27px;
            border-radius: 17px;
            box-shadow: 0 8px 28px rgba(16,29,53,.06);
        }
        .avatar {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #fff0e2;
            color: #e87516;
            font-size: 30px;
            font-weight: 800;
            margin-bottom: 18px;
        }
        .card h2 { margin: 0 0 20px; font-size: 20px; }
        .user-name { font-size: 21px; font-weight: 800; margin-bottom: 7px; }
        .user-email { color: #68758b; overflow-wrap: anywhere; }
        .info {
            border-top: 1px solid #edf0f5;
            margin-top: 23px;
            padding-top: 17px;
            font-size: 13px;
            color: #68758b;
            line-height: 1.8;
        }
        label { display: block; font-size: 13px; font-weight: 700; margin: 17px 0 8px; }
        input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #dce2ec;
            border-radius: 9px;
            font: inherit;
            outline: none;
        }
        input:focus { border-color: #e87516; box-shadow: 0 0 0 3px #fff0e2; }
        input[readonly] { background: #f3f6fb; color: #68758b; }
        button {
            margin-top: 23px;
            width: 100%;
            border: 0;
            border-radius: 9px;
            background: #e87516;
            color: white;
            padding: 14px;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
        }
        button:hover { background: #cf610b; }
        .message { padding: 13px; border-radius: 9px; margin-bottom: 16px; font-size: 14px; }
        .success { color: #17633a; background: #e8f8ee; }
        .error { color: #9e2626; background: #fff0f0; }
        .back {
            display: inline-block;
            margin-top: 22px;
            color: #e87516;
            font-weight: 700;
            text-decoration: none;
        }
        @media(max-width: 700px) {
            .profile-grid { grid-template-columns: 1fr; }
            .heading h1 { font-size: 25px; }
            .navbar { padding: 16px 5%; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a class="brand" href="dashboard.php">SGTS<span>.</span></a>
    <div class="navlinks">
        <a href="index.php">Home</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="booking.php">Book Transport</a>
        <a href="my_bookings.php">My Bookings</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<main class="container">
    <div class="heading">
        <h1>My Profile</h1>
        <p>View your account details and update your personal information.</p>
    </div>

    <?php if ($success !== ""): ?>
        <div class="message success"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="message error"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="profile-grid">
        <section class="card">
            <div class="avatar">
                <?= e(strtoupper(substr($user["name"], 0, 1))) ?>
            </div>
            <div class="user-name"><?= e($user["name"]) ?></div>
            <div class="user-email"><?= e($user["email"]) ?></div>

            <div class="info">
                <strong>Account Type:</strong>
                <?= e(ucfirst($_SESSION["user_role"] ?? "user")) ?><br>
                <strong>Member Since:</strong>
                <?= e(date("d M Y", strtotime($user["created_at"]))) ?>
            </div>
        </section>

        <section class="card">
            <h2>Edit Personal Details</h2>

            <form method="POST" action="my_profile.php">
                <label for="name">Full Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    maxlength="100"
                    value="<?= e($user["name"]) ?>"
                    required
                >

                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    value="<?= e($user["email"]) ?>"
                    readonly
                >

                <label for="phone">Mobile Number</label>
                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    maxlength="20"
                    value="<?= e($user["phone"]) ?>"
                    required
                >

                <button type="submit">Save Changes</button>
            </form>
        </section>
    </div>

    <a class="back" href="dashboard.php">← Back to Dashboard</a>
</main>

</body>
</html>
