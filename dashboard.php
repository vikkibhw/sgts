```php
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$name = $_SESSION["user_name"] ?? "User";
$role = $_SESSION["user_role"] ?? "user";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | SGTS</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: #f3f6fa;
            color: #17243a;
            font-family: Inter, Arial, sans-serif;
        }

        nav {
            background: #0b1f38;
            color: white;
            padding: 18px 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .brand {
            font-size: 22px;
            font-weight: 800;
            color: white;
            text-decoration: none;
            white-space: nowrap;
        }

        .brand i { color: #ff8a24; }

        .profile-link {
            color: #ffffff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            padding: 9px 12px;
            border-radius: 7px;
            transition: 0.2s;
        }

        .profile-link:hover {
            background: #1c3552;
            color: #ffb36d;
        }

        .logout {
            background: #ff8a24;
            color: white;
            padding: 10px 16px;
            border-radius: 7px;
            font-weight: 700;
            text-decoration: none;
            font-size: 14px;
            white-space: nowrap;
        }

        .logout:hover { background: #e87516; }

        main {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 22px;
        }

        .welcome {
            padding: 35px;
            border-radius: 16px;
            color: white;
            background: linear-gradient(120deg, #123b61, #0b1f38);
        }

        .welcome h1 { margin: 0 0 12px; font-size: 30px; }

        .welcome p {
            color: #d5e0ec;
            line-height: 1.7;
        }

        .tag {
            display: inline-block;
            padding: 6px 11px;
            border-radius: 20px;
            background: #ff8a2426;
            color: #ffb36d;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .section-title { margin: 30px 0 16px; }

        .cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .card {
            background: white;
            border-radius: 13px;
            padding: 25px;
            box-shadow: 0 5px 20px #142c4610;
        }

        .card i { font-size: 28px; color: #f58220; }

        .card h3 { margin-bottom: 8px; }

        .card p {
            color: #718096;
            font-size: 14px;
            line-height: 1.6;
        }

        .card-link {
            display: inline-block;
            margin-top: 10px;
            color: #e87516;
            font-weight: 700;
            text-decoration: none;
        }

        .card-link:hover { color: #b95306; }

        .status {
            margin-top: 25px;
            color: #718096;
            font-size: 13px;
        }

        @media(max-width: 700px) {
            nav { padding: 16px 5%; }

            .nav-left {
                gap: 12px;
                flex-wrap: wrap;
            }

            .brand { font-size: 20px; }

            .profile-link { padding: 8px 5px; }

            .cards { grid-template-columns: 1fr; }

            .welcome { padding: 25px; }

            .welcome h1 { font-size: 25px; }
        }
    </style>
</head>

<body>

<nav>
    <div class="nav-left">
        <a href="dashboard.php" class="brand">
            <i class="fa-solid fa-truck-fast"></i> SGTS
        </a>
        
        <a href="my_profile.php" class="profile-link">
            <i class="fa-solid fa-user"></i> My Profile
        </a>
    </div>

    <a class="logout" href="logout.php">
        Logout <i class="fa-solid fa-right-from-bracket"></i>
    </a>
</nav>

<main>
    <section class="welcome">
        <span class="tag">
            <?= htmlspecialchars($role, ENT_QUOTES, "UTF-8") ?> account
        </span>

        <h1>
            Welcome, <?= htmlspecialchars($name, ENT_QUOTES, "UTF-8") ?>!
        </h1>

        <p>
            Your SGTS account is active. From here, you can manage your goods
            transport and shifting activities.
        </p>
    </section>

    <h2 class="section-title">Your Dashboard</h2>

    <section class="cards">
        <div class="card">
            <i class="fa-solid fa-boxes-stacked"></i>
            <h3>Book a Shifting Service</h3>
            <p>Book transport for home shifting, shop shifting, or other goods.</p>
            <a href="booking.php" class="card-link">Book Now →</a>
        </div>

        
        

<div class="card">
    <div class="card-icon">
        <i class="fas fa-star"></i>
    </div>
    <h3>Feedback</h3>
    <p>Share your transport experience and rate our service.</p>
    <a href="feedback.php" class="card-link">Give Feedback → </a>
</div>



        <div class="card">
            <i class="fa-solid fa-clipboard-list"></i>
            <h3>My Bookings</h3>
            <p>View your booking history and transport status.</p>
            <a href="my_bookings.php" class="card-link">
                View My Bookings →
            </a>
        </div>
    </section>

    <p class="status">
        SGTS — Smart Goods Transport System | User Dashboard
    </p>
</main>

</body>
</html>
```