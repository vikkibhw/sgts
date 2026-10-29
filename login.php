
<?php
session_start();
require_once __DIR__ . "/config/db.php";

if (isset($_SESSION['user_id'])) {
    if (($_SESSION['user_role'] ?? '') === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

$error = "";
$registered = isset($_GET['registered']) && $_GET['registered'] == "1";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $error = "Please enter your email and password.";
    } else {
        $stmt = $pdo->prepare(
            "SELECT id, name, email, password, role
             FROM users WHERE email = ? LIMIT 1"
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["password"])) {
            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_role"] = $user["role"];

            if ($user["role"] === "admin") {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SGTS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: linear-gradient(135deg, #071a33, #123b61);
            font-family: Inter, Arial, sans-serif;
            color: #17243a;
        }
        .login-box {
            width: 100%;
            max-width: 950px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 70px #0005;
        }
        .welcome {
            padding: 55px 40px;
            color: white;
            background: linear-gradient(145deg, #102e50, #071a33);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .logo {
            font-size: 25px;
            font-weight: 800;
            margin-bottom: 35px;
        }
        .logo i { color: #ff8a24; margin-right: 8px; }
        .welcome h1 { font-size: 34px; line-height: 1.25; margin: 0 0 18px; }
        .welcome p { color: #d0dceb; line-height: 1.8; }
        .welcome .truck {
            font-size: 80px;
            color: #ff8a24;
            margin-top: 25px;
        }
        .form-area { padding: 55px 42px; }
        .form-area h2 { font-size: 28px; margin: 0 0 8px; }
        .sub { color: #718096; margin-bottom: 28px; font-size: 14px; }
        label { display: block; font-size: 13px; font-weight: 700; margin: 18px 0 8px; }
        input {
            width: 100%;
            padding: 14px;
            border: 1px solid #d9e0e8;
            border-radius: 9px;
            outline: none;
            font-size: 14px;
        }
        input:focus { border-color: #ff8a24; box-shadow: 0 0 0 3px #ff8a241c; }
        button {
            width: 100%;
            border: none;
            border-radius: 9px;
            padding: 15px;
            margin-top: 25px;
            background: #ff8a24;
            color: #fff;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
        }
        button:hover { background: #ed7410; }
        .message {
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            line-height: 1.5;
            margin: 15px 0;
        }
        .error { background: #fff0f0; color: #b42318; }
        .success { background: #eaf8ef; color: #187443; }
        .bottom { text-align: center; margin-top: 23px; font-size: 13px; color: #718096; }
        a { color: #e87516; font-weight: 700; text-decoration: none; }
        .home { display: block; text-align: center; margin-top: 18px; font-size: 13px; }
        @media (max-width: 700px) {
            .login-box { grid-template-columns: 1fr; }
            .welcome { padding: 30px; }
            .welcome h1 { font-size: 27px; }
            .welcome .truck { font-size: 48px; margin-top: 10px; }
            .logo { margin-bottom: 18px; }
            .form-area { padding: 35px 25px; }
        }
    </style>
</head>
<body>
<div class="login-box">
    <section class="welcome">
        <div class="logo"><i class="fa-solid fa-truck-fast"></i> SGTS</div>
        <h1>Welcome Back!</h1>
        <p>Log in to manage your goods transport and shifting bookings with Smart Goods Transport System.</p>
        <div class="truck"><i class="fa-solid fa-truck-moving"></i></div>
    </section>

    <section class="form-area">
        <h2>Login to SGTS</h2>
        <p class="sub">Enter your registered account details.</p>

        <?php if ($registered): ?>
            <div class="message success">Registration successful! Please log in.</div>
        <?php endif; ?>

        <?php if ($error !== ""): ?>
            <div class="message error">
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email"
                   placeholder="Enter your registered email"
                   autocomplete="email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   placeholder="Enter your password"
                   autocomplete="current-password" required>

            <button type="submit">
                Login <i class="fa-solid fa-arrow-right-to-bracket"></i>
            </button>
        </form>

        <div class="bottom">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
        <a class="home" href="index.php">← Back to Home</a>
    </section>
</div>
</body>
</html>
