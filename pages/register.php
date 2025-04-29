<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitizeInput($_POST["username"]);
    $email = sanitizeInput($_POST["email"]);
    $password = $_POST["password"];

    // Validate input data
    if (!validateEmail($email)) {
        $error = "Invalid email format";
    } elseif (!validatePassword($password)) {
        $error = "Password must be at least 8 characters";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already registered";
        } else {
            // Hash password and register user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $insert->bind_param("sss", $username, $email, $hashed_password);

            if ($insert->execute()) {
                $success = "Registration successful! <a href='login.php'>Login here</a>";
            } else {
                $error = "Registration failed: " . $conn->error;
            }
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <h2>Create Account</h2>
    
    <?php if ($error): ?>
        <div class="alert error"><?php echo $error; ?></div>
    <?php elseif ($success): ?>
        <div class="alert success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password (minimum 8 characters)" required>
        <button type="submit">Register</button>
    </form>
    
    <p>Already have an account? <a href="login.php">Login</a></p>
</div>

<?php require_once '../includes/footer.php'; ?>