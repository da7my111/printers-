<?php
require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../includes/printer_errors.php';

function parseToner($value) {
    return is_numeric($value) ? min(100, max(0, intval($value))) : 0;
}

// Function to cache SNMP data
function snmp_get_cached($ip, $oid, $cache_time = 60) {
    // Set cache directory
    $cache_dir = __DIR__ . '/../cache';
    
    // Create directory if it doesn't exist
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }

    // Create cache file name based on IP and OID
    $cache_file = $cache_dir . '/' . md5($ip . $oid) . '.txt';

    // Return cached data if it exists and is still valid
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_time) {
        return file_get_contents($cache_file);
    }

    // Get data via SNMP
    $result = @snmpget($ip, "public", $oid);
    $clean_result = $result ? preg_replace('/^.*: /', '', $result) : 'N/A';
    
    // Store result in cache
    file_put_contents($cache_file, $clean_result);
    
    return $clean_result;
}

if (!isLoggedIn()) {
    redirect('../pages/login.php');
}

// Get all printers
$printers = $conn->query("SELECT * FROM printers ORDER BY id DESC");

$all_printers = [];
while ($printer = $printers->fetch_assoc()) {
    $building = $printer['building'] ?: 'Not specified';
    $all_printers[$building][] = $printer;
}
?>

<style>
    .toner-bar {
        height: 10px;
        border-radius: 4px;
        background-color: lightgray;
        position: relative;
        margin-bottom: 6px;
    }
    .toner-bar::after {
        content: '';
        display: block;
        height: 100%;
        border-radius: 4px;
        position: absolute;
        top: 0;
        left: 0;
    }
    .toner-bar.black::after   { background: #000; }
    .toner-bar.cyan::after    { background: #00bfff; }
    .toner-bar.magenta::after { background: #c71585; }
    .toner-bar.yellow::after  { background: #ffd700; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
    th, td { padding: 8px; border: 1px solid #ccc; text-align: center; }
    .btn-web {
        text-decoration: none;
        background-color: #4CAF50;
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
    }

    /* Responsive CSS for smaller screens */
    @media (max-width: 768px) {
        table, th, td {
            font-size: 12px;
        }
        .toner-bar {
            height: 8px;
        }
    }
</style>

<div class="dashboard">
    <h2>📊 Printer Dashboard</h2>

    <!-- Building filter -->
    <label for="buildingFilter">Filter by Building:</label>
    <select id="buildingFilter" onchange="filterPrinters()">
        <option value="">All</option>
        <?php foreach (array_keys($all_printers) as $building): ?>
            <option value="<?= htmlspecialchars($building) ?>"><?= htmlspecialchars($building) ?></option>
        <?php endforeach; ?>
    </select>

    <div id="printerTables">
        <?php foreach ($all_printers as $building => $printers_group): ?>
            <div class="building-group" data-building="<?= htmlspecialchars($building) ?>">
                <h3>🏢 <?= htmlspecialchars($building) ?></h3>
                <table>
                    <thead>
                        <tr>
                            <th>Printer</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Toner Levels</th>
                            <th>Errors</th>
                            <th>Model</th>
                            <th>Toner Code</th>
                            <th>Last Update</th>
                            <th>Web</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($printers_group as $printer):
                            $ip = $printer['ip_address'];
                            $brand = $printer['type'];

                            // OIDs
                            $status_oid       = '1.3.6.1.2.1.25.3.5.1.1.1';
                            $error_oid        = '1.3.6.1.2.1.25.3.5.1.2.1';
                            $model_oid        = '1.3.6.1.2.1.25.3.2.1.3.1';

                            $toner_black_oid  = '1.3.6.1.2.1.43.11.1.1.9.1.1';
                            $toner_cyan_oid   = '1.3.6.1.2.1.43.11.1.1.9.1.2';
                            $toner_mag_oid    = '1.3.6.1.2.1.43.11.1.1.9.1.3';
                            $toner_yel_oid    = '1.3.6.1.2.1.43.11.1.1.9.1.4';

                            // Get SNMP data
                            $status       = snmp_get_cached($ip, $status_oid);
                            $error_code   = snmp_get_cached($ip, $error_oid);
                            $model        = snmp_get_cached($ip, $model_oid);
                            $toner_black  = snmp_get_cached($ip, $toner_black_oid);
                            $toner_cyan   = snmp_get_cached($ip, $toner_cyan_oid);
                            $toner_mag    = snmp_get_cached($ip, $toner_mag_oid);
                            $toner_yel    = snmp_get_cached($ip, $toner_yel_oid);

                            // Process errors
                            $errorMessage = ($error_code === 'N/A') ? '❓ Not Available' : getPrinterErrorMessage($brand, intval($error_code));

                            // Parse toner levels
                            $p_black  = parseToner($toner_black);
                            $p_cyan   = parseToner($toner_cyan);
                            $p_mag    = parseToner($toner_mag);
                            $p_yel    = parseToner($toner_yel);

                            // Last update time
                            $last_update = date('Y-m-d H:i:s');
                        ?>
                        <tr class="<?= ($p_black < 10 || $p_cyan < 10 || $p_mag < 10 || $p_yel < 10) ? 'low-toner' : '' ?>">
                            <td><?= htmlspecialchars($printer['name']) ?> (<?= $ip ?>)</td>
                            <td><?= htmlspecialchars($printer['location']) ?></td>
                            <td class="status-<?= strtolower($printer['status']) ?>">
                                <?= ucfirst(htmlspecialchars($printer['status'])) ?>
                            </td>
                            <td>
                                <div>🖤 <?= $p_black ?>%</div>
                                <div class="toner-bar black" style="width:<?= $p_black ?>%"></div>

                                <div>🔵 <?= $p_cyan ?>%</div>
                                <div class="toner-bar cyan" style="width:<?= $p_cyan ?>%"></div>

                                <div>🟣 <?= $p_mag ?>%</div>
                                <div class="toner-bar magenta" style="width:<?= $p_mag ?>%"></div>

                                <div>🟡 <?= $p_yel ?>%</div>
                                <div class="toner-bar yellow" style="width:<?= $p_yel ?>%"></div>
                            </td>
                            <td><?= ($errorMessage === 'No Error') ? '✅ No Errors' : '⚠️ ' . $errorMessage ?></td>
                            <td><?= htmlspecialchars($model) ?></td>
                            <td><?= htmlspecialchars($printer['toner_code']) ?></td>
                            <td><span class="last-update"><?= $last_update ?></span></td>
                            <td><a href="http://<?= $ip ?>" target="_blank" class="btn btn-web">🔗</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    // Filter printers by building
    function filterPrinters() {
        var filter = document.getElementById("buildingFilter").value;
        var groups = document.querySelectorAll('.building-group');
        groups.forEach(function(group) {
            if (filter === "" || group.getAttribute('data-building') === filter) {
                group.style.display = 'block';
            } else {
                group.style.display = 'none';
            }
        });
    }

    // Update time dynamically
    setInterval(function() {
        var elements = document.querySelectorAll('.last-update');
        elements.forEach(function(el) {
            el.textContent = new Date().toLocaleString();
        });
    }, 60000);
</script>

<?php require_once '../includes/footer.php'; ?>