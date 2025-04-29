<?php
require_once 'config.php'; 
function checkUserRole($required_role) {
    global $conn;

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../pages/login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $query = "SELECT roles.role_name FROM users 
              JOIN roles ON users.role_id = roles.id 
              WHERE users.id = $user_id LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (!$result || mysqli_num_rows($result) === 0) {
        echo "خطأ في تحميل صلاحيات المستخدم.";
        exit();
    }

    $role = mysqli_fetch_assoc($result)['role_name'];

    if ($role !== $required_role) {
        echo "❌ ليس لديك صلاحية للوصول إلى هذه الصفحة.";
        exit();
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitizeInput($data) {
    return htmlspecialchars(trim($data));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password) {
    return strlen($password) >= 8;
}
?>

