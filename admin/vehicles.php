
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

$error = "";
$success = "";
$editingVehicle = null;

// Delete is avoided so existing bookings remain linked to their vehicle.
// Deactivate a vehicle instead.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    try {
        if ($action === "save") {
            $id = (int)($_POST["id"] ?? 0);
            $name = trim($_POST["vehicle_name"] ?? "");
            $type = trim($_POST["vehicle_type"] ?? "");
            $capacity = filter_var($_POST["capacity_kg"] ?? "", FILTER_VALIDATE_INT);
            $baseFare = filter_var($_POST["base_fare"] ?? "", FILTER_VALIDATE_FLOAT);
            $perKm = filter_var($_POST["price_per_km"] ?? "", FILTER_VALIDATE_FLOAT);
            $packing = filter_var($_POST["packing_charge"] ?? "0", FILTER_VALIDATE_FLOAT);
            $status = $_POST["status"] ?? "active";

            if ($name === "" || $type === "" || $capacity === false || $capacity <= 0 ||
                $baseFare === false || $baseFare < 0 ||
                $perKm === false || $perKm < 0 ||
                $packing === false || $packing < 0 ||
                !in_array($status, ["active", "inactive"], true)) {
                throw new InvalidArgumentException("Please enter valid vehicle details and prices.");
            }

            if ($id > 0) {
                $stmt = $pdo->prepare(
                    "UPDATE vehicles
                     SET vehicle_name=?, vehicle_type=?, capacity_kg=?,
                         base_fare=?, price_per_km=?, packing_charge=?, status=?
                     WHERE id=?"
                );
                $stmt->execute([$name, $type, $capacity, $baseFare, $perKm, $packing, $status, $id]);
                $success = "Vehicle updated successfully.";
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO vehicles
                     (vehicle_name, vehicle_type, capacity_kg, base_fare,
                      price_per_km, packing_charge, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([$name, $type, $capacity, $baseFare, $perKm, $packing, $status]);
                $success = "Vehicle added successfully.";
            }
        } elseif ($action === "toggle") {
            $id = (int)($_POST["id"] ?? 0);
            $stmt = $pdo->prepare(
                "UPDATE vehicles
                 SET status = IF(status = 'active', 'inactive', 'active')
                 WHERE id = ?"
            );
            $stmt->execute([$id]);
            $success = "Vehicle status updated.";
        } else {
            throw new InvalidArgumentException("Invalid request.");
        }
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (PDOException $e) {
        error_log("SGTS vehicle management error: " . $e->getMessage());
        $error = "Unable to save vehicle details. Please check the database columns.";
    }
}

if (isset($_GET["edit"])) {
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
    $stmt->execute([(int)$_GET["edit"]]);
    $editingVehicle = $stmt->fetch();

    if (!$editingVehicle) {
        $error = "Vehicle not found.";
    }
}

$vehicles = $pdo->query("SELECT * FROM vehicles ORDER BY id DESC")->fetchAll();

$form = $editingVehicle ?: [
    "id" => 0,
    "vehicle_name" => "",
    "vehicle_type" => "",
    "capacity_kg" => "",
    "base_fare" => "",
    "price_per_km" => "",
    "packing_charge" => 0,
    "status" => "active"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Vehicles | SGTS Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box}
