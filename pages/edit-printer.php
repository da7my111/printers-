<?php
require_once '../includes/auth.php'; // This includes config.php automatically

if (!isLoggedIn()) {
    redirect('../pages/login.php');
}

if (!isset($_GET['id'])) {
    die("No printer ID provided.");
}

$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM printers WHERE id = $id");

if ($result->num_rows == 0) {
    die("Printer not found.");
}

$printer = $result->fetch_assoc();

// دالة لفحص حالة الطابعة
function getPrinterStatus($ip) {
    $pingCommand = stripos(PHP_OS, 'WIN') === 0 ? "ping -n 1 $ip" : "ping -c 1 $ip";
    exec($pingCommand, $output, $result);
    return $result === 0 ? 'online' : 'offline';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = sanitizeInput($_POST['name']);
    $type       = sanitizeInput($_POST['type']);
    $location   = sanitizeInput($_POST['location']);
    $building   = sanitizeInput($_POST['building']);
    $ip_address = sanitizeInput($_POST['ip_address']);
    $toner_code = sanitizeInput($_POST['toner_code']);

    $status = getPrinterStatus($ip_address);

    $stmt = $conn->prepare("UPDATE printers SET name=?, type=?, location=?, building=?, ip_address=?, toner_code=?, status=? WHERE id=?");
    $stmt->bind_param("sssssssi", $name, $type, $location, $building, $ip_address, $toner_code, $status, $id);

    if ($stmt->execute()) {
        redirect('../pages/printers.php');
    } else {
        $error = "❌ An error occurred while updating the printer.";
    }
}

require_once '../includes/header.php';
?>

<div class="edit-printer-form" style="max-width: 500px; margin: auto; padding: 25px; background: #f9f9f9; border-radius: 12px;">
    <h2 style="margin-bottom: 20px;">✏️ Edit Printer</h2>

    <?php if (isset($error)): ?>
        <div style="color: red; font-weight: bold;"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post">
        <label>🖨️ Printer Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($printer['name']); ?>" required style="width: 100%; padding: 8px; margin: 6px 0;">

        <label>🏷️ Type</label>
        <input type="text" name="type" value="<?php echo htmlspecialchars($printer['type']); ?>" required style="width: 100%; padding: 8px; margin: 6px 0;">

        <label>📍 Location</label>
        <input type="text" name="location" value="<?php echo htmlspecialchars($printer['location']); ?>" required style="width: 100%; padding: 8px; margin: 6px 0;">

        <label>🏢 Building</label>
        <input type="text" name="building" value="<?php echo htmlspecialchars($printer['building']); ?>" required style="width: 100%; padding: 8px; margin: 6px 0;">

        <label>🧪 Toner Code</label>
        <input type="text" name="toner_code" value="<?php echo htmlspecialchars($printer['toner_code']); ?>" style="width: 100%; padding: 8px; margin: 6px 0;">

        <label>🌐 IP Address</label>
        <input type="text" name="ip_address" value="<?php echo htmlspecialchars($printer['ip_address']); ?>" required style="width: 100%; padding: 8px; margin: 6px 0;">

        <p style="margin-top: 10px; color: #555;">📡 Status will be checked automatically via IP</p>

        <button type="submit" class="btn" style="background: #4CAF50; color: white; padding: 10px 20px; border: none; margin-top: 15px;">Save Changes</button>
        <a href="printers.php" class="btn" style="margin-left: 10px;">Cancel</a>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
