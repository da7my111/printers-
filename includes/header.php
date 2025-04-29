<?php
if (!isset($title)) {
    $title = "Printer Monitoring System";
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>

  
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">


<link rel="stylesheet" href="../assets/css/variables-base.css">


<link rel="stylesheet" href="../assets/css/layout-nav.css">


<link rel="stylesheet" href="../assets/css/forms-tables.css">
</head>
<body>
<script src="../assets/js/main.js"></script>
    <header class="main-header">
        <div class="container">
            <div class="logo-container">
                <div class="logo">
                    <i class="fas fa-print"></i>
                    <h1>Printer Monitoring System</h1>
                </div>
                <button class="mobile-menu-toggle" aria-label="Mobile Menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <?php if (isLoggedIn()): ?>
            <nav class="main-nav">
                <ul>
                    <li><a href="../pages/dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                    <li><a href="../pages/printers.php" class="nav-link"><i class="fas fa-print"></i> <span>Printers</span></a></li>
                    <li class="user-menu">
                        <a href="#" class="nav-link user-link">
                            <i class="fas fa-user-circle"></i> 
                            <span><?php echo $_SESSION['username']; ?></span>
                            <i class="fas fa-chevron-down dropdown-icon"></i>
                        </a>
                        <ul class="dropdown">
                            <li><a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </header>

    <main class="container">