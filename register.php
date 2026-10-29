
<?php
session_start();
require_once __DIR__ . "/config/db.php";

$error = "";
$name = "";
$email = "";
$phone = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if ($name === "" || $email === "" || $phone === "" || $password === "") {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (!preg_match('/^[0-9+\-\s]{10,16}$/', $phone)) {
        $error = "Please enter a valid phone number.";
    } elseif (strlen($password) < 8) {
        $error = "Password must contain at least 8 characters.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        try {
            $check = $pdo->prepare(
                "SELECT id FROM users WHERE email = :email LIMIT 1"
            );
            $check->execute(["email" => $email]);

            if ($check->fetch()) {
                $error = "This email is already registered. Please log in.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare(
                    "INSERT INTO users (name, email, phone, password, role)
                     VALUES (:name, :email, :phone, :password, 'user')"
                );

                $stmt->execute([
                    "name" => $name,
                    "email" => $email,
                    "phone" => $phone,
                    "password" => $hashedPassword
                ]);

                header("Location: login.php?registered=1");
                exit;
            }
        } catch (PDOException $e) {
            error_log("SGTS registration error: " . $e->getMessage());
            $error = "Registration could not be completed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Account | SGTS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
*{box-sizing:border-box;margin:0;padding:0}
body{
    font-family:Inter,Arial,sans-serif;
    min-height:100vh;
    background:linear-gradient(135deg,#062343,#0b4779);
    display:flex;flex-direction:column;color:#18314e
}
header{
    padding:18px 6%;display:flex;align-items:center;
    justify-content:space-between;color:white
}
.logo{display:flex;align-items:center;gap:11px;color:white;text-decoration:none}
.logo-icon{
    width:47px;height:47px;border-radius:14px;
    background:#ff8a1d;display:grid;place-items:center;
    font-size:24px;color:#062343
}
.logo-name{font-size:26px;font-weight:800;letter-spacing:1px}
.logo-name span{color:#ff9a32}
.logo small{display:block;font-size:10px;color:#cfdeed;margin-top:3px}
.home-link{color:white;text-decoration:none;font-size:13px;font-weight:600}
.home-link:hover{color:#ffad56}
main{flex:1;display:grid;place-items:center;padding:30px 16px 50px}
.register-card{
    width:100%;max-width:1000px;background:white;border-radius:22px;
    overflow:hidden;display:grid;grid-template-columns: .9fr 1.1fr;
    box-shadow:0 25px 70px #00172b50
}
.side-panel{
    padding:45px 36px;color:white;position:relative;
    background:linear-gradient(160deg,#0b4779,#041e3a);
    display:flex;flex-direction:column;justify-content:center;overflow:hidden
}
.side-panel:after{
    content:"";position:absolute;width:280px;height:280px;
    border:45px solid #ffffff0b;border-radius:50%;right:-130px;bottom:-100px
}
.truck-art{
    font-size:78px;color:#ff922d;margin:15px 0 30px;
    filter:drop-shadow(0 10px 12px #0003)
}
.side-panel h1{font-size:31px;line-height:1.25;margin-bottom:15px}
.side-panel h1 span{color:#ff9b3c}
.side-panel p{font-size:13px;line-height:1.9;color:#d1e1f0}
.benefits{margin-top:28px;display:grid;gap:18px}
.benefit{display:flex;gap:12px;align-items:center;font-size:12px;color:#e8f1fa}
.benefit i{color:#ff9b3c;font-size:17px;width:22px}
.form-panel{padding:43px 45px}
.form-panel h2{font-size:27px;color:#092d52;margin-bottom:8px}
.subtitle{font-size:13px;color:#7c8ba0;margin-bottom:25px}
.alert{
    background:#fff0ee;color:#b42318;padding:12px 14px;
    border-radius:9px;font-size:12px;margin-bottom:18px;line-height:1.5
}
.field{margin-bottom:16px}
.field label{display:block;font-size:12px;font-weight:700;margin-bottom:8px;color:#263e59}
.input-wrap{position:relative}
.input-wrap i{
    position:absolute;left:14px;top:50%;transform:translateY(-50%);
    color:#8294a8;font-size:14px
}
.field input{
    width:100%;padding:13px 14px 13px 42px;
    border:1px solid #dce5ef;border-radius:10px;
    outline:none;font:inherit;font-size:13px;transition:.2s
}
.field input:focus{border-color:#f39232;box-shadow:0 0 0 3px #ff922d20}
.two-fields{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.submit-btn{
    width:100%;padding:15px;border:0;border-radius:10px;
    background:#ff8a1d;color:#092747;font-size:14px;
    font-weight:800;cursor:pointer;margin-top:5px;transition:.2s
}
.submit-btn:hover{background:#ffab50;transform:translateY(-1px)}
.submit-btn i{margin-left:7px}
.bottom-text{text-align:center;margin-top:21px;color:#75859a;font-size:12px}
.bottom-text a{color:#e8750a;font-weight:800;text-decoration:none}
.terms{font-size:11px;color:#8997a9;line-height:1.6;margin:14px 0}
@media(max-width:760px){
    header{padding:15px 5%}
    .register-card{max-width:480px;grid-template-columns:1fr}
    .side-panel{padding:25px 28px}
    .truck-art{font-size:45px;margin:10px 0}
    .side-panel h1{font-size:23px}
    .side-panel p{font-size:12px}
    .benefits{grid-template-columns:1fr 1fr;gap:12px;margin-top:18px}
    .form-panel{padding:30px 24px}
}
@media(max-width:420px){
    .two-fields{grid-template-columns:1fr;gap:0}
    .form-panel h2{font-size:24px}
}
</style>
</head>
<body>

<header>
    <a class="logo" href="index.php">
        <div class="logo-icon"><i class="fa-solid fa-truck-fast"></i></div>
        <div>
            <div class="logo-name">SG<span>TS</span></div>
            <small>Smart Goods Transport System</small>
        </div>
    </a>
    <a href="index.php" class="home-link"><i class="fa-solid fa-arrow-left"></i> Back to Home</a>
</header>

<main>
<div class="register-card">
    <section class="side-panel">
        <div class="truck-art"><i class="fa-solid fa-truck-moving"></i></div>
        <h1>Your Goods.<br><span>Our Priority.</span></h1>
        <p>Create your SGTS account and get ready to manage your goods transport bookings in one place.</p>
        <div class="benefits">
            <div class="benefit"><i class="fa-solid fa-circle-check"></i> Simple booking process</div>
            <div class="benefit"><i class="fa-solid fa-truck-fast"></i> Suitable vehicle options</div>
            <div class="benefit"><i class="fa-solid fa-clipboard-check"></i> Booking status updates</div>
        </div>
    </section>

    <section class="form-panel">
        <h2>Create Your Account</h2>
        <p class="subtitle">Join SGTS to get started with your transport booking.</p>

        <?php if ($error !== ""): ?>
            <div class="alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="field">
                <label for="name">Full Name</label>
                <div class="input-wrap">
                    <i class="fa-regular fa-user"></i>
                    <input id="name" type="text" name="name"
                        placeholder="Enter your full name"
                        value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                        maxlength="100" autocomplete="name" required>
                </div>
            </div>

            <div class="field">
                <label for="email">Email Address</label>
                <div class="input-wrap">
                    <i class="fa-regular fa-envelope"></i>
                    <input id="email" type="email" name="email"
                        placeholder="you@example.com"
                        value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                        maxlength="150" autocomplete="email" required>
                </div>
            </div>

            <div class="field">
                <label for="phone">Mobile Number</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-phone"></i>
                    <input id="phone" type="tel" name="phone"
                        placeholder="Enter mobile number"
                        value="<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>"
                        maxlength="16" autocomplete="tel" required>
                </div>
            </div>

            <div class="two-fields">
                <div class="field">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-lock"></i>
                        <input id="password" type="password" name="password"
                            placeholder="Min. 8 characters"
                            minlength="8" autocomplete="new-password" required>
                    </div>
                </div>

                <div class="field">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-shield-halved"></i>
                        <input id="confirm_password" type="password"
                            name="confirm_password" placeholder="Repeat password"
                            minlength="8" autocomplete="new-password" required>
                    </div>
                </div>
            </div>

            <p class="terms">By creating an account, you agree to provide accurate information for your bookings.</p>

            <button type="submit" class="submit-btn">
                Create Account <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <div class="bottom-text">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </section>
</div>
</main>

</body>
</html>
