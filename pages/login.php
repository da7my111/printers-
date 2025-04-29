<?php
require_once '../includes/auth.php';

if (isLoggedIn()) {
    redirect('../pages/dashboard.php');
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitizeInput($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        $error = "Email and password are required";
    } else {
        $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                
                redirect('../pages/dashboard.php');
            }
        }
        
        $error = "Invalid email or password";
    }
}

require_once '../includes/header.php';
?>

<div class="auth-form">
    <h2>Login</h2>
    
    <?php if (!empty($error)): ?>
        <div class="alert error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn">Login</button>
    </form>
    
    <p class="auth-link">Don't have an account? <a href="login/register.php">Create a new account</a></p>
</div>

<?php require_once '../includes/footer.php'; ?>