<?php
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    redirect('../pages/login.php');
}


$printers = $conn->query("SELECT * FROM printers ORDER BY building, name");
$buildings = $conn->query("SELECT DISTINCT building FROM printers ORDER BY building");

if (!$printers || !$buildings) {
    die("SQL Error: " . $conn->error);
}

require_once '../includes/header.php';
?>

<div class="printers-list">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2>🖨️ Manage Printers</h2>
        <a href="add-printer.php" class="btn"><i class="fas fa-plus"></i> Add Printer</a>
    </div>

    
    <div style="margin: 15px 0;">
        <label for="building-filter">🏢 Filter by Building:</label>
        <select id="building-filter" onchange="filterByBuilding()" style="padding: 6px 10px; margin-left: 10px;">
            <option value="all">All Buildings</option>
            <?php while ($b = $buildings->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($b['building']); ?>">
                    <?php echo htmlspecialchars($b['building']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <?php
    $currentBuilding = null;
    while ($printer = $printers->fetch_assoc()):
        if ($printer['building'] !== $currentBuilding):
            if ($currentBuilding !== null) echo "</tbody></table><br>";
            $currentBuilding = $printer['building'];
    ?>
        <div class="building-section" data-building="<?php echo htmlspecialchars($currentBuilding); ?>">
            <h3 style="margin-top: 30px;">🏢 Building: <?php echo htmlspecialchars($currentBuilding); ?></h3>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>IP Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    <?php endif; ?>

        <tr>
            <td><?php echo htmlspecialchars($printer['name']); ?></td>
            <td><?php echo htmlspecialchars($printer['type']); ?></td>
            <td><?php echo htmlspecialchars($printer['location']); ?></td>
            <td>
                <?php
                

                $online = isPrinterOnline($printer['ip_address']) ? 'online' : 'offline';
                echo $online === 'online'
                    ? '<span class="status-online"><i class="fas fa-circle" style="color:green;"></i> Online</span>'
                    : '<span class="status-offline"><i class="fas fa-circle" style="color:red;"></i> Offline</span>';
                ?>
            </td>
            <td><?php echo htmlspecialchars($printer['ip_address']); ?></td>
            <td class="actions">
                <a href="edit-printer.php?id=<?php echo $printer['id']; ?>" class="btn edit"><i class="fas fa-edit"></i></a>
                <a href="#" class="btn delete" data-id="<?php echo $printer['id']; ?>"><i class="fas fa-trash"></i></a>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody></table>
</div>

<script>

function filterByBuilding() {
    const selected = document.getElementById("building-filter").value;
    const sections = document.querySelectorAll(".building-section");

    sections.forEach(section => {
        const building = section.getAttribute("data-building");
        if (selected === "all" || building === selected) {
            section.style.display = "block";
        } else {
            section.style.display = "none";
        }
    });
}


document.querySelectorAll('.delete').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const printerId = this.getAttribute('data-id');

        if (confirm('Are you sure you want to delete this printer?')) {
            fetch(`delete-printer.php?id=${printerId}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('An error occurred while deleting the printer');
                }
            });
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
