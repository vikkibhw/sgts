
<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION['user_id'];
$message = '';
$error = '';

function h($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/* Get this user's bookings and existing feedback */
$stmt = $pdo->prepare(
    "SELECT b.id, b.booking_code, b.booking_status,
            f.id AS feedback_id
     FROM bookings b
     LEFT JOIN feedback f
       ON f.booking_id = b.id AND f.user_id = b.user_id
     WHERE b.user_id = ?
     ORDER BY b.created_at DESC"
);
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();

$eligibleBookings = [];
foreach ($bookings as $booking) {
    if (
        strtolower((string)$booking['booking_status']) === 'delivered'
        && empty($booking['feedback_id'])
    ) {
        $eligibleBookings[] = $booking;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
    $comment = trim($_POST['message'] ?? '');

    if (!$bookingId || !$rating || $rating < 1 || $rating > 5) {
        $error = "Please select a valid booking and rating from 1 to 5.";
    } elseif ($comment === '' || mb_strlen($comment) > 2000) {
        $error = "Please enter feedback (maximum 2000 characters).";
    } else {
        try {
            $check = $pdo->prepare(
                "SELECT id FROM bookings
                 WHERE id = ? AND user_id = ? AND booking_status = 'delivered'"
            );
            $check->execute([$bookingId, $userId]);

            if (!$check->fetch()) {
                $error = "Feedback can be submitted only for your delivered bookings.";
            } else {
                $existing = $pdo->prepare(
                    "SELECT id FROM feedback WHERE booking_id = ? AND user_id = ?"
                );
                $existing->execute([$bookingId, $userId]);

                if ($existing->fetch()) {
                    $error = "You have already submitted feedback for this booking.";
                } else {
                    $insert = $pdo->prepare(
                        "INSERT INTO feedback (user_id, booking_id, rating, message)
                         VALUES (?, ?, ?, ?)"
                    );
                    $insert->execute([$userId, $bookingId, $rating, $comment]);

                    header("Location: feedback.php?success=1");
                    exit;
                }
            }
        } catch (PDOException $e) {
            error_log("Feedback submission failed: " . $e->getMessage());
            $error = "Feedback could not be saved. Please try again.";
        }
    }
}

if (isset($_GET['success'])) {
    $message = "Thank you! Your feedback has been submitted successfully.";
}

/* Refresh booking list after submission */
$stmt = $pdo->prepare(
    "SELECT b.id, b.booking_code, b.booking_status,
            f.id AS feedback_id
     FROM bookings b
     LEFT JOIN feedback f
       ON f.booking_id = b.id AND f.user_id = b.user_id
     WHERE b.user_id = ?
     ORDER BY b.created_at DESC"
);
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();

$eligibleBookings = [];
foreach ($bookings as $booking) {
    if (
        strtolower((string)$booking['booking_status']) === 'delivered'
        && empty($booking['feedback_id'])
    ) {
        $eligibleBookings[] = $booking;
    }
}

/* Get user's submitted feedback */
$stmt = $pdo->prepare(
    "SELECT f.rating, f.message, f.created_at, b.booking_code
     FROM feedback f
     LEFT JOIN bookings b ON f.booking_id = b.id
     WHERE f.user_id = ?
     ORDER BY f.created_at DESC"
);
$stmt->execute([$userId]);
$myFeedback = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Feedback | SGTS</title>
<style>
*{box-sizing:border-box}
body{margin:0;font-family:Arial,sans-serif;background:#f3f6fb;color:#17243b}
header{background:#101d35;color:white;padding:18px 5%;display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap}
.brand{font-size:23px;font-weight:bold}
.brand span{color:#ff8a32}
nav a{color:white;text-decoration:none;margin-left:16px}
main{max-width:900px;margin:30px auto;padding:0 18px}
h1{margin-bottom:8px}
.sub{color:#68758a;margin-bottom:24px}
.panel{background:white;padding:24px;border-radius:12px;box-shadow:0 4px 18px #15284b0c;margin-bottom:22px}
label{display:block;font-weight:bold;margin:16px 0 8px}
select,textarea{width:100%;padding:12px;border:1px solid #d6dce6;border-radius:8px;font:inherit}
textarea{min-height:120px;resize:vertical}
button{margin-top:18px;border:0;border-radius:8px;background:#ff8a32;color:white;padding:12px 18px;font-weight:bold;cursor:pointer}
.notice{padding:12px;border-radius:8px;margin-bottom:16px;background:#e7f8ed;color:#176537}
.error{background:#ffeded;color:#9b2222}
.feedback-item{padding:16px 0;border-bottom:1px solid #edf0f5}
.feedback-item:last-child{border-bottom:0}
.stars{color:#f59e0b;font-size:19px;letter-spacing:2px}
small{color:#68758a}
@media(max-width:600px){nav a{margin:8px 10px 0 0;display:inline-block}}
</style>
</head>
<body>
<header>
    <div class="brand">SGTS <span>Feedback</span></div>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="my_bookings.php">My Bookings</a>
        <a href="my_profile.php">My Profile</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>
    <h1>Share Your Feedback</h1>
    <p class="sub">Tell us about your goods transport experience.</p>

    <?php if ($message !== ''): ?>
        <div class="notice"><?= h($message) ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="notice error"><?= h($error) ?></div>
    <?php endif; ?>

    <section class="panel">
        <h2>Rate Your Booking</h2>

        <?php if (!$eligibleBookings): ?>
            <p>No eligible bookings available for feedback.</p>
            <small>Feedback is available after your booking status becomes Delivered.</small>
        <?php else: ?>
            <form method="POST">
                <label for="booking_id">Select Delivered Booking</label>
                <select id="booking_id" name="booking_id" required>
                    <option value="">-- Select Booking --</option>
                    <?php foreach ($eligibleBookings as $booking): ?>
                        <option value="<?= (int)$booking['id'] ?>">
                            <?= h($booking['booking_code']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="rating">Your Rating</label>
                <select id="rating" name="rating" required>
                    <option value="">-- Select Rating --</option>
                    <option value="5">5 - Excellent</option>
                    <option value="4">4 - Very Good</option>
                    <option value="3">3 - Good</option>
                    <option value="2">2 - Fair</option>
                    <option value="1">1 - Poor</option>
                </select>

                <label for="message">Your Comments</label>
                <textarea id="message" name="message" maxlength="2000"
                    placeholder="Write about your experience..." required></textarea>

                <button type="submit">Submit Feedback</button>
            </form>
        <?php endif; ?>
    </section>

    <section class="panel">
        <h2>My Submitted Feedback</h2>

        <?php if (!$myFeedback): ?>
            <p>You have not submitted any feedback yet.</p>
        <?php else: ?>
            <?php foreach ($myFeedback as $item): ?>
                <div class="feedback-item">
                    <strong><?= h($item['booking_code'] ?: 'General Feedback') ?></strong>
                    <div class="stars">
                        <?= str_repeat('★', max(0, min(5, (int)$item['rating']))) ?>
                        <small><?= (int)$item['rating'] ?>/5</small>
                    </div>
                    <p><?= nl2br(h($item['message'])) ?></p>
                    <small><?= h($item['created_at']) ?></small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