body{margin:0;background:#f3f6fb;color:#17243a;font-family:Inter,Arial,sans-serif}
.topbar{background:#0b203b;color:white;padding:18px 5%;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
.brand{font-size:21px;font-weight:800}.brand i{color:#ff8a24;margin-right:8px}
a{text-decoration:none}
.topbar a{color:white}
.container{max-width:1250px;margin:28px auto;padding:0 20px}
.heading{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:22px}
h1{font-size:26px;margin:0 0 7px}.muted{color:#718096;font-size:13px}
.back{background:#e87516;color:white;padding:11px 15px;border-radius:8px;font-size:13px;font-weight:700}
.layout{display:grid;grid-template-columns:340px minmax(0,1fr);gap:20px;align-items:start}
.panel{background:white;padding:23px;border-radius:13px;border:1px solid #e5ebf3;box-shadow:0 4px 18px #17243a08}
.panel h2{font-size:18px;margin:0 0 20px}
label{display:block;font-size:12px;font-weight:700;margin:14px 0 7px}
input,select{width:100%;padding:11px;border:1px solid #d9e0e8;border-radius:8px;font-size:13px;background:white}
input:focus,select:focus{outline:2px solid #ff8a2433;border-color:#ff8a24}
.btn{display:inline-block;border:0;border-radius:7px;padding:11px 13px;font-size:12px;font-weight:700;cursor:pointer}
.primary{background:#ff8a24;color:white;width:100%;margin-top:18px}
.secondary{background:#edf1f7;color:#26364c}
.table-wrap{overflow-x:auto;border-radius:10px}
table{width:100%;border-collapse:collapse;min-width:800px}
th,td{padding:13px 12px;text-align:left;border-bottom:1px solid #edf0f5;font-size:12px}
th{background:#f8fafc;color:#59677b;font-size:10px;text-transform:uppercase;letter-spacing:.4px}
tr:last-child td{border-bottom:0}
.pill{display:inline-block;padding:5px 8px;border-radius:20px;font-size:10px;font-weight:800}
.active{background:#e6f7ed;color:#167447}.inactive{background:#fff0f0;color:#b42318}
.actions{display:flex;gap:7px;align-items:center;flex-wrap:wrap}
.edit{background:#e9f2ff;color:#175cd3}.toggle{background:#fff0e5;color:#b95b12}
.alert{padding:12px 14px;border-radius:8px;margin-bottom:18px;font-size:13px}
.good{background:#e6f7ed;color:#167447}.bad{background:#fff0f0;color:#b42318}
.note{margin-top:14px;font-size:12px;line-height:1.6;color:#718096}
@media(max-width:950px){.layout{grid-template-columns:1fr}}
</style>
</head>
<body>
<header class="topbar">
    <div class="brand"><i class="fa-solid fa-truck-fast"></i> SGTS <span style="font-size:12px;font-weight:500;color:#cbd5e1">ADMIN</span></div>
    <a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
</header>

<main class="container">
    <div class="heading">
        <div><h1>Manage Vehicles</h1><div class="muted">Add vehicles and manage capacity, rates and availability.</div></div>
        <a class="back" href="dashboard.php"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <?php if ($success !== ""): ?><div class="alert good"><?= h($success) ?></div><?php endif; ?>
    <?php if ($error !== ""): ?><div class="alert bad"><?= h($error) ?></div><?php endif; ?>

    <div class="layout">
        <section class="panel">
            <h2><i class="fa-solid fa-truck" style="color:#e87516"></i> <?= $editingVehicle ? "Edit Vehicle" : "Add New Vehicle" ?></h2>
            <form method="POST">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= h($form["id"]) ?>">

                <label for="vehicle_name">Vehicle Name</label>
                <input id="vehicle_name" name="vehicle_name" value="<?= h($form["vehicle_name"]) ?>" placeholder="e.g. Mini Pickup" required>

                <label for="vehicle_type">Vehicle Type</label>
                <input id="vehicle_type" name="vehicle_type" value="<?= h($form["vehicle_type"]) ?>" placeholder="e.g. Pickup Truck" required>

                <label for="capacity_kg">Capacity (kg)</label>
                <input id="capacity_kg" type="number" min="1" name="capacity_kg" value="<?= h($form["capacity_kg"]) ?>" required>

                <label for="base_fare">Base Fare (₹)</label>
                <input id="base_fare" type="number" min="0" step="0.01" name="base_fare" value="<?= h($form["base_fare"]) ?>" required>

                <label for="price_per_km">Price per KM (₹)</label>
                <input id="price_per_km" type="number" min="0" step="0.01" name="price_per_km" value="<?= h($form["price_per_km"]) ?>" required>

                <label for="packing_charge">Packing Charge (₹)</label>
                <input id="packing_charge" type="number" min="0" step="0.01" name="packing_charge" value="<?= h($form["packing_charge"]) ?>" required>

                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" <?= $form["status"] === "active" ? "selected" : "" ?>>Active</option>
                    <option value="inactive" <?= $form["status"] === "inactive" ? "selected" : "" ?>>Inactive</option>
                </select>

                <button class="btn primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> <?= $editingVehicle ? "Update Vehicle" : "Add Vehicle" ?></button>
                <?php if ($editingVehicle): ?>
                    <a class="btn secondary" style="display:block;text-align:center;margin-top:9px" href="vehicles.php">Cancel Edit</a>
                <?php endif; ?>
            </form>
            <p class="note">Inactive vehicles should not be offered for new bookings. Existing bookings are preserved; vehicles are deactivated instead of permanently deleted.</p>
        </section>

        <section class="panel">
            <h2>Vehicle List (<?= count($vehicles) ?>)</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr><th>Vehicle</th><th>Capacity</th><th>Base Fare</th><th>Per KM</th><th>Packing</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php if (!$vehicles): ?>
                        <tr><td colspan="7" style="text-align:center;padding:25px">No vehicles found. Add your first vehicle.</td></tr>
                    <?php else: ?>
                        <?php foreach ($vehicles as $vehicle): ?>
                        <tr>
                            <td><strong><?= h($vehicle["vehicle_name"]) ?></strong><br><span class="muted"><?= h($vehicle["vehicle_type"]) ?></span></td>
                            <td><?= number_format((int)$vehicle["capacity_kg"]) ?> kg</td>
                            <td>₹<?= number_format((float)$vehicle["base_fare"], 2) ?></td>
                            <td>₹<?= number_format((float)$vehicle["price_per_km"], 2) ?></td>
                            <td>₹<?= number_format((float)$vehicle["packing_charge"], 2) ?></td>
                            <td><span class="pill <?= h($vehicle["status"]) ?>"><?= h($vehicle["status"]) ?></span></td>
                            <td>
                                <div class="actions">
                                    <a class="btn edit" href="vehicles.php?edit=<?= (int)$vehicle["id"] ?>"><i class="fa-solid fa-pen"></i> Edit</a>
                                    <form method="POST" onsubmit="return confirm('Change this vehicle availability?');">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?= (int)$vehicle["id"] ?>">
                                        <button class="btn toggle" type="submit"><?= $vehicle["status"] === "active" ? "Deactivate" : "Activate" ?></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>
</body>
</html>
