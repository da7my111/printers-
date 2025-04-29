<?php
require_once '../includes/auth.php';
require_once '../includes/header.php';

if (!isLoggedIn()) {
    redirect('../pages/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name']);
    $type       = trim($_POST['type']);
    $location   = trim($_POST['location']);
    $building   = trim($_POST['building']);
    $ip_address = trim($_POST['ip_address']);
    $toner_code = trim($_POST['toner_code']);
    $status     = isPrinterOnline($ip_address);

    if ($name && $type && $location && $building && $ip_address) {
        $stmt = $conn->prepare("INSERT INTO printers (name, type, location, building, status, ip_address, toner_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $type, $location, $building, $status, $ip_address, $toner_code);

        if ($stmt->execute()) {
            header("Location: printers.php");
            exit;
        } else {
            $error = "❌ An error occurred while adding the printer.";
        }
    } else {
        $error = "⚠️ Please fill in all the required fields.";
    }
}
?>

<div class="form-container" style="max-width: 500px; margin: auto; background: #f5f5f5; padding: 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <h2 style="margin-bottom: 20px;">➕ Add New Printer</h2>

    <?php if (isset($error)): ?>
        <p style="color: red; font-weight: bold;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>🖨️ Printer Name:</label>
        <input type="text" name="name" required style="width:100%; padding: 8px; margin: 6px 0;">

        <label>🏷️ Printer Type (HP / Canon):</label>
        <input type="text" name="type" required style="width:100%; padding: 8px; margin: 6px 0;">

        <label>📍 Location (Room / Floor):</label>
        <input type="text" name="location" required style="width:100%; padding: 8px; margin: 6px 0;">

        <label>🏢 Building:</label>
        <input type="text" name="building" required style="width:100%; padding: 8px; margin: 6px 0;">

        <label>🧪 Toner Code (Manual):</label>
        <input type="text" name="toner_code" style="width:100%; padding: 8px; margin: 6px 0;">

        <label>🌐 Printer IP Address:</label>
        <input type="text" name="ip_address" required style="width:100%; padding: 8px; margin: 6px 0;">

        <p style="margin-top: 12px; font-size: 14px; color: #555;">📡 <strong>Status</strong> will be checked automatically via ping</p>

        <button type="submit" class="btn" style="margin-top: 15px; background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 6px;">Save Printer</button>
        <a href="printers.php" style="margin-left: 10px; text-decoration: none; color: #666;">Cancel</a>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
