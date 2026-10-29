
<?php
session_start();
require_once __DIR__ . "/config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];
$error = "";
$success = "";
$bookingCode = "";
$estimate = 0;
$upiLink = "";

$form = [
    "shifting_type" => "Home Shifting",
    "pickup_address" => "",
    "delivery_address" => "",
    "shifting_date" => "",
    "item_details" => "",
    "distance_km" => "10",
    "vehicle_id" => "",
    "packing_required" => "0",
    "payment_method" => "COD"
];

$vehiclesStmt = $pdo->query(
    "SELECT id, vehicle_name, vehicle_type, capacity_kg,
            base_fare, price_per_km, packing_charge
     FROM vehicles
     WHERE status = 'active'
     ORDER BY base_fare ASC"
);
$vehicles = $vehiclesStmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    foreach ($form as $key => $default) {
        if ($key === "packing_required") {
            $form[$key] = isset($_POST[$key]) ? "1" : "0";
        } else {
            $form[$key] = trim((string) ($_POST[$key] ?? $default));
        }
    }

    $types = ["Home Shifting", "Shop Shifting", "Goods Transport"];
    $allowedPayments = ["UPI", "COD"];
    $distance = filter_var($form["distance_km"], FILTER_VALIDATE_FLOAT);
    $vehicleId = filter_var($form["vehicle_id"], FILTER_VALIDATE_INT);

    $date = DateTime::createFromFormat("!Y-m-d", $form["shifting_date"]);
    $validDate = $date && $date->format("Y-m-d") === $form["shifting_date"];

    if (!in_array($form["shifting_type"], $types, true)) {
        $error = "Please select a valid service type.";
    } elseif (!in_array($form["payment_method"], $allowedPayments, true)) {
        $error = "Please select a valid payment method.";
    } elseif ($form["pickup_address"] === "" || $form["delivery_address"] === "") {
        $error = "Please enter pickup and delivery addresses.";
    } elseif (strlen($form["pickup_address"]) > 2000 || strlen($form["delivery_address"]) > 2000) {
        $error = "Address is too long.";
    } elseif (!$validDate || $form["shifting_date"] < date("Y-m-d")) {
        $error = "Please select today or a future shifting date.";
    } elseif ($form["item_details"] === "" || strlen($form["item_details"]) > 5000) {
        $error = "Please enter goods details (maximum 5000 characters).";
    } elseif ($distance === false || $distance < 0.1 || $distance > 5000) {
        $error = "Enter a distance between 0.1 and 5000 km.";
    } elseif ($vehicleId === false) {
        $error = "Please select a vehicle.";
    } else {
        $vehicleStmt = $pdo->prepare(
            "SELECT id, vehicle_name, capacity_kg, base_fare,
                    price_per_km, packing_charge
             FROM vehicles
             WHERE id = ? AND status = 'active'
             LIMIT 1"
        );
        $vehicleStmt->execute([$vehicleId]);
        $vehicle = $vehicleStmt->fetch();

        if (!$vehicle) {
            $error = "Selected vehicle is not available.";
        } else {
            $packing = $form["packing_required"] === "1" ? 1 : 0;

            $estimate = (float) $vehicle["base_fare"]
                + ((float) $distance * (float) $vehicle["price_per_km"])
                + ($packing ? (float) $vehicle["packing_charge"] : 0);

            if ($estimate > 99999999) {
                $error = "Estimated fare is too high. Please check the distance.";
            } else {
                try {
                    $bookingCode = "SGTS" . date("ymd") . strtoupper(bin2hex(random_bytes(3)));

                    $insert = $pdo->prepare(
                        "INSERT INTO bookings
                        (booking_code, user_id, vehicle_id, shifting_type,
                         pickup_address, delivery_address, shifting_date,
                         item_details, distance_km, packing_required,
                         estimated_price, payment_method, payment_status)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'unpaid')"
                    );

                    $insert->execute([
                        $bookingCode,
                        $userId,
                        $vehicleId,
                        $form["shifting_type"],
                        $form["pickup_address"],
                        $form["delivery_address"],
                        $form["shifting_date"],
                        $form["item_details"],
                        $distance,
                        $packing,
                        $estimate,
                        $form["payment_method"]
                    ]);

                    if ($form["payment_method"] === "UPI") {
                        $upiLink = "upi://pay?" . http_build_query([
                            "pa" => "8210121756@ybl",
                            "pn" => "SGTS Transport",
                            "tr" => $bookingCode,
                            "tn" => "Transport booking " . $bookingCode,
                            "am" => number_format($estimate, 2, ".", ""),
                            "cu" => "INR"
                        ], "", "&", PHP_QUERY_RFC3986);
                    }

                    $success = "Booking request submitted successfully.";

                    $form = [
                        "shifting_type" => "Home Shifting",
                        "pickup_address" => "",
                        "delivery_address" => "",
                        "shifting_date" => "",
                        "item_details" => "",
                        "distance_km" => "10",
                        "vehicle_id" => "",
                        "packing_required" => "0",
                        "payment_method" => "COD"
                    ];
                } catch (PDOException $e) {
                    error_log("SGTS booking error: " . $e->getMessage());
                    $error = "Booking could not be saved. Please try again.";
                }
            }
        }
    }
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Book a Vehicle | SGTS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box}
body{margin:0;background:#f3f6fa;color:#17243a;font-family:Inter,Arial,sans-serif}
nav{background:#0b1f38;color:white;padding:18px 6%;display:flex;justify-content:space-between;align-items:center;gap:15px}
.brand{font-size:22px;font-weight:800;color:white;text-decoration:none}
.brand i{color:#ff8a24}
nav a{color:white;text-decoration:none;font-size:14px;margin-left:15px}
main{max-width:1000px;margin:35px auto;padding:0 20px 40px}
h1{font-size:29px;margin-bottom:8px}
.muted,.hint{color:#718096;font-size:13px;line-height:1.6}
.panel,.result{background:white;padding:28px;border-radius:15px;box-shadow:0 6px 24px #142c460d;margin-top:22px}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.field{margin-bottom:18px}
.full{grid-column:1/-1}
label{display:block;font-size:13px;font-weight:700;margin-bottom:8px}
input,select,textarea{width:100%;padding:13px;border:1px solid #d8e0e9;border-radius:8px;font:inherit;font-size:14px;background:white}
textarea{min-height:90px;resize:vertical}
input:focus,select:focus,textarea:focus{outline:2px solid #ff8a2440;border-color:#ff8a24}
.check{display:flex;align-items:center;gap:10px;margin:12px 0}
.check input{width:18px;height:18px;accent-color:#ff8a24}
.check label{margin:0}
.button{display:inline-block;border:0;border-radius:8px;padding:14px 20px;background:#ff8a24;color:white;font-weight:800;text-decoration:none;cursor:pointer}
.button:hover{background:#ed7410}
.secondary{background:#0b1f38;margin-left:8px}
.message{padding:14px;border-radius:8px;margin:18px 0;line-height:1.6;font-size:14px}
.error{color:#a51d24;background:#fff0f0}
.success{color:#176b3b;background:#eaf8ef}
.price-info{background:#fff6eb;border:1px solid #ffe0bd;padding:14px;border-radius:8px;color:#754313;font-size:13px;line-height:1.7}
@media(max-width:650px){.grid{grid-template-columns:1fr;gap:0}.full{grid-column:auto}.panel,.result{padding:20px}h1{font-size:24px}nav{padding:16px 5%}nav a{margin-left:8px}}
</style>
</head>
<body>
<nav>
    <a class="brand" href="dashboard.php"><i class="fa-solid fa-truck-fast"></i> SGTS</a>
    <div><a href="dashboard.php">Dashboard</a><a href="my_bookings.php">My Bookings</a><a href="logout.php">Logout</a></div>
</nav>

<main>
<h1><i class="fa-solid fa-boxes-stacked" style="color:#f58220"></i> Book a Shifting Service</h1>
<p class="muted">Enter your transport details, select a vehicle and choose how you want to pay.</p>

<?php if ($error !== ""): ?>
<div class="message error"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($success !== ""): ?>
<section class="result">
    <div class="message success">
        <strong><?= e($success) ?></strong><br>
        Booking ID: <strong><?= e($bookingCode) ?></strong><br>
        Estimated fare: <strong>₹<?= number_format($estimate, 2) ?></strong><br>
        Payment method: <strong><?= e($_POST["payment_method"] ?? "COD") ?></strong><br>
        Payment status: <strong>Unpaid</strong>
    </div>

    <?php if ($upiLink !== ""): ?>
        <p><strong>UPI Advance Payment</strong></p>
        <p class="muted">Open a supported UPI app and verify the recipient and amount before paying.</p>
        <a class="button" href="<?= e($upiLink) ?>"><i class="fa-solid fa-indian-rupee-sign"></i> Pay ₹<?= number_format($estimate, 2) ?> by UPI</a>
        <p class="hint">UPI app opening does not confirm payment on this website. Your booking remains Unpaid until payment is verified.</p>
    <?php else: ?>
        <p><strong>Cash on Delivery selected.</strong> Pay the agreed amount after delivery, according to the service terms.</p>
    <?php endif; ?>

    <a class="button secondary" href="my_bookings.php">View My Bookings</a>
</section>
<?php endif; ?>

<?php if (count($vehicles) === 0): ?>
<div class="message error">No active vehicles are available. Add vehicles to the database first.</div>
<?php else: ?>
<form class="panel" method="POST" action="booking.php">
<div class="grid">
    <div class="field">
        <label for="shifting_type">Service Type</label>
        <select id="shifting_type" name="shifting_type" required>
        <?php foreach (["Home Shifting","Shop Shifting","Goods Transport"] as $type): ?>
            <option value="<?= e($type) ?>" <?= $form["shifting_type"] === $type ? "selected" : "" ?>><?= e($type) ?></option>
        <?php endforeach; ?>
        </select>
    </div>

    <div class="field">
        <label for="shifting_date">Shifting Date</label>
        <input type="date" id="shifting_date" name="shifting_date" min="<?= date("Y-m-d") ?>" value="<?= e($form["shifting_date"]) ?>" required>
    </div>

    <div class="field">
        <label for="pickup_address">Pickup Address</label>
        <textarea id="pickup_address" name="pickup_address" maxlength="2000" required><?= e($form["pickup_address"]) ?></textarea>
    </div>

    <div class="field">
        <label for="delivery_address">Delivery Address</label>
        <textarea id="delivery_address" name="delivery_address" maxlength="2000" required><?= e($form["delivery_address"]) ?></textarea>
    </div>

    <div class="field full">
        <label for="item_details">Goods / Items Details</label>
        <textarea id="item_details" name="item_details" maxlength="5000" placeholder="Example: bed, cupboard, cartons..." required><?= e($form["item_details"]) ?></textarea>
    </div>

    <div class="field">
        <label for="distance_km">Estimated Distance (KM)</label>
        <input type="number" id="distance_km" name="distance_km" min="0.1" max="5000" step="0.1" value="<?= e($form["distance_km"]) ?>" required>
        <div class="hint">Enter the estimated distance manually.</div>
    </div>

    <div class="field">
        <label for="vehicle_id">Select Vehicle</label>
        <select id="vehicle_id" name="vehicle_id" required>
            <option value="">-- Choose vehicle --</option>
            <?php foreach ($vehicles as $v): ?>
            <option value="<?= (int)$v["id"] ?>"
                data-base="<?= e($v["base_fare"]) ?>"
                data-km="<?= e($v["price_per_km"]) ?>"
                data-pack="<?= e($v["packing_charge"]) ?>"
                <?= (string)$form["vehicle_id"] === (string)$v["id"] ? "selected" : "" ?>>
                <?= e($v["vehicle_name"]) ?> — <?= number_format((float)$v["capacity_kg"]) ?> kg
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="field full">
        <div class="check">
            <input type="checkbox" id="packing_required" name="packing_required" value="1" <?= $form["packing_required"] === "1" ? "checked" : "" ?>>
            <label for="packing_required">I need packing service</label>
        </div>
        <div class="price-info">
            <strong>Fare formula:</strong> Base fare + (distance × price per km) + packing charge, if selected.
            <p id="liveEstimate" aria-live="polite">Choose a vehicle to calculate the fare.</p>
        </div>
    </div>

    <div class="field full">
        <label for="payment_method">Choose Payment Method</label>
        <select id="payment_method" name="payment_method" required>
            <option value="COD" <?= $form["payment_method"] === "COD" ? "selected" : "" ?>>Cash on Delivery — Pay after delivery</option>
            <option value="UPI" <?= $form["payment_method"] === "UPI" ? "selected" : "" ?>>UPI — Pay in advance</option>
        </select>
        <div class="hint">UPI ID: 8210121756@ybl</div>
    </div>
</div>

<button class="button" type="submit">Submit Booking Request <i class="fa-solid fa-arrow-right"></i></button>
</form>
<?php endif; ?>
</main>

<script>
const vehicleSelect = document.getElementById("vehicle_id");
const distanceInput = document.getElementById("distance_km");
const packingCheckbox = document.getElementById("packing_required");
const estimateText = document.getElementById("liveEstimate");

function updateEstimate() {
    if (!vehicleSelect || !distanceInput || !estimateText) return;

    const option = vehicleSelect.options[vehicleSelect.selectedIndex];
    if (!option || !option.value) {
        estimateText.textContent = "Choose a vehicle to calculate the fare.";
        return;
    }

    const total = Number(option.dataset.base || 0)
        + Number(distanceInput.value || 0) * Number(option.dataset.km || 0)
        + (packingCheckbox.checked ? Number(option.dataset.pack || 0) : 0);

    estimateText.textContent = "Estimated fare: ₹" + total.toLocaleString("en-IN", {
        minimumFractionDigits: 2, maximumFractionDigits: 2
    });
}

if (vehicleSelect) {
    vehicleSelect.addEventListener("change", updateEstimate);
    distanceInput.addEventListener("input", updateEstimate);
    packingCheckbox.addEventListener("change", updateEstimate);
    updateEstimate();
}
</script>
</body>
</html>
